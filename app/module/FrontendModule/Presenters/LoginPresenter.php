<?php

namespace App\FrontendModule\Presenters;

use App\Component\Form\RecoveryForm;
use App\Component\Form\RegisterForm;
use App\Model\Entity\User;
use App\Model\Repository\Users;
use App\Presenters\BasePresenter;
//use Kdyby\Facebook\Facebook;
use Nette\Application\UI\Form;
use Nette;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;

class LoginPresenter extends BasePresenter
{
    /**
     * @var Facebook
     */
    //protected $facebook;

    /**
     * @var \Nette\Caching\Cache
     */
    protected $cache;

    /**
     * @var Users
     */
    protected $users;

    /**
     * @var User 
     */
    protected $user;

    protected $userData = [];
    
    protected $isStepPresenter = false;

    protected function startup()
    {
        parent::startup();

        if ($this->getUser()->isLoggedIn()) {
            $this->userData = $this->getUser()->getIdentity()->getData();
            $this->user = $this->users->repository()->findOneById($this->getUser()->getId());
        }
        $this->template->userData = $this->userData;
    }

    private function getUserKey(User $user)
    {
        return 'recovery_' . $user->getId() . '_' . $user->getEmail();
    }

    public function actionLogin()
    {
        if ($this->getUser()->isLoggedIn()) {
            if ($r = $this->getParameter('return_url', NULL)) {
                $this->redirectUrl($r);
            }
            $this->redirect(':Frontend:Frontend:default');
        }
    }

    public function actionLogout()
    {
        $this->getUser()->logout(true);
        $this->redirect('Frontend:default');
    }

    //-----------------------  LOGIN

    protected function createComponentLoginForm()
    {
        $form = new Form();
        $form->addProtection();
        $form->addText('email', 'email:')
            ->setType('email')
            ->addRule(Form::EMAIL, 'Zadejte e-mail')
            ->setRequired(true);
        $form->addPassword('password', 'password:')
            ->setType('password')
            ->setRequired(true);
        $form->addSubmit('login', 'Login');
        $form->addHidden('return_url', $this->getParameter('return_url', NULL));
        $form->onSuccess[] = [$this, 'loginFormSucceeded'];

        return $form;
    }

    public function loginFormSucceeded(Form $form = null, $values)
    {
        $message = 'tr.user.success';
        $success = true;
        try{
            $this->getUser()->login($values->email, $values->password);
        } catch (Nette\Security\AuthenticationException $e) {
            $success = false;
            switch($e->getMessage()) {
                case 'User not found.'   : $message = 'tr.user.not_found'; break;
                case 'Invalid password.' : $message = 'tr.user.wrong_pass'; break;
                default: $message = 'tr.user.error'; break;
            }
        }
        $this->flashMessage($message, $success ? 'info' : 'danger');
        $returnUrl = $form && isset($form['return_url']) ? $form['return_url']->getValue() : null;
        if (!$success)
            return;

        if ($returnUrl) {
            $this->redirectUrl($returnUrl);
        } else {
            $this->redirect('Frontend:');
        }
    }

    //-----------------------  REGISTER

    public function createComponentRegisterForm()
    {
        $form = new RegisterForm();
        $form->create();
        $form->addHidden('return_url', $this->getParameter('return_url', NULL));
        $form->addSubmit('send', $this->translator->trans('tr.modal.register_button'))
            ->onClick[] = [$this, 'registerForm'];

        return $form;
    }

    public function registerForm(Nette\Forms\Controls\SubmitButton $button)
    {
        $user = new User();

        try {
            $button->getForm()->process($user);

            $this->users->repository()->save($user);

            $this->flashMessage('tr.user.user_registered');
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->flashMessage('tr.user.email_unique', 'danger');
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->flashMessage('tr.user.email_unique', 'danger');
        } catch (Exception $e) {
            $this->flashMessage($e->getMessage(), 'danger');
        }

        $data    = $button->getForm()->getValues();
        $credentials = (object) ['email' => $data['reg_email'], 'password' => $data['reg_password']];

        //TODO: confirm e-mail send
        $this->loginFormSucceeded($button->getForm(), $credentials);
    }

    //-----------------------  PROFILE

    protected function createComponentProfileForm()
    {
        $form = new Form();
        $form->addPassword('old_password', 'Stare heslo')
            ->setType('password')
            ->setRequired(true);
        $form->addPassword('password', 'Nove heslo')
            ->setType('password')
            ->setRequired(true);
        $form->addSubmit('login', $this->translator->trans('tr.user.change_password'));
        $form->addHidden('return_url', $this->link('this'));
        $form->onSuccess[] = [$this, 'profileFormSucceeded'];

        return $form;
    }

    public function profileFormSucceeded(Form $form = null, $values)
    {
        $userData = $this->getUser()->getIdentity()->getData();
        $user = $this->users->repository()->findOneBy(['id'=>$userData['id']]);

        if (!\Nette\Security\Passwords::verify($values['old_password'], $user->getPassword())) {
            $this->flashMessage('tr.user.wrong_old_password', 'danger');
            return;
        } else {
            $user->setPassword(\Nette\Security\Passwords::hash($values['password']));
            $this->users->repository()->save($user);
            $this->flashMessage('tr.user.wrong_old_password');
        }

        $returnUrl = $form && isset($form['return_url']) ? $form['return_url']->getValue() : null;

        if ($returnUrl) {
            $this->redirectUrl($returnUrl);
        } else {
            $this->redirect('Frontend:');
        }
    }

    //-----------------------  FORGOTTEN PASSWORD

    protected function createComponentForgottenForm()
    {
        $form = new Form();
        $form->addText('email', 'email:')
            ->setType('email')
            ->addRule(Form::EMAIL, 'Zadejte e-mail')
            ->setRequired(true);
        $form->addSubmit('login', $this->translator->trans('tr.modal.forgotten_proceed'));
        $form->onSuccess[] = [$this, 'loginForgottenSucceeded'];

        return $form;
    }

    public function loginForgottenSucceeded(Form $form = null, $values)
    {
        try{
            $user = $this->users->repository()->findOneBy(['email'=>$values['email']]);
            if (!$user) {
                $this->flashMessage('tr.user.recovery_error');
                return;
            }

            $hash = md5(md5($this->getUserKey($user)).rand());
            $this->cache->save($hash, $user->getId(), [Cache::EXPIRE => '1 hour']);

            $this->sendMail($values['email'], $this->translator->trans('tr.user.forgotten_subject'),
                [
                    'hash' => $hash,
                ],
                'forgottenMail'
            );
        } catch (\Exception $e) {

        }
        $this->flashMessage('tr.user.recovery_sent');

        $this->redirect('Frontend:');
    }

    public function actionPasswordRecovery($hash)
    {
        $user = $this->users->repository()->findOneBy(['id' => $this->cache->load($hash)]);
        if (!$user) {
            $this->flashMessage('tr.user.recovery_errorě');
            $this->redirect(':Frontend:Frontend:default');
        }

        $this->cache->remove($this->getUserKey($user));
    }

    protected function createComponentRecoveryForm()
    {
        $cached = $this->cache->load($this->getParameter('hash'));
        $user = $this->users->repository()->findOneBy(['id'=>$cached]);

        $form = new RecoveryForm();
        $form->create();
        $form->setItem($user);
        $form->addSubmit('send', $this->translator->trans('tr.modal.recovery_button'))
            ->onClick[] = [$this, 'recoveryForm'];

        return $form;
    }

    public function recoveryForm(Nette\Forms\Controls\SubmitButton $button)
    {
        $data = $button->getForm()->getValues();
        $user = $this->users->repository()->findOneBy(['id'=>$data['id']]);

        try {
            $button->getForm()->process($user);

            $this->users->repository()->save($user);

            $this->flashMessage('tr.user.user_registered');
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->flashMessage('tr.user.email_unique', 'danger');
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->flashMessage('tr.user.email_unique', 'danger');
        } catch (Exception $e) {
            $this->flashMessage($e->getMessage(), 'danger');
        }

        $credentials = (object) ['email' => $user->getEmail(), 'password' => $data['reg_password']];

        $this->loginFormSucceeded($button->getForm(), $credentials);
    }



    //-----------------------  FACEBOOK

//     protected function createComponentFacebookLogin()
//     {
//         $dialog = $this->facebook->createDialog('login');
//         /** @var \Kdyby\Facebook\Dialog\LoginDialog $dialog */
//         $dialog->onResponse[] = function (\Kdyby\Facebook\Dialog\LoginDialog $dialog) {
//             $fb = $dialog->getFacebook();

//             if (!$fb->getUser()) {
//                 $this->flashMessage("Sorry bro, facebook authentication failed.");

//                 return;
//             }

//             /**
//              * If we get here, it means that the user was recognized
//              * and we can call the Facebook API
//              */
//             try {
//                 $me = $fb->api('/me', null, ['fields' => [
//                         'id',
//                         'first_name',
//                         'last_name',
//                         'picture',
//                         'email',
//                     ]]);

//                 if (!$user = $this->users->findByFacebookId($me->id)) {
//                     /**
//                      * Variable $me contains all the public information about the user
//                      * including facebook id, name and email, if he allowed you to see it.
//                      */
//                     //TODO add FB information you have got to created user
//                     $user = new User();
// //                    $user
// //                        ->setEmail($me->email);
// //                    $this->users->create($user);
//                 }

// //                TODO save token for later use
// //                $this->usersModel->updateFacebookAccessToken($fb->getUser(), $fb->getAccessToken());

//                 // TODO get user identity from User
//                 $this->getUser()->login(new Nette\Security\Identity($user->getId(), $user->getRoles(), $user));
//             } catch (\Kdyby\Facebook\FacebookApiException $e) {
//                 /**
//                  * You might wanna know what happened, so let's log the exception.
//                  *
//                  * Rendering entire bluescreen is kind of slow task,
//                  * so might wanna log only $e->getMessage(), it's up to you
//                  */
//                 \Tracy\Debugger::log($e, 'facebook');
//                 $this->flashMessage("Sorry bro, facebook authentication failed hard.");
//             }

//             $this->redirect('this');
//         };

//         return $dialog;
//     }

//     /**
//      * @param Facebook $facebook
//      */
//     public function injectFacebook(Facebook $facebook)
//     {
//         $this->facebook = $facebook;
//     }

    /**
     * @param Users $users
     */
    public function injectUsers(Users $users)
    {
        $this->users = $users;
    }

    /**
     * @param IStorage $cache
     */
    public function injectCache(IStorage $storage)
    {
        $this->cache = new Cache($storage);
    }
}

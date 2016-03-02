<?php

namespace App\AdminModule\Presenters;

use App\Model\Repository\Users;
use App\Presenters\BasePresenter;

abstract class AdminPresenter extends BasePresenter
{
    /**
     * @var Users
     */
    protected $users;

    /**
     */
    protected function startup()
    {
        parent::startup();

        $user = $this->getUser();
        if (!$user->isLoggedIn()) {
            $this->redirect(':Frontend:Login:login', ['return_url' => $this->getHttpRequest()->getUrl()->getAbsoluteUrl()]);
        }
        if (!$user->isAllowed('Admin')) {
            $this->flashMessage('Nemate opravneni do teto sekce.', 'danger');
            $this->redirect(':Frontend:homepage:');
        }
    }

    /**
     */
    protected function beforeRender()
    {
        parent::beforeRender();
    }

    /**
     * @param Users $users
     */
    public function injectUsers(Users $users)
    {
        $this->users = $users;
    }

}

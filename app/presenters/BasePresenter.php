<?php

namespace App\Presenters;

use App\Model\Repository\DataRepository;
use Nette\Application\UI\Presenter;


class BasePresenter extends Presenter
{
    const DEFAULT_LANGUAGE = 'cs';

    /** @persistent */
    public $locale;

    /** @var \App\Email\MyMail */
    protected $mail;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;

    /**
     * Config parameters
     *
     * @var array
     */
    protected $parameters;

    /**
     */
    protected function startup()
    {
        parent::startup();
    }

    /**
     * Common render method.
     */
    protected function beforeRender()
    {
        parent::beforeRender();

        $this->template->gulpVersion = 1;
    }

    protected function sendMail($to, $subject, $params, $templateConfig, $attachments = [])
    {
        $params['_presenter'] = $this;
        $params['_controller'] = $this;
        $params['_control'] = $this;
        $params['url'] = new \Nette\Http\Url($this->getHttpRequest()->getUrl());

        $module = explode(':', $this->getName());
        $this->mail->send($this->parameters['admin_mail'], $to, $this->translator->trans($subject), $params, [$module[0], $templateConfig], $attachments);
    }

    /**
     * Saves the message to template, that can be displayed after redirect.
     * @param  string
     * @param  string
     * @return \stdClass
     */
    public function flashMessage($message, $type = 'info')
    {
        return parent::flashMessage($this->translator->trans($message), $type);
    }

    public function trans($message)
    {
        return $this->translator->trans($message);
    }

    /**
     * @param \App\Email\MyMail $mail
     */
    public function injectMailer(\App\Email\MyMail $mail)
    {
        $this->mail = $mail;
    }

    /**
     * @param \Nette\DI\Container $container
     */
    public function injectContainer(\Nette\DI\Container $container)
    {
        $this->parameters = $container->getParameters();
    }
}

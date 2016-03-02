<?php

namespace App\AdminModule\Presenters;

use App\Component\Form;
use App\Model\Repository\Users;

class UserPresenter extends AdminFormPresenter
{
    const ENTITY_NAME = 'App\Model\Entity\User';
    const FORM_NAME   = 'App\Component\Form\UserForm';

    /**
     * @var Users
     */
    protected $repo;

    protected function startup()
    {
        parent::startup();
        $this->repo = $this->users;
    }

    protected function getEntityName()
    {
        return self::ENTITY_NAME;
    }

    protected function getFormName()
    {
        return self::FORM_NAME;
    }
}

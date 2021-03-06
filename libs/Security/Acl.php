<?php

namespace App\Security;

use Nette\Security\Permission;

class Acl extends Permission
{
    /**
     * Acl constructor.
     */
    public function __construct()
    {
        $this->defineRoles();
        $this->defineResources();
        $this->definePrivileges();
    }

    private function defineRoles()
    {
        $this->addRole('guest');
        $this->addRole('registered', 'guest');
        $this->addRole('administrator', 'registered');
    }

    private function defineResources()
    {
        $this->addResource('Admin');
    }

    private function definePrivileges()
    {
        $this->allow('administrator', 'Admin');
    }
}

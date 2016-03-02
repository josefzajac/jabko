<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User
{
    use MagicAccessors;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $fid;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="array")
     */
    protected $roles = [];

    /**
     * When was voted
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->created        = new \Datetime();
    }

    public function export()
    {
        return [
            'id'    => $this->id,
            'fid'   => $this->fid,
            'name'  => $this->name,
            'email' => $this->email,
            'roles' => $this->roles,
        ];
    }
}

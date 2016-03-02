<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * @ORM\Entity
 */
class MailHistory
{
    use MagicAccessors;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * Sent date
     *
     * @ORM\Column(type="datetime")
     */
    protected $sent_date;

    /**
     * Sent
     *
     * @ORM\Column(type="boolean", options={"default" = 0})
     */
    protected $sent;

    /**
     * From
     *
     * @ORM\Column(type="string")
     */
    protected $sender;

    /**
     * To
     *
     * @ORM\Column(type="string")
     */
    protected $receiver;

    /**
     * Subject
     *
     * @ORM\Column(type="string")
     */
    protected $subject;

    /**
     * Content
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $content;

    /**
     * HTML
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $html;

    /**
     *
     */
    public function __construct()
    {
        $this->sent     = 0;
        $this->sent_date = new \DateTime();
    }
}

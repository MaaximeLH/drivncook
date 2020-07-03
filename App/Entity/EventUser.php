<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventUser
 *
 * @ORM\Table(name="event_user", indexes={@ORM\Index(name="IDX_92589AE271F7E88B", columns={"event_id"}), @ORM\Index(name="IDX_92589AE2A76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class EventUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="event_user_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     * })
     */
    private $event;

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param \Event $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return \Users
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \Users $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PromotionTarget
 *
 * @ORM\Table(name="promotion_target", indexes={@ORM\Index(name="IDX_53D504C14575D828", columns={"card_item_id"}), @ORM\Index(name="IDX_53D504C1BF7DD742", columns={"card_category"})})
 * @ORM\Entity
 */
class EventUser
{
    /**
     * @var \Users
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var \Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event", referencedColumnName="id")
     * })
     */
    private $event;

    /**
     * Get the value of user
     *
     * @return  \Users
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @param  \User  $user
     *
     * @return  self
     */
    public function setUser(Users $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of event
     *
     * @return  \Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set the value of event
     *
     * @param  \Event  $event
     *
     * @return  self
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;

        return $this;
    }
}

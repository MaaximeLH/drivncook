<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventCustomer
 *
 * @ORM\Table(name="event_customer")
 * @ORM\Entity
 */
class EventCustomer
{
    /**
     * @var \Customer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Customer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $customer;

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
     * @var string|null
     *
     * @ORM\Column(name="statut", type="string", length=255, nullable=true)
     */
    private $statut;

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

    /**
     * Get the value of customer
     *
     * @return  \Customer
     */ 
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set the value of customer
     *
     * @param  Customer  $customer
     *
     * @return  self
     */ 
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get the value of statut
     *
     * @return  string|null
     */ 
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set the value of statut
     *
     * @param  string|null  $statut
     *
     * @return  self
     */ 
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }
}

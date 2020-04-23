<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Invoice
 *
 * @ORM\Table(name="invoice", indexes={@ORM\Index(name="IDX_90651744A76ED395", columns={"user_id"}), @ORM\Index(name="IDX_906517449395C3F3", columns={"customer_id"}), @ORM\Index(name="IDX_906517445080ECDE", columns={"warehouse_id"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Invoice
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="invoice_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="recipient", type="string", length=255, nullable=true)
     */
    private $recipient;

    /**
     * @var string|null
     *
     * @ORM\Column(name="owner", type="string", length=255, nullable=true)
     */
    private $owner;

    /**
     * @var string|null
     *
     * @ORM\Column(name="owner_address_line", type="string", length=255, nullable=true)
     */
    private $ownerAddressLine;

    /**
     * @var string|null
     *
     * @ORM\Column(name="owner_postal_code", type="string", length=255, nullable=true)
     */
    private $ownerPostalCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="owner_city", type="string", length=255, nullable=true)
     */
    private $ownerCity;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    private $status = false;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="Customer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $customer;

    /**
     * @var Warehouse
     *
     * @ORM\ManyToOne(targetEntity="Warehouse")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="warehouse_id", referencedColumnName="id")
     * })
     */
    private $warehouse;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param string|null $recipient
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * @return string|null
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param string|null $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return string|null
     */
    public function getOwnerAddressLine()
    {
        return $this->ownerAddressLine;
    }

    /**
     * @param string|null $ownerAddressLine
     */
    public function setOwnerAddressLine($ownerAddressLine)
    {
        $this->ownerAddressLine = $ownerAddressLine;
    }

    /**
     * @return string|null
     */
    public function getOwnerPostalCode()
    {
        return $this->ownerPostalCode;
    }

    /**
     * @param string|null $ownerPostalCode
     */
    public function setOwnerPostalCode($ownerPostalCode)
    {
        $this->ownerPostalCode = $ownerPostalCode;
    }

    /**
     * @return string|null
     */
    public function getOwnerCity()
    {
        return $this->ownerCity;
    }

    /**
     * @param string|null $ownerCity
     */
    public function setOwnerCity($ownerCity)
    {
        $this->ownerCity = $ownerCity;
    }

    /**
     * @return bool|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param bool|null $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Gets triggered only on insert

     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime("now");
    }

    /**
     * @return Users
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param Users $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return Warehouse
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * @param Warehouse $warehouse
     */
    public function setWarehouse($warehouse)
    {
        $this->warehouse = $warehouse;
    }
}

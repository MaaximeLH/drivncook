<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Users
 *
 * @ORM\Table(name="users", indexes={@ORM\Index(name="IDX_1483A5E9C6957CCE", columns={"truck_id"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Users
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="users_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="society_name", type="string", length=255, nullable=true)
     */
    private $societyName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="siret", type="string", length=80, nullable=true)
     */
    private $siret;

    /**
     * @var string|null
     *
     * @ORM\Column(name="address_line", type="string", length=255, nullable=true)
     */
    private $addressLine;

    /**
     * @var string|null
     *
     * @ORM\Column(name="country", type="string", length=100, nullable=true)
     */
    private $country;

    /**
     * @var string|null
     *
     * @ORM\Column(name="postal_code", type="string", length=80, nullable=true)
     */
    private $postalCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="address_city", type="string", length=100, nullable=true)
     */
    private $addressCity;

    /**
     * @var string|null
     *
     * @ORM\Column(name="address_state", type="string", length=100, nullable=true)
     */
    private $addressState;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone_number", type="string", length=80, nullable=true)
     */
    private $phoneNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="password", type="string", length=70, nullable=true)
     */
    private $password;

    /**
     * @var string|null
     *
     * @ORM\Column(name="stripe_public_key", type="string", length=255, nullable=true)
     */
    private $stripePublicKey;

    /**
     * @var string|null
     *
     * @ORM\Column(name="stripe_private_key", type="string", length=255, nullable=true)
     */
    private $stripePrivateKey;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=true, options={"default"="1"})
     */
    private $isActive = true;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var Truck
     *
     * @ORM\ManyToOne(targetEntity="Truck")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="truck_id", referencedColumnName="id")
     * })
     */
    private $truck;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Event", mappedBy="user")
     */
    private $event;

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
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string|null
     */
    public function getSocietyName()
    {
        return $this->societyName;
    }

    /**
     * @param string|null $societyName
     */
    public function setSocietyName($societyName)
    {
        $this->societyName = $societyName;
    }

    /**
     * @return string|null
     */
    public function getSiret()
    {
        return $this->siret;
    }

    /**
     * @param string|null $siret
     */
    public function setSiret($siret)
    {
        $this->siret = $siret;
    }

    /**
     * @return string|null
     */
    public function getAddressLine()
    {
        return $this->addressLine;
    }

    /**
     * @param string|null $addressLine
     */
    public function setAddressLine($addressLine)
    {
        $this->addressLine = $addressLine;
    }

    /**
     * @return string|null
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string|null $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return string|null
     */
    public function getAddressCity()
    {
        return $this->addressCity;
    }

    /**
     * @param string|null $addressCity
     */
    public function setAddressCity($addressCity)
    {
        $this->addressCity = $addressCity;
    }

    /**
     * @return string|null
     */
    public function getAddressState()
    {
        return $this->addressState;
    }

    /**
     * @param string|null $addressState
     */
    public function setAddressState($addressState)
    {
        $this->addressState = $addressState;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string|null $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getStripePublicKey()
    {
        return $this->stripePublicKey;
    }

    /**
     * @param string|null $stripePublicKey
     */
    public function setStripePublicKey($stripePublicKey)
    {
        $this->stripePublicKey = $stripePublicKey;
    }

    /**
     * @return string|null
     */
    public function getStripePrivateKey()
    {
        return $this->stripePrivateKey;
    }

    /**
     * @param string|null $stripePrivateKey
     */
    public function setStripePrivateKey($stripePrivateKey)
    {
        $this->stripePrivateKey = $stripePrivateKey;
    }

    /**
     * @return bool|null
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param bool|null $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
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
     * @return Truck
     */
    public function getTruck()
    {
        return $this->truck;
    }

    /**
     * @param Truck $truck
     */
    public function setTruck($truck)
    {
        $this->truck = $truck;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->event = new \Doctrine\Common\Collections\ArrayCollection();
    }

}

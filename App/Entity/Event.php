<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Event
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="event_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="start_at", type="datetime", nullable=true)
     */
    private $startAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="end_at", type="datetime", nullable=true)
     */
    private $endAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="begin_register_at", type="datetime", nullable=true)
     */
    private $beginRegisterAt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Users", inversedBy="event")
     * @ORM\JoinTable(name="event_user",
     *   joinColumns={
     *     @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *   }
     * )
     */
    private $user;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title_Email_FR", type="text", nullable=true)
     */
    private $titleEmailFR;

    /**
     * @var string|null
     *
     * @ORM\Column(name="text_Email_FR", type="text", nullable=true)
     */
    private $textEmailFR;

    /**
     * @var string|null
     *
     * @ORM\Column(name="text_FR", type="text", nullable=true)
     */
    private $textFR;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title_Email_EN", type="text", nullable=true)
     */
    private $titleEmailEN;

    /**
     * @var string|null
     *
     * @ORM\Column(name="text_Email_EN", type="text", nullable=true)
     */
    private $textEmailEN;

    /**
     * @var string|null
     *
     * @ORM\Column(name="text_EN", type="text", nullable=true)
     */
    private $textEN;

    /**
     * @var string|null
     *
     * @ORM\Column(name="image", type="text", nullable=true)
     */
    private $image;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return \DateTime|null
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * @param \DateTime|null $startAt
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * @param \DateTime|null $endAt
     */
    public function setEndAt($endAt)
    {
        $this->endAt = $endAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getBeginRegisterAt()
    {
        return $this->beginRegisterAt;
    }

    /**
     * @param \DateTime|null $beginRegisterAt
     */
    public function setBeginRegisterAt($beginRegisterAt)
    {
        $this->beginRegisterAt = $beginRegisterAt;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
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
     * Get the value of image
     *
     * @return  string|null
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the value of image
     *
     * @param  string|null  $image
     *
     * @return  self
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get the value of textEmailEN
     *
     * @return  string|null
     */
    public function getTextEmailEN()
    {
        return $this->textEmailEN;
    }

    /**
     * Set the value of textEmailEN
     *
     * @param  string|null  $textEmailEN
     *
     * @return  self
     */
    public function setTextEmailEN($textEmailEN)
    {
        $this->textEmailEN = $textEmailEN;

        return $this;
    }

    /**
     * Get the value of textEN
     *
     * @return  string|null
     */
    public function getTextEN()
    {
        return $this->textEN;
    }

    /**
     * Set the value of textEN
     *
     * @param  string|null  $textEN
     *
     * @return  self
     */
    public function setTextEN($textEN)
    {
        $this->textEN = $textEN;

        return $this;
    }

    /**
     * Get the value of titleEmailEN
     *
     * @return  string|null
     */
    public function getTitleEmailEN()
    {
        return $this->titleEmailEN;
    }

    /**
     * Set the value of titleEmailEN
     *
     * @param  string|null  $titleEmailEN
     *
     * @return  self
     */
    public function setTitleEmailEN($titleEmailEN)
    {
        $this->titleEmailEN = $titleEmailEN;

        return $this;
    }

    /**
     * Get the value of textFR
     *
     * @return  string|null
     */
    public function getTextFR()
    {
        return $this->textFR;
    }

    /**
     * Set the value of textFR
     *
     * @param  string|null  $textFR
     *
     * @return  self
     */
    public function setTextFR($textFR)
    {
        $this->textFR = $textFR;

        return $this;
    }

    /**
     * Get the value of textEmailFR
     *
     * @return  string|null
     */
    public function getTextEmailFR()
    {
        return $this->textEmailFR;
    }

    /**
     * Set the value of textEmailFR
     *
     * @param  string|null  $textEmailFR
     *
     * @return  self
     */
    public function setTextEmailFR($textEmailFR)
    {
        $this->textEmailFR = $textEmailFR;

        return $this;
    }

    /**
     * Get the value of titleEmailFR
     *
     * @return  string|null
     */
    public function getTitleEmailFR()
    {
        return $this->titleEmailFR;
    }

    /**
     * Set the value of titleEmailFR
     *
     * @param  string|null  $titleEmailFR
     *
     * @return  self
     */
    public function setTitleEmailFR($titleEmailFR)
    {
        $this->titleEmailFR = $titleEmailFR;

        return $this;
    }
}

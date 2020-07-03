<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity
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
     * @var string|null
     *
     * @ORM\Column(name="title_email_fr", type="string", length=255, nullable=true)
     */
    private $titleEmailFr;

    /**
     * @var string|null
     *
     * @ORM\Column(name="text_email_fr", type="text", nullable=true)
     */
    private $textEmailFr;

    /**
     * @var string|null
     *
     * @ORM\Column(name="text_fr", type="text", nullable=true)
     */
    private $textFr;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title_email_en", type="string", length=255, nullable=true)
     */
    private $titleEmailEn;

    /**
     * @var string|null
     *
     * @ORM\Column(name="text_email_en", type="text", nullable=true)
     */
    private $textEmailEn;

    /**
     * @var string|null
     *
     * @ORM\Column(name="text_en", type="text", nullable=true)
     */
    private $textEn;

    /**
     * @var string|null
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var string|null
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

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
     * Gets triggered only on insert

     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime("now");
    }

    /**
     * @return string|null
     */
    public function getTitleEmailFr()
    {
        return $this->titleEmailFr;
    }

    /**
     * @param string|null $titleEmailFr
     */
    public function setTitleEmailFr($titleEmailFr)
    {
        $this->titleEmailFr = $titleEmailFr;
    }

    /**
     * @return string|null
     */
    public function getTextEmailFr()
    {
        return $this->textEmailFr;
    }

    /**
     * @param string|null $textEmailFr
     */
    public function setTextEmailFr($textEmailFr)
    {
        $this->textEmailFr = $textEmailFr;
    }

    /**
     * @return string|null
     */
    public function getTextFr()
    {
        return $this->textFr;
    }

    /**
     * @param string|null $textFr
     */
    public function setTextFr($textFr)
    {
        $this->textFr = $textFr;
    }

    /**
     * @return string|null
     */
    public function getTitleEmailEn()
    {
        return $this->titleEmailEn;
    }

    /**
     * @param string|null $titleEmailEn
     */
    public function setTitleEmailEn($titleEmailEn)
    {
        $this->titleEmailEn = $titleEmailEn;
    }

    /**
     * @return string|null
     */
    public function getTextEmailEn()
    {
        return $this->textEmailEn;
    }

    /**
     * @param string|null $textEmailEn
     */
    public function setTextEmailEn($textEmailEn)
    {
        $this->textEmailEn = $textEmailEn;
    }

    /**
     * @return string|null
     */
    public function getTextEn()
    {
        return $this->textEn;
    }

    /**
     * @param string|null $textEn
     */
    public function setTextEn($textEn)
    {
        $this->textEn = $textEn;
    }

    /**
     * @return string|null
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string|null $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}

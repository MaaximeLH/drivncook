<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Openings
 *
 * @ORM\Table(name="openings", indexes={@ORM\Index(name="IDX_196D69D5A76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class Openings
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="openings_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="monday_open", type="time", nullable=true)
     */
    private $mondayOpen;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="monday_close", type="time", nullable=true)
     */
    private $mondayClose;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="tuesday_open", type="time", nullable=true)
     */
    private $tuesdayOpen;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="tuesday_close", type="time", nullable=true)
     */
    private $tuesdayClose;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="wed_open", type="time", nullable=true)
     */
    private $wedOpen;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="wed_close", type="time", nullable=true)
     */
    private $wedClose;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="thursday_open", type="time", nullable=true)
     */
    private $thursdayOpen;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="thursday_close", type="time", nullable=true)
     */
    private $thursdayClose;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="friday_open", type="time", nullable=true)
     */
    private $fridayOpen;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="friday_close", type="time", nullable=true)
     */
    private $fridayClose;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="sat_open", type="time", nullable=true)
     */
    private $satOpen;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="sat_close", type="time", nullable=true)
     */
    private $satClose;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="sun_open", type="time", nullable=true)
     */
    private $sunOpen;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="sun_close", type="time", nullable=true)
     */
    private $sunClose;

    /**
     * @var int|null
     *
     * @ORM\Column(name="global_open", type="integer", nullable=true)
     */
    private $globalOpen;

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime|null
     */
    public function getMondayOpen()
    {
        return $this->mondayOpen;
    }

    /**
     * @param \DateTime|null $mondayOpen
     */
    public function setMondayOpen($mondayOpen)
    {
        $this->mondayOpen = $mondayOpen;
    }

    /**
     * @return \DateTime|null
     */
    public function getMondayClose()
    {
        return $this->mondayClose;
    }

    /**
     * @param \DateTime|null $mondayClose
     */
    public function setMondayClose($mondayClose)
    {
        $this->mondayClose = $mondayClose;
    }

    /**
     * @return \DateTime|null
     */
    public function getTuesdayOpen()
    {
        return $this->tuesdayOpen;
    }

    /**
     * @param \DateTime|null $tuesdayOpen
     */
    public function setTuesdayOpen($tuesdayOpen)
    {
        $this->tuesdayOpen = $tuesdayOpen;
    }

    /**
     * @return \DateTime|null
     */
    public function getTuesdayClose()
    {
        return $this->tuesdayClose;
    }

    /**
     * @param \DateTime|null $tuesdayClose
     */
    public function setTuesdayClose($tuesdayClose)
    {
        $this->tuesdayClose = $tuesdayClose;
    }

    /**
     * @return \DateTime|null
     */
    public function getWedOpen()
    {
        return $this->wedOpen;
    }

    /**
     * @param \DateTime|null $wedOpen
     */
    public function setWedOpen($wedOpen)
    {
        $this->wedOpen = $wedOpen;
    }

    /**
     * @return \DateTime|null
     */
    public function getWedClose()
    {
        return $this->wedClose;
    }

    /**
     * @param \DateTime|null $wedClose
     */
    public function setWedClose($wedClose)
    {
        $this->wedClose = $wedClose;
    }

    /**
     * @return \DateTime|null
     */
    public function getThursdayOpen()
    {
        return $this->thursdayOpen;
    }

    /**
     * @param \DateTime|null $thursdayOpen
     */
    public function setThursdayOpen($thursdayOpen)
    {
        $this->thursdayOpen = $thursdayOpen;
    }

    /**
     * @return \DateTime|null
     */
    public function getThursdayClose()
    {
        return $this->thursdayClose;
    }

    /**
     * @param \DateTime|null $thursdayClose
     */
    public function setThursdayClose($thursdayClose)
    {
        $this->thursdayClose = $thursdayClose;
    }

    /**
     * @return \DateTime|null
     */
    public function getFridayOpen()
    {
        return $this->fridayOpen;
    }

    /**
     * @param \DateTime|null $fridayOpen
     */
    public function setFridayOpen($fridayOpen)
    {
        $this->fridayOpen = $fridayOpen;
    }

    /**
     * @return \DateTime|null
     */
    public function getFridayClose()
    {
        return $this->fridayClose;
    }

    /**
     * @param \DateTime|null $fridayClose
     */
    public function setFridayClose($fridayClose)
    {
        $this->fridayClose = $fridayClose;
    }

    /**
     * @return \DateTime|null
     */
    public function getSatOpen()
    {
        return $this->satOpen;
    }

    /**
     * @param \DateTime|null $satOpen
     */
    public function setSatOpen($satOpen)
    {
        $this->satOpen = $satOpen;
    }

    /**
     * @return \DateTime|null
     */
    public function getSatClose()
    {
        return $this->satClose;
    }

    /**
     * @param \DateTime|null $satClose
     */
    public function setSatClose($satClose)
    {
        $this->satClose = $satClose;
    }

    /**
     * @return \DateTime|null
     */
    public function getSunOpen()
    {
        return $this->sunOpen;
    }

    /**
     * @param \DateTime|null $sunOpen
     */
    public function setSunOpen($sunOpen)
    {
        $this->sunOpen = $sunOpen;
    }

    /**
     * @return \DateTime|null
     */
    public function getSunClose()
    {
        return $this->sunClose;
    }

    /**
     * @param \DateTime|null $sunClose
     */
    public function setSunClose($sunClose)
    {
        $this->sunClose = $sunClose;
    }

    /**
     * @return int|null
     */
    public function getGlobalOpen()
    {
        return $this->globalOpen;
    }

    /**
     * @param int|null $globalOpen
     */
    public function setGlobalOpen($globalOpen)
    {
        $this->globalOpen = $globalOpen;
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
}
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MaintenanceInfo
 *
 * @ORM\Table(name="maintenance_info", indexes={@ORM\Index(name="IDX_49A519BF6C202BC", columns={"maintenance_id"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class MaintenanceInfo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="maintenance_info_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="info", type="text", nullable=true)
     */
    private $info;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var Maintenance
     *
     * @ORM\ManyToOne(targetEntity="Maintenance")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="maintenance_id", referencedColumnName="id")
     * })
     */
    private $maintenance;

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
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param string|null $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return Maintenance
     */
    public function getMaintenance()
    {
        return $this->maintenance;
    }

    /**
     * @param Maintenance $maintenance
     */
    public function setMaintenance($maintenance)
    {
        $this->maintenance = $maintenance;
    }

    /**
     * Gets triggered only on insert

     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime("now");
    }
}

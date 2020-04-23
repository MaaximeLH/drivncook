<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WarehouseCategory
 *
 * @ORM\Table(name="warehouse_category", indexes={@ORM\Index(name="IDX_26047B67727ACA70", columns={"parent_id"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class WarehouseCategory
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="warehouse_category_id_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \WarehouseCategory
     *
     * @ORM\ManyToOne(targetEntity="WarehouseCategory")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    private $parent;

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
     * @return \WarehouseCategory
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param \WarehouseCategory $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }
}

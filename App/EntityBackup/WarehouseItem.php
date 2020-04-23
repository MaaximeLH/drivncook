<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WarehouseItem
 *
 * @ORM\Table(name="warehouse_item", indexes={@ORM\Index(name="IDX_C07125CA64C19C1", columns={"category"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class WarehouseItem
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="warehouse_item_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var float|null
     *
     * @ORM\Column(name="price", type="float", precision=10, scale=0, nullable=true)
     */
    private $price;

    /**
     * @var float|null
     *
     * @ORM\Column(name="resale_price", type="float", precision=10, scale=0, nullable=true)
     */
    private $resalePrice;

    /**
     * @var float|null
     *
     * @ORM\Column(name="tva", type="float", precision=10, scale=0, nullable=true)
     */
    private $tva;

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
     *   @ORM\JoinColumn(name="category", referencedColumnName="id")
     * })
     */
    private $category;

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
     * @return float|null
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float|null $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float|null
     */
    public function getResalePrice()
    {
        return $this->resalePrice;
    }

    /**
     * @param float|null $resalePrice
     */
    public function setResalePrice($resalePrice)
    {
        $this->resalePrice = $resalePrice;
    }

    /**
     * @return float|null
     */
    public function getTva()
    {
        return $this->tva;
    }

    /**
     * @param float|null $tva
     */
    public function setTva($tva)
    {
        $this->tva = $tva;
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
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param \WarehouseCategory $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }
}

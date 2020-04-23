<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WarehouseStockItem
 *
 * @ORM\Table(name="warehouse_stock_item", indexes={@ORM\Index(name="IDX_5DE046891F1B251E", columns={"item"}), @ORM\Index(name="IDX_5DE04689ECB38BFC", columns={"warehouse"})})
 * @ORM\Entity
 */
class WarehouseStockItem
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="warehouse_stock_item_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(name="quantity", type="integer", nullable=true)
     */
    private $quantity;

    /**
     * @var WarehouseItem
     *
     * @ORM\ManyToOne(targetEntity="WarehouseItem")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item", referencedColumnName="id")
     * })
     */
    private $item;

    /**
     * @var Warehouse
     *
     * @ORM\ManyToOne(targetEntity="Warehouse")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="warehouse", referencedColumnName="id")
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
     * @return int|null
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int|null $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return WarehouseItem
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param WarehouseItem $item
     */
    public function setItem($item)
    {
        $this->item = $item;
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

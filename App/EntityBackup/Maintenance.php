<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Maintenance
 *
 * @ORM\Table(name="maintenance", indexes={@ORM\Index(name="IDX_2F84F8E9C6957CCE", columns={"truck_id"}), @ORM\Index(name="IDX_2F84F8E990651744", columns={"invoice"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Maintenance
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="maintenance_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=false)
     */
    private $status;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \Trucks
     *
     * @ORM\ManyToOne(targetEntity="Truck")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="truck_id", referencedColumnName="id")
     * })
     */
    private $truck;

    /**
     * @var \Invoice
     *
     * @ORM\ManyToOne(targetEntity="Invoice")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="invoice", referencedColumnName="id")
     * })
     */
    private $invoice;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
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
     * @return \Truck
     */
    public function getTruck()
    {
        return $this->truck;
    }

    /**
     * @param \Truck $truck
     */
    public function setTruck($truck)
    {
        $this->truck = $truck;
    }

    /**
     * @return \Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param \Invoice $invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
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

<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * FidelityCard
 *
 * @ORM\Table(name="fidelity_card")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class FidelityCard
{
    /**
     * @var int|null
     *
     * @ORM\Column(name="nb_point", type="integer", nullable=true)
     */
    private $nbPoint;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="end_at", type="datetime", nullable=true)
     */
    private $endAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var Customer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Customer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $customer;

    /**
     * Gets triggered only on insert

     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime("now");
    }

    /**
     * @return int|null
     */
    public function getNbPoint(): int
    {
        return $this->nbPoint;
    }

    /**
     * @param int|null $nbPoint
     */
    public function setNbPoint(int $nbPoint)
    {
        $this->nbPoint = $nbPoint;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndAt(): \DateTime
    {
        return $this->endAt;
    }

    /**
     * @param \DateTime|null $endAt
     */
    public function setEndAt(\DateTime $endAt)
    {
        $this->endAt = $endAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }
}

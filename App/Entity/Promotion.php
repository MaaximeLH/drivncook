<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Promotion
 *
 * @ORM\Table(name="promotion", indexes={@ORM\Index(name="IDX_C11D7DD1A76ED395", columns={"user_id"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Promotion
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="promotion_id_seq", allocationSize=1, initialValue=1)
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
     * @var float|null
     *
     * @ORM\Column(name="min_price", type="float", precision=10, scale=0, nullable=true)
     */
    private $minPrice;

    /**
     * @var float|null
     *
     * @ORM\Column(name="max_price", type="float", precision=10, scale=0, nullable=true)
     */
    private $maxPrice;

    /**
     * @var float|null
     *
     * @ORM\Column(name="reduc_percentage", type="float", precision=10, scale=0, nullable=true)
     */
    private $reducPercentage;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

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
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return \DateTime|null
     */
    public function getStartAt(): \DateTime
    {
        return $this->startAt;
    }

    /**
     * @param \DateTime|null $startAt
     */
    public function setStartAt(\DateTime $startAt)
    {
        $this->startAt = $startAt;
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
     * @return float|null
     */
    public function getMinPrice(): float
    {
        return $this->minPrice;
    }

    /**
     * @param float|null $minPrice
     */
    public function setMinPrice(float $minPrice)
    {
        $this->minPrice = $minPrice;
    }

    /**
     * @return float|null
     */
    public function getMaxPrice(): float
    {
        return $this->maxPrice;
    }

    /**
     * @param float|null $maxPrice
     */
    public function setMaxPrice(float $maxPrice)
    {
        $this->maxPrice = $maxPrice;
    }

    /**
     * @return float|null
     */
    public function getReducPercentage(): float
    {
        return $this->reducPercentage;
    }

    /**
     * @param float|null $reducPercentage
     */
    public function setReducPercentage(float $reducPercentage)
    {
        $this->reducPercentage = $reducPercentage;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): \DateTime
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
     * @return Users
     */
    public function getUser(): Users
    {
        return $this->user;
    }

    /**
     * @param Users $user
     */
    public function setUser(Users $user)
    {
        $this->user = $user;
    }
}

<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * FidelityCard
 *
 * @ORM\Table(name="fidelity_card")
 * @ORM\Entity
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
     * @var \Customer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Customer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $customer;


}

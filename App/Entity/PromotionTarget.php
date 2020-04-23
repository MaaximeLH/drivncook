<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * PromotionTarget
 *
 * @ORM\Table(name="promotion_target", indexes={@ORM\Index(name="IDX_53D504C14575D828", columns={"card_item_id"}), @ORM\Index(name="IDX_53D504C1BF7DD742", columns={"card_category"})})
 * @ORM\Entity
 */
class PromotionTarget
{
    /**
     * @var \Promotion
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Promotion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="promotion_id", referencedColumnName="id")
     * })
     */
    private $promotion;

    /**
     * @var \CardItem
     *
     * @ORM\ManyToOne(targetEntity="CardItem")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="card_item_id", referencedColumnName="id")
     * })
     */
    private $cardItem;

    /**
     * @var \CardCategory
     *
     * @ORM\ManyToOne(targetEntity="CardCategory")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="card_category", referencedColumnName="id")
     * })
     */
    private $cardCategory;


}

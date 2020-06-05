<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class CardRepository extends EntityRepository
{
    public function getCardWithCategoryAndItem($id)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('card.id as card_id, card.name as card_name, card_category.name as card_category_name, card_item.name as card_item_name, card_item.price as card_item_price, card_item.id as card_item_id')
            ->from('App\Entity\Card', 'card')
            ->leftJoin(
                'App\Entity\CardCategory',
                'card_category',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'card_category.card = card.id'
            )
            ->leftJoin(
                'App\Entity\CardItem',
                'card_item',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'card_item.category = card_category.id'
            )
            ->where('card.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }
}

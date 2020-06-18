<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class EventRepository extends EntityRepository
{
    public function getEventSubscribed($user_id)
    {
        $result = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('event.id')
            ->from('App\Entity\Event', 'event')
            ->leftJoin(
                'App\Entity\EventUser',
                'event_user',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'event_user.user = :user_id'
            )
            ->where('event.id = event_user.event')
            ->setParameter('user_id', $user_id)
            ->getQuery()
            ->getResult();
        $newtab = [];
        foreach ($result as $k => $value) {
            $newtab[$k] = $value['id'];
        }
        return $newtab;
    }
}

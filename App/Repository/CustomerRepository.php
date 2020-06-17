<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class CustomerRepository extends EntityRepository
{
    public function search(string $query)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('customer.id, customer.firstName, customer.lastName, customer.email')
            ->from('App\Entity\Customer', 'customer')

            ->where('LOWER(customer.firstName) LIKE LOWER(:query)')
            ->orwhere('LOWER(customer.lastName) LIKE LOWER(:query)')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }
}

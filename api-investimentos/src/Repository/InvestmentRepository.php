<?php

namespace App\Repository;

use App\Entity\Owner;
use App\Entity\Investment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Investment>
 */
class InvestmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Investment::class);
    }

    /**
        * Retorna investimentos paginados para um owner
        *
        * @param Owner $owner
        * @param int $page  Página (1-based)
        * @param int $limit Itens por página
        * @return array ['items' => Investment[], 'total' => int, 'pages' => int, 'page' => int, 'limit' => int]
    */

    public function findPaginatedByOwner(Owner $owner, int $page = 1, int $limit = 10): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);
        $offset = ($page - 1) * $limit;

        $qb = $this->createQueryBuilder('i')
            ->andWhere('i.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('i.creationDate', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $query = $qb->getQuery();

        $paginator = new Paginator($query, true);
        $total = count($paginator);

        $items = iterator_to_array($paginator->getIterator());

        $pages = ($limit > 0) ? (int) ceil($total / $limit) : 0;

        return [
            'items' => $items,
            'total' => (int) $total,
            'pages' => $pages,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    //    /**
    //     * @return Investment[] Returns an array of Investment objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Investment
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

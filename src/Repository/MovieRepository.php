<?php

namespace App\Repository;

use App\Entity\Movie;
use App\Entity\Vote;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;


/**
 * @extends ServiceEntityRepository<Movie>
 */
class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    /**
     * Return ordered movies
     * @param string $sortBy
     * @param string $sortOrder
     * @return QueryBuilder
     */
    public function getSortedQueryBuilder(string $sortBy = 'created_at', string $sortOrder = 'desc'): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m');

        switch ($sortBy) {
            case 'likes':
                $qb->leftJoin('m.votes', 'v', Join::WITH, 'v.type = :type')
                    ->setParameter('type', Vote::TYPE_LIKE)
                    ->groupBy('m.id')
                    ->orderBy('COUNT(v.id)', $sortOrder);
                break;

            case 'hates':
                $qb->leftJoin('m.votes', 'v', Join::WITH, 'v.type = :type')
                    ->setParameter('type', Vote::TYPE_HATE)
                    ->groupBy('m.id')
                    ->orderBy('COUNT(v.id)', $sortOrder);
                break;

            case 'updated_at':
                $qb->orderBy('m.updated_at', $sortOrder);
                break;

            case 'created_at':
            default:
                $qb->orderBy('m.created_at', $sortOrder);
                break;
        }

        return $qb;
    }

//    /**
//     * @return Movie[] Returns an array of Movie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Movie
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

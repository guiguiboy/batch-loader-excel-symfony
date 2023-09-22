<?php

namespace App\Repository;

use App\Entity\LineFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LineFile>
 *
 * @method LineFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method LineFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method LineFile[]    findAll()
 * @method LineFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LineFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LineFile::class);
    }

    public function removeAll(): int
    {
        return $this->createQueryBuilder('l')
            ->delete()
            ->getQuery()
            ->execute()
            ;
    }

//    /**
//     * @return LineFile[] Returns an array of LineFile objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?LineFile
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evenement>
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * Find upcoming events that haven't ended yet
     *
     * @param int|null $limit Maximum number of events to return
     * @return Evenement[] Returns an array of Evenement objects
     */
    public function findUpcomingEvents(?int $limit = null): array
    {
        $today = new \DateTime();
        
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.dateDebut >= :today')
            ->andWhere('e.statut NOT IN (:excluded_statuses)')
            ->setParameter('today', $today)
            ->setParameter('excluded_statuses', ['Annulé', 'Terminé', 'cancelled'])
            ->orderBy('e.dateDebut', 'ASC');
            
        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Finds all events and sorts them by date
     */
    public function findAllSorted(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.statut NOT IN (:excluded_statuses)')
            ->setParameter('excluded_statuses', ['Annulé', 'Terminé', 'cancelled'])
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Evenement[] Returns an array of Evenement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Evenement
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

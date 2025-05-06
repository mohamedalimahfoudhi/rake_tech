<?php

namespace App\Repository;

use App\Entity\Billet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Billet>
 */
class BilletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Billet::class);
    }

    /**
     * Find available tickets for an event
     *
     * @param int $eventId
     * @return array
     */
    public function findAvailableTicketsByEvent(int $eventId): array
    {
        // Debug message to log
        error_log("Finding tickets for event ID: " . $eventId);
        
        // Try direct query without status constraint
        $tickets = $this->findBy([
            'eventID' => $eventId
        ]);
        
        if (!empty($tickets)) {
            error_log("Found " . count($tickets) . " tickets directly");
            return $tickets;
        }
        
        error_log("No tickets found with direct query, trying QueryBuilder");
        
        // Fallback to a more flexible query without status constraint
        return $this->createQueryBuilder('b')
            ->andWhere('b.eventID = :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('b.prix', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find tickets by their IDs
     *
     * @param array $ids
     * @return array
     */
    public function findTicketsByIds(array $ids): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count available tickets for an event
     *
     * @param int $eventId
     * @return int
     */
    public function countAvailableTickets(int $eventId): int
    {
        return $this->createQueryBuilder('b')
            ->select('SUM(b.quantite)')
            ->andWhere('b.eventID = :eventId')
            ->setParameter('eventId', $eventId)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }

    /**
     * Save or update a ticket
     *
     * @param Billet $entity
     * @param bool $flush
     * @return void
     */
    public function save(Billet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove a ticket
     *
     * @param Billet $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Billet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return Billet[] Returns an array of Billet objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Billet
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

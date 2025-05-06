<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Find all ticket reservations by user ID
     *
     * @param int $userId
     * @return array
     */
    public function findTicketReservationsByUser(int $userId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.utilisateurID = :userId')
            ->andWhere('r.type = :type')
            ->setParameter('userId', $userId)
            ->setParameter('type', 'BILLET')
            ->orderBy('r.dateReservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all active ticket reservations by user ID
     *
     * @param int $userId
     * @return array
     */
    public function findActiveTicketReservationsByUser(int $userId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.utilisateurID = :userId')
            ->andWhere('r.type = :type')
            ->andWhere('r.statut = :statut')
            ->setParameter('userId', $userId)
            ->setParameter('type', 'BILLET')
            ->setParameter('statut', 'Confirmée')
            ->orderBy('r.dateReservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all reservations with ticket information
     *
     * @return array
     */
    public function findAllReservationsWithTickets(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.type = :type')
            ->setParameter('type', 'BILLET')
            ->orderBy('r.dateReservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Save or update a reservation
     *
     * @param Reservation $entity
     * @param bool $flush
     * @return void
     */
    public function save(Reservation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove a reservation
     *
     * @param Reservation $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Reservation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

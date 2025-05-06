<?php

namespace App\Repository;

use App\Entity\ReservationBillet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReservationBillet>
 */
class ReservationBilletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationBillet::class);
    }

    /**
     * Find all ticket reservations with join to ticket and event information
     *
     * @param int $reservationId
     * @return array
     */
    public function findTicketsWithEventInfo(int $reservationId): array
    {
        return $this->createQueryBuilder('rb')
            ->join('rb.billet', 'b')
            ->join('b.evenement', 'e')
            ->andWhere('rb.reservationID = :reservationId')
            ->setParameter('reservationId', $reservationId)
            ->select('rb, b, e')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all ticket reservations by user
     *
     * @param int $userId
     * @return array
     */
    public function findTicketsByUser(int $userId): array
    {
        return $this->createQueryBuilder('rb')
            ->join('rb.reservation', 'r')
            ->join('rb.billet', 'b')
            ->join('b.evenement', 'e')
            ->andWhere('r.utilisateurID = :userId')
            ->andWhere('r.type = :type')
            ->setParameter('userId', $userId)
            ->setParameter('type', 'BILLET')
            ->select('rb, r, b, e')
            ->orderBy('r.dateReservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count tickets booked for an event
     *
     * @param int $eventId
     * @return int
     */
    public function countTicketsForEvent(int $eventId): int
    {
        return (int) $this->createQueryBuilder('rb')
            ->join('rb.billet', 'b')
            ->andWhere('b.eventID = :eventId')
            ->setParameter('eventId', $eventId)
            ->select('SUM(rb.nombreBillet)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Save a ReservationBillet entity
     *
     * @param ReservationBillet $entity
     * @param bool $flush
     * @return void
     */
    public function save(ReservationBillet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove a ReservationBillet entity
     *
     * @param ReservationBillet $entity
     * @param bool $flush
     * @return void
     */
    public function remove(ReservationBillet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
} 
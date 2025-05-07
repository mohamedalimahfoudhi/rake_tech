<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 *
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Trouve les réservations par utilisateur
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.utilisateurid = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('r.datereservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les réservations par type
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.type = :type')
            ->setParameter('type', $type)
            ->orderBy('r.datereservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les réservations par statut
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.statut = :status')
            ->setParameter('status', $status)
            ->orderBy('r.datereservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les réservations entre deux dates
     */
    public function findByDateRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.datereservation BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('r.datereservation', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les réservations récentes
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.datereservation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les réservations actives (non annulées)
     */
    public function findActive(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.statut != :status')
            ->setParameter('status', 'Annulée')
            ->orderBy('r.datereservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des réservations par utilisateur et type
     */
    public function findByUserAndType(int $userId, string $type): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.utilisateurid = :userId')
            ->andWhere('r.type = :type')
            ->setParameter('userId', $userId)
            ->setParameter('type', $type)
            ->orderBy('r.datereservation', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 
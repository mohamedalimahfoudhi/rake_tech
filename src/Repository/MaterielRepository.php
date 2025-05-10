<?php

namespace App\Repository;

use App\Entity\Materiel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Materiel>
 *
 * @method Materiel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Materiel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Materiel[]    findAll()
 * @method Materiel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaterielRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Materiel::class);
    }

    /**
     * Trouve tout le matériel disponible
     *
     * @return Materiel[]
     */
    public function findAllAvailable(): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.disponible = :disponible')
            ->setParameter('disponible', true)
            ->orderBy('m.type', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve le matériel par type
     *
     * @param string $type
     * @return Materiel[]
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.type = :type')
            ->setParameter('type', $type)
            ->orderBy('m.marque', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve le matériel par état
     *
     * @param string $etat
     * @return Materiel[]
     */
    public function findByEtat(string $etat): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.etat = :etat')
            ->setParameter('etat', $etat)
            ->orderBy('m.type', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche de matériel avec filtres
     *
     * @param array $filters
     * @return Materiel[]
     */
    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('m');

        if (isset($filters['type'])) {
            $qb->andWhere('m.type = :type')
               ->setParameter('type', $filters['type']);
        }

        if (isset($filters['marque'])) {
            $qb->andWhere('m.marque = :marque')
               ->setParameter('marque', $filters['marque']);
        }

        if (isset($filters['etat'])) {
            $qb->andWhere('m.etat = :etat')
               ->setParameter('etat', $filters['etat']);
        }

        if (isset($filters['disponible'])) {
            $qb->andWhere('m.disponible = :disponible')
               ->setParameter('disponible', $filters['disponible']);
        }

        return $qb->orderBy('m.type', 'ASC')
                 ->getQuery()
                 ->getResult();
    }

    /**
     * Trouve le matériel qui nécessite une maintenance
     * (état "Mauvais" ou maintenance planifiée)
     *
     * @return Materiel[]
     */
    public function findNeedingMaintenance(): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.maintenances', 'maint')
            ->where('m.etat = :etat')
            ->orWhere('maint.datePrevue IS NOT NULL AND maint.dateRealisation IS NULL')
            ->setParameter('etat', 'Mauvais')
            ->orderBy('m.type', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve le matériel le plus emprunté
     *
     * @param int $limit Nombre de résultats à retourner
     * @return array
     */
    public function findMostBorrowed(int $limit = 10): array
    {
        return $this->createQueryBuilder('m')
            ->select('m', 'COUNT(e.id) as emprunt_count')
            ->leftJoin('m.emprunts', 'e')
            ->groupBy('m.id')
            ->orderBy('emprunt_count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques sur le matériel
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $totalMateriel = $this->createQueryBuilder('m')
            ->select('COUNT(m.id) as total')
            ->getQuery()
            ->getSingleScalarResult();

        $disponible = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.disponible = :disponible')
            ->setParameter('disponible', true)
            ->getQuery()
            ->getSingleScalarResult();

        $parEtat = $this->createQueryBuilder('m')
            ->select('m.etat, COUNT(m.id) as count')
            ->groupBy('m.etat')
            ->getQuery()
            ->getResult();

        return [
            'total' => $totalMateriel,
            'disponible' => $disponible,
            'indisponible' => $totalMateriel - $disponible,
            'par_etat' => $parEtat
        ];
    }

    /**
     * Sauvegarde un matériel
     *
     * @param Materiel $materiel
     * @return void
     */
    public function save(Materiel $materiel): void
    {
        $this->_em->persist($materiel);
        $this->_em->flush();
    }

    /**
     * Supprime un matériel
     *
     * @param Materiel $materiel
     * @return void
     */
    public function remove(Materiel $materiel): void
    {
        $this->_em->remove($materiel);
        $this->_em->flush();
    }
} 
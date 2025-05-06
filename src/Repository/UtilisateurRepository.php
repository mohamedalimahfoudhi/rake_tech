<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setMotdepasse($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Loads user by identifier (email in our case)
     * Required by UserLoaderInterface
     */
    public function loadUserByIdentifier(string $identifier): ?Utilisateur
    {
        return $this->createQueryBuilder('u')
            ->select('u, r')
            ->andWhere('LOWER(u.email) = LOWER(:email)')
            ->setParameter('email', $identifier)
            ->leftJoin('u.role', 'r')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Directly compare a password with what's in the database
     * This is useful for debugging authentication issues
     */
    public function validateCredentials(string $email, string $password): bool
    {
        $user = $this->createQueryBuilder('u')
            ->andWhere('LOWER(u.email) = LOWER(:email)')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
        
        if (!$user) {
            error_log("User not found for email: " . $email);
            return false;
        }
        
        // Debugging info - uncommented to debug password issues
        error_log("DB Password: " . $user->getMotdepasse());
        error_log("Input Password: " . $password);
        error_log("Match: " . ($user->getMotdepasse() === $password ? "YES" : "NO"));
        
        // Simple string comparison for plaintext passwords
        return $user->getMotdepasse() === $password;
    }

    /**
     * Find a user by their email with their role joined
     */
    public function findByEmail(string $email): ?Utilisateur
    {
        return $this->createQueryBuilder('u')
            ->select('u, r')
            ->andWhere('LOWER(u.email) = LOWER(:email)')
            ->setParameter('email', $email)
            ->leftJoin('u.role', 'r')
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Utilisateur[] Returns an array of Utilisateur objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Utilisateur
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

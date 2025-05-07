<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserManagementFormType;
use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Psr\Log\LoggerInterface;

class UserManagementController extends AbstractController
{
    private $logger;
    private $paginator;

    public function __construct(LoggerInterface $logger, PaginatorInterface $paginator)
    {
        $this->logger = $logger;
        $this->paginator = $paginator;
    }

    #[Route('/user-management', name: 'app_user_management')]
    public function index(Request $request, UserRepository $userRepository, RoleRepository $roleRepository): Response
    {
        $search = $request->query->get('search');
        $selectedRole = $request->query->get('role');
        
        // Get all roles for the filter dropdown
        $roles = $roleRepository->findAll();
        
        // Create query builder
        $qb = $userRepository->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->orderBy('u.id', 'DESC');
        
        // Add search condition if search term exists
        if ($search) {
            $qb->andWhere('u.email LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search OR u.numeroTelephone LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        
        // Add role filter if role is selected
        if ($selectedRole) {
            $qb->andWhere('r.roleID = :roleId')
               ->setParameter('roleId', $selectedRole);
        }
        
        // Create pagination
        $pagination = $this->paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10 // items per page
        );
        
        return $this->render('user_management/index.html.twig', [
            'pagination' => $pagination,
            'search' => $search,
            'roles' => $roles,
            'selected_role' => $selectedRole
        ]);
    }

    #[Route('/admin/users/{id}/edit', name: 'app_user_edit')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        try {
            $form = $this->createForm(UserManagementFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Handle password change if provided
                if ($form->get('plainPassword')->getData()) {
                    $user->setPassword(
                        $passwordHasher->hashPassword(
                            $user,
                            $form->get('plainPassword')->getData()
                        )
                    );
                }

                $entityManager->flush();

                $this->addFlash('success', 'User updated successfully!');
                return $this->redirectToRoute('app_user_management');
            }

            return $this->render('user_management/edit.html.twig', [
                'userForm' => $form->createView(),
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error updating user', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'An error occurred while updating the user.');
            return $this->redirectToRoute('app_user_management');
        }
    }

    #[Route('/admin/users/{id}/delete', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        try {
            if (!$this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
                $this->logger->error('Invalid CSRF token for user deletion', [
                    'user_id' => $user->getId(),
                    'ip' => $request->getClientIp()
                ]);
                $this->addFlash('error', 'Invalid security token.');
                return $this->redirectToRoute('app_user_management');
            }

            // Prevent deleting the last admin
            if ($user->getRole()->getRoleNom() === 'ADMIN') {
                $adminCount = $entityManager->getRepository(User::class)
                    ->createQueryBuilder('u')
                    ->join('u.role', 'r')
                    ->where('r.roleNom = :role')
                    ->setParameter('role', 'ADMIN')
                    ->select('COUNT(u.id)')
                    ->getQuery()
                    ->getSingleScalarResult();

                if ($adminCount <= 1) {
                    $this->logger->warning('Attempt to delete the last admin user', [
                        'user_id' => $user->getId(),
                        'email' => $user->getEmail()
                    ]);
                    $this->addFlash('error', 'Cannot delete the last admin user.');
                    return $this->redirectToRoute('app_user_management');
                }
            }

            $entityManager->remove($user);
            $entityManager->flush();
            
            $this->logger->info('User deleted successfully', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'deleted_by' => $this->getUser()->getUserIdentifier()
            ]);
            
            $this->addFlash('success', 'User deleted successfully!');
        } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {
            $this->logger->error('Foreign key constraint violation while deleting user', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            $this->addFlash('error', 'Cannot delete user: This user has associated records that need to be handled first.');
        } catch (\Exception $e) {
            $this->logger->error('Error deleting user', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'An error occurred while deleting the user. Please try again or contact support.');
        }

        return $this->redirectToRoute('app_user_management');
    }
} 
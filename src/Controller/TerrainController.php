<?php

namespace App\Controller;

use App\Entity\Terrain;
use App\Form\TerrainType;
use App\Repository\TerrainRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/terrain')]
final class TerrainController extends AbstractController
{
    #[Route(name: 'app_terrain_index', methods: ['GET'])]
    public function index(TerrainRepository $terrainRepository): Response
    {
        return $this->render('terrain/index.html.twig', [
            'terrains' => $terrainRepository->findAll(),
        ]);
    }
    #[Route('/front', name: 'app_terrain_indexf', methods: ['GET'])]
    public function indexf(TerrainRepository $terrainRepository): Response
    {
        return $this->render('terrain/indexf.html.twig', [
            'terrains' => $terrainRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_terrain_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $terrain = new Terrain();
        $form = $this->createForm(TerrainType::class, $terrain);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($terrain);
            $entityManager->flush();

            return $this->redirectToRoute('app_terrain_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('terrain/new.html.twig', [
            'terrain' => $terrain,
            'form' => $form,
        ]);
    }
    #[Route('/terrain/map', name: 'terrain_map')]
    public function showMap(): Response
    {
        return $this->render('event/map.html.twig');
    }
    #[Route('/map', name: 'app_map')]
    public function map(TerrainRepository $terrainRepository): Response
    {
        // Récupérer tous les terrains
        $terrains = $terrainRepository->findAll();

        // Récupérer les coordonnées et les autres informations si nécessaires
        $coordinates = [];
        foreach ($terrains as $terrain) {
            $coordinates[] = [
                'latitude' => $terrain->getLatitude(),
                'longitude' => $terrain->getLongitude(),
                'name' => $terrain->getName(),
            ];
        }

        // Passer les données des terrains à la vue
        return $this->render('map/index.html.twig', [
            'terrains' => $coordinates,  // Passer les coordonnées à la vue
        ]);
    }
    #[Route('/mapb', name: 'app_mapb')]
    public function mapb(TerrainRepository $terrainRepository): Response
    {
        // Récupérer tous les terrains
        $terrains = $terrainRepository->findAll();

        // Récupérer les coordonnées et les autres informations si nécessaires
        $coordinates = [];
        foreach ($terrains as $terrain) {
            $coordinates[] = [
                'latitude' => $terrain->getLatitude(),
                'longitude' => $terrain->getLongitude(),
                'name' => $terrain->getName(),
            ];
        }

        // Passer les données des terrains à la vue
        return $this->render('map/indexb.html.twig', [
            'terrains' => $coordinates,  // Passer les coordonnées à la vue
        ]);
    }


    #[Route('/api/terrains', name: 'api_terrain_list', methods: ['GET'])]
    public function apiList(TerrainRepository $terrainRepository): JsonResponse
    {
        $terrains = $terrainRepository->findAll();
        $data = [];

        foreach ($terrains as $terrain) {
            $data[] = [
                'id' => $terrain->getId(),
                'name' => $terrain->getName(),
                'latitude' => $terrain->getLatitude(),
                'longitude' => $terrain->getLongitude(),
            ];
        }

        return $this->json($data);
    }



    #[Route('/{id}', name: 'app_terrain_show', methods: ['GET'])]
    public function show(Terrain $terrain): Response
    {
        return $this->render('terrain/show.html.twig', [
            'terrain' => $terrain,
        ]);
    }
    #[Route('/front/{id}', name: 'app_terrain_showf', methods: ['GET'])]
    public function showf(Terrain $terrain): Response
    {
        return $this->render('terrain/showf.html.twig', [
            'terrain' => $terrain,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_terrain_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Terrain $terrain, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TerrainType::class, $terrain);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_terrain_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('terrain/edit.html.twig', [
            'terrain' => $terrain,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_terrain_delete', methods: ['POST'])]
    public function delete(Request $request, Terrain $terrain, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $terrain->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($terrain);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_terrain_index', [], Response::HTTP_SEE_OTHER);
    }
}

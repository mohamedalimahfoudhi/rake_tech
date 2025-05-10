<?php
// src/Controller/ChatbotController.php
namespace App\Controller;

use App\Service\TournoiChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ChatbotController extends AbstractController
{
    private $tournoiChatbotService;

    public function __construct(TournoiChatbotService $tournoiChatbotService)
    {
        $this->tournoiChatbotService = $tournoiChatbotService;
    }

    #[Route('/chatbot/tournoi', name: 'chatbot_tournoi', methods: ['POST'])]
    public function chatbot(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $userMessage = $data['message'] ?? '';
    
        // Nettoyage de la question et extraction du nom du tournoi
        $tournoiName = $this->extractTournoiName($userMessage);
    
        if (strpos(strtolower($userMessage), 'statut') !== false) {
            $response = $this->tournoiChatbotService->getTournoiStatus($tournoiName);
        } elseif (strpos(strtolower($userMessage), 'date') !== false) {
            $response = $this->tournoiChatbotService->getTournoiDate($tournoiName);
        } elseif (strpos(strtolower($userMessage), 'récompense') !== false) {
            $response = $this->tournoiChatbotService->getTournoiRecompense($tournoiName);
        } elseif (strpos(strtolower($userMessage), 'en cours') !== false) {
            $response = $this->tournoiChatbotService->getTournoisEnCours();
        } else {
            $response = 'Désolé, je ne comprends pas cette question.';
        }
    
        return new JsonResponse(['response' => $response]);
    }
    
    // Méthode pour extraire le nom du tournoi de la question
    private function extractTournoiName(string $userMessage): string
    {
        // Supposons que le nom du tournoi est toujours le dernier mot
        $words = explode(' ', $userMessage);  // Divise la question en mots
        return strtolower(end($words));  // Prend le dernier mot, en minuscules
    }
}    
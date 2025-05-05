<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ChatbotPageController extends AbstractController
{

    #[Route('/chatbot', name: 'chatbot_page', methods: ['GET'])]
    public function index()
    {
        return $this->render('chatbot/chatbot.html.twig');
    }
}
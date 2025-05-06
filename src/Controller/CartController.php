<?php

namespace App\Controller;

use App\Entity\Billet;
use App\Repository\BilletRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    private $cartService;
    
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
    
    #[Route('/', name: 'cart_index')]
    public function index(): Response
    {
        $cart = $this->cartService->getCart();
        $total = $this->cartService->getTotal();
        
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'total' => $total
        ]);
    }
    
    #[Route('/add/{id}', name: 'cart_add')]
    public function add(Billet $billet, Request $request): Response
    {
        $quantity = (int) $request->query->get('quantity', 1);
        
        if ($quantity <= 0) {
            $quantity = 1;
        }
        
        $this->cartService->addToCart($billet, $quantity);
        
        $this->addFlash('success', 'Ticket added successfully');
        
        // Redirect to the referer URL or cart page if not available
        $referer = $request->headers->get('referer');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('cart_index');
    }
    
    #[Route('/remove/{id}', name: 'cart_remove')]
    public function remove(int $id): Response
    {
        $this->cartService->removeFromCart($id);
        
        $this->addFlash('success', 'Ticket removed successfully');
        
        return $this->redirectToRoute('cart_index');
    }
    
    #[Route('/update/{id}', name: 'cart_update', methods: ['POST'])]
    public function update(int $id, Request $request): Response
    {
        $quantity = (int) $request->request->get('quantity', 1);
        
        if ($quantity <= 0) {
            $quantity = 1;
        }
        
        $this->cartService->updateQuantity($id, $quantity);
        
        return $this->redirectToRoute('cart_index');
    }
    
    #[Route('/clear', name: 'cart_clear')]
    public function clear(): Response
    {
        $this->cartService->clearCart();
        
        $this->addFlash('success', 'All tickets cleared');
        
        return $this->redirectToRoute('cart_index');
    }
} 
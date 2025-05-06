<?php

namespace App\Service;

use App\Entity\Billet;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private $requestStack;
    
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
    
    /**
     * Get the current cart from the session
     */
    public function getCart(): array
    {
        $session = $this->requestStack->getSession();
        return $session->get('cart', []);
    }
    
    /**
     * Get the number of items in the cart
     */
    public function getCartCount(): int
    {
        $cart = $this->getCart();
        $count = 0;
        
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }
        
        return $count;
    }
    
    /**
     * Add a ticket to the cart
     */
    public function addToCart(Billet $billet, int $quantity = 1): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        $billetId = $billet->getId();
        
        if (isset($cart[$billetId])) {
            $cart[$billetId]['quantity'] += $quantity;
        } else {
            $cart[$billetId] = [
                'id' => $billetId,
                'type' => $billet->getTypeBillet(),
                'price' => $billet->getPrix(),
                'quantity' => $quantity,
                'event_name' => $billet->getEvenement() ? $billet->getEvenement()->getNom() : 'Unknown Event',
                'event_id' => $billet->getEventID()
            ];
        }
        
        $session->set('cart', $cart);
    }
    
    /**
     * Remove a ticket from the cart
     */
    public function removeFromCart(int $billetId): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        
        if (isset($cart[$billetId])) {
            unset($cart[$billetId]);
            $session->set('cart', $cart);
        }
    }
    
    /**
     * Update the quantity of a ticket in the cart
     */
    public function updateQuantity(int $billetId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeFromCart($billetId);
            return;
        }
        
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        
        if (isset($cart[$billetId])) {
            $cart[$billetId]['quantity'] = $quantity;
            $session->set('cart', $cart);
        }
    }
    
    /**
     * Clear the entire cart
     */
    public function clearCart(): void
    {
        $session = $this->requestStack->getSession();
        $session->remove('cart');
    }
    
    /**
     * Calculate the total price of the cart
     */
    public function getTotal(): float
    {
        $cart = $this->getCart();
        $total = 0;
        
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return $total;
    }
} 
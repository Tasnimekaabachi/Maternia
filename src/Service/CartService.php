<?php
// src/Service/CartService.php

namespace App\Service;

use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private $session;
    private $produitRepository;

    public function __construct(RequestStack $requestStack, ProduitRepository $produitRepository)
    {
        $this->session = $requestStack->getSession();
        $this->produitRepository = $produitRepository;
    }

    public function getCart(): array
    {
        return $this->session->get('cart', []);
    }

    public function getCartDetails(): array
    {
        $cart = $this->getCart();
        $produits = [];
        $total = 0;

        if (!empty($cart)) {
            $produits = $this->produitRepository->findBy(['id' => $cart]);
            foreach ($produits as $produit) {
                $total += $produit->getPrix() ?? 0;
            }
        }

        return [
            'produits' => $produits,
            'total' => $total,
            'count' => count($cart)
        ];
    }
}
<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier')]
final class CartController extends AbstractController
{
    #[Route('', name: 'app_cart_show', methods: ['GET'])]
    public function show(Request $request, ProduitRepository $produitRepository): Response
    {
        $session = $request->getSession();
        /** @var int[] $cart */
        $cart = $session->get('cart', []);

        $produits = [];
        $total = 0;

        if (!empty($cart)) {
            $produits = $produitRepository->findBy(['id' => $cart]);
            foreach ($produits as $produit) {
                $total += $produit->getPrix() ?? 0;
            }
        }

        return $this->render('pages/cart.html.twig', [
            'produits' => $produits,
            'total' => $total,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST', 'GET'])]
    public function add(
        int $id,
        Request $request,
        ProduitRepository $produitRepository,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $session = $request->getSession();
        /** @var int[] $cart */
        $cart = $session->get('cart', []);

        // Vérifier si le produit existe
        $produit = $produitRepository->find($id);
        if (!$produit) {
            $this->addFlash('danger', 'Produit introuvable.');
            return $this->redirectToRoute('app_marketplace');
        }

        // Vérifier le stock disponible
        if ($produit->getStock() <= 0) {
            $this->addFlash('warning', 'Ce produit est en rupture de stock.');
            return $this->redirectToRoute('app_marketplace');
        }

        // Vérifier si le produit n'est pas déjà dans le panier
        if (!in_array($id, $cart, true)) {
            // Diminuer le stock de 1
            $produit->setStock($produit->getStock() - 1);
            $entityManager->persist($produit);
            $entityManager->flush();

            // Ajouter au panier
            $cart[] = $id;
            $session->set('cart', $cart);
            $this->addFlash('success', 'Produit ajouté au panier. Stock mis à jour.');
        } else {
            $this->addFlash('info', 'Ce produit est déjà dans votre panier.');
        }

        return $this->redirectToRoute('app_marketplace');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST', 'GET'])]
    public function remove(
        int $id,
        Request $request,
        ProduitRepository $produitRepository,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $session = $request->getSession();
        /** @var int[] $cart */
        $cart = $session->get('cart', []);

        // Vérifier si le produit existe et est dans le panier
        $produit = $produitRepository->find($id);
        if ($produit && in_array($id, $cart, true)) {
            // Augmenter le stock de 1
            $produit->setStock($produit->getStock() + 1);
            $entityManager->persist($produit);
            $entityManager->flush();
        }

        // Retirer du panier
        $cart = array_values(array_filter($cart, static fn (int $productId) => $productId !== $id));
        $session->set('cart', $cart);

        $this->addFlash('success', 'Produit retiré du panier. Stock mis à jour.');

        return $this->redirectToRoute('app_cart_show');
    }

    #[Route('/checkout', name: 'app_cart_checkout', methods: ['POST'])]
    public function checkout(
        Request $request,
        ProduitRepository $produitRepository,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $session = $request->getSession();
        /** @var int[] $cart */
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart_show');
        }

        $produits = $produitRepository->findBy(['id' => $cart]);

        if (empty($produits)) {
            $this->addFlash('warning', 'Aucun produit valide dans le panier.');
            return $this->redirectToRoute('app_cart_show');
        }

        $commande = new Commande();
        $total = 0;

        foreach ($produits as $produit) {
            $commande->addProduit($produit);
            $total += $produit->getPrix() ?? 0;
        }

        $commande->setTotal($total);

        $entityManager->persist($commande);
        $entityManager->flush();

        // Vider le panier
        $session->remove('cart');

        $this->addFlash('success', 'Votre commande a été créée avec succès.');

        // Rester sur la page panier après la validation
        return $this->redirectToRoute('app_cart_show');
    }
}
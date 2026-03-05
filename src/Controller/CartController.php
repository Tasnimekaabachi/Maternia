<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\ProduitRepository;
use App\Repository\PromoCodeRepository;
use App\Service\NotificationService;
use App\Service\PaymentService;
use App\Service\Shipping\DhlShipmentClient;
use App\Service\Shipping\ShippingQuoteService;
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
    public function show(Request $request, ProduitRepository $produitRepository, ShippingQuoteService $shippingQuoteService, PaymentService $paymentService): Response
    {
        $session = $request->getSession();
        /** @var int[] $cart */
        $cart = $session->get('cart', []);

        $produits = [];
        $total = 0;
        $shippingQuote = null;

        if (!empty($cart)) {
            $produits = $produitRepository->findBy(['id' => $cart]);
            foreach ($produits as $produit) {
                $total += $produit->getPrix() ?? 0;
            }
            if (!empty($produits)) {
                $shippingQuote = $shippingQuoteService->quote($produits, 'TN', 'POSTE');
            }
        }

        return $this->render('pages/cart.html.twig', [
            'produits' => $produits,
            'total' => $total,
            'shippingQuote' => $shippingQuote,
            'stripeAvailable' => $paymentService->isConfigured(),
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
        EntityManagerInterface $entityManager,
        ShippingQuoteService $shippingQuoteService,
        DhlShipmentClient $dhlShipmentClient,
        NotificationService $notificationService,
        PromoCodeRepository $promoCodeRepository,
        PaymentService $paymentService
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

        $email = trim((string) $request->request->get('email'));
        $telephone = trim((string) $request->request->get('telephone'));
        $address = trim((string) $request->request->get('address'));
        $city = trim((string) $request->request->get('city'));
        $postalCode = trim((string) $request->request->get('postal_code'));
        $country = strtoupper(trim((string) $request->request->get('country', 'TN')));
        $carrier = strtoupper(trim((string) $request->request->get('carrier', 'POSTE')));
        $promoCodeInput = strtoupper(trim((string) $request->request->get('promo_code')));

        $subtotal = 0.0;
        foreach ($produits as $produit) {
            $commande->addProduit($produit);
            $subtotal += $produit->getPrix() ?? 0;
        }

        // Poids total du colis (utilisé pour DHL Shipment)
        $weightKg = 0.0;
        foreach ($produits as $produit) {
            $weightKg += $produit->getPoidsKg() ?? 0.30;
        }
        $weightKg = max(0.1, $weightKg);

        $destination = [
            'country' => $country,
            'postalCode' => $postalCode,
            'city' => $city,
            'address' => $address,
        ];
        $quote = $shippingQuoteService->quote($produits, $country ?: 'TN', $carrier ?: 'POSTE', $destination);

        $total = $subtotal + $quote->cost;

        // Application éventuelle du code promo
        if ($promoCodeInput !== '') {
            $promo = $promoCodeRepository->findActiveForEmail($promoCodeInput, $email);
            if ($promo) {
                $discount = max(0, min(100, $promo->getDiscountPercent()));
                $total = $total * (1 - $discount / 100);
                $promo->markUsed();
            } else {
                $this->addFlash('warning', 'Code promo invalide ou déjà utilisé.');
            }
        }

        $commande->setEmail($email ?: null);
        $commande->setTelephone($telephone ?: null);
        $commande->setShippingAddress($address ?: null);
        $commande->setShippingCity($city ?: null);
        $commande->setShippingPostalCode($postalCode ?: null);
        $commande->setShippingCountry($country ?: null);
        $commande->setShippingCarrier($quote->carrier);
        $commande->setShippingEtaDays($quote->etaDays);
        $commande->setShippingCost($quote->cost);
        $commande->setTotal((float) $total);

        $paymentMethod = strtolower(trim((string) $request->request->get('payment_method', 'offline')));
        $useStripe = $paymentMethod === 'stripe' && $paymentService->isConfigured();

        if ($useStripe) {
            $commande->setPaymentStatus('pending_stripe');
        } else {
            $commande->setPaymentStatus('pending_offline');
        }

        $entityManager->persist($commande);
        $entityManager->flush();

        // 1) Tenter de créer un envoi réel DHL (si transporteur DHL + API configurée)
        if (
            !$useStripe
            && strtoupper($quote->carrier) === 'DHL'
            && $dhlShipmentClient->isConfigured()
            && $commande->getShippingTracking() === null
        ) {
            $shipment = $dhlShipmentClient->createShipment(
                $weightKg,
                [
                    'recipientName' => $email !== '' ? $email : 'Client Maternia',
                    'address' => $address,
                    'city' => $city,
                    'postalCode' => $postalCode,
                    'country' => $country,
                    'phone' => $telephone,
                    'email' => $email,
                ],
                $quote->productCode ?? 'N',
                sprintf('CMD-%d', $commande->getId())
            );

            if ($shipment !== null) {
                $commande->setShippingTracking($shipment['trackingNumber']);
                $entityManager->flush();
            }
        }

        // 2) Si aucun tracking n'a pu être créé (POSTE, ARAMEX, DHL non configuré...),
        // on génère un numéro de suivi interne Maternia pour toutes les commandes.
        if ($commande->getShippingTracking() === null) {
            $internalTracking = sprintf(
                'MTR-%s-%04d',
                (new \DateTimeImmutable())->format('Ymd'),
                $commande->getId()
            );
            $commande->setShippingTracking($internalTracking);
            $entityManager->flush();
        }

        if ($useStripe) {
            $session->remove('cart');
            return $this->redirectToRoute('app_cart_payment', ['id' => $commande->getId()]);
        }

        $notificationService->sendOrderPaid($commande);
        $session->remove('cart');
        $this->addFlash('success', 'Votre commande a été créée avec succès.');
        return $this->redirectToRoute('app_checkout_success', ['id' => $commande->getId()]);
    }

    #[Route('/payment/{id}', name: 'app_cart_payment', methods: ['GET'])]
    public function payment(Commande $commande, PaymentService $paymentService): Response
    {
        if ($commande->getPaymentStatus() === 'paid') {
            $this->addFlash('success', 'Cette commande est déjà payée.');
            return $this->redirectToRoute('app_checkout_success', ['id' => $commande->getId()]);
        }
        if ($commande->getPaymentStatus() !== 'pending_stripe') {
            $this->addFlash('warning', 'Cette commande n\'est pas en attente de paiement par carte.');
            return $this->redirectToRoute('app_checkout_success', ['id' => $commande->getId()]);
        }
        if (!$paymentService->isConfigured()) {
            $this->addFlash('danger', 'Le paiement en ligne n\'est pas disponible.');
            return $this->redirectToRoute('app_checkout_success', ['id' => $commande->getId()]);
        }

        return $this->render('pages/payment.html.twig', [
            'commande' => $commande,
            'stripePublishableKey' => $this->getParameter('stripe_publishable_key'),
        ]);
    }

    #[Route('/checkout/success/{id}', name: 'app_checkout_success', methods: ['GET'])]
    public function checkoutSuccess(Commande $commande, PaymentService $paymentService, Request $request): Response
    {
        // Fallback : si retour depuis Stripe, confirmer et envoyer l'email
        $paymentIntentId = $request->query->get('payment_intent');
        if (!$paymentIntentId && $paymentService->isConfigured()) {
            $clientSecret = $request->query->get('payment_intent_client_secret');
            if ($clientSecret && preg_match('/^(pi_[a-zA-Z0-9]+)_secret_/', (string) $clientSecret, $m)) {
                $paymentIntentId = $m[1];
            }
        }
        if ($paymentIntentId && $commande->getPaymentStatus() === 'pending_stripe') {
            $confirmed = $paymentService->confirmFromRedirect($commande, (string) $paymentIntentId);
            if ($confirmed && !$commande->getEmail()) {
                $this->addFlash('warning', 'Merci de renseigner votre email au checkout pour recevoir la facture par email.');
            }
        }

        return $this->render('pages/checkout_success.html.twig', [
            'commande' => $commande,
        ]);
    }
}
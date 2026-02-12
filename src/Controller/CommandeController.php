<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commande')]
final class CommandeController extends AbstractController
{
    #[Route(name: 'app_commande_index', methods: ['GET'])]
    public function index(Request $request, CommandeRepository $commandeRepository): Response
    {
        $term = $request->query->get('q', '');
        $sort = $request->query->get('sort', '');

        $qb = $commandeRepository->createQueryBuilder('c');

        if ($term) {
            $qb
                ->andWhere('c.statut LIKE :t')
                ->setParameter('t', '%'.$term.'%');
        }

        // Tri
        if ($sort === 'date_asc') {
            $qb->orderBy('c.dateCommande', 'ASC');
        } elseif ($sort === 'date_desc') {
            $qb->orderBy('c.dateCommande', 'DESC');
        } elseif ($sort === 'total_asc') {
            $qb->orderBy('c.total', 'ASC');
        } elseif ($sort === 'total_desc') {
            $qb->orderBy('c.total', 'DESC');
        } elseif ($sort === 'status_asc') {
            $qb->orderBy('c.statut', 'ASC');
        } elseif ($sort === 'status_desc') {
            $qb->orderBy('c.statut', 'DESC');
        } else {
            $qb->orderBy('c.dateCommande', 'DESC');
        }

        $commandes = $qb->getQuery()->getResult();

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
            'searchTerm' => $term,
            'sort' => $sort,
        ]);
    }

    #[Route('/export', name: 'app_commande_export', methods: ['GET'])]
    public function exportCsv(CommandeRepository $commandeRepository): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($commandeRepository) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Date', 'Statut', 'Total', 'Produits'], ';');

            foreach ($commandeRepository->findAll() as $commande) {
                $produits = $commande->getProduits()->map(fn ($p) => $p->getNom())->toArray();
                fputcsv($handle, [
                    $commande->getId(),
                    $commande->getDateCommande()?->format('Y-m-d H:i'),
                    $commande->getStatut(),
                    $commande->getTotal(),
                    implode(', ', $produits),
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="commandes_' . date('Y-m-d') . '.csv"');

        return $response;
    }

    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ProduitRepository $produitRepository
    ): Response
    {
        $commande = new Commande();

        // Si on arrive depuis la marketplace avec ?produit=ID, pré-remplir la commande
        $produitId = $request->query->get('produit');
        if ($produitId) {
            $produit = $produitRepository->find($produitId);
            if ($produit) {
                $commande->addProduit($produit);
            }
        }

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Synchroniser le stock : nouvelle commande = on consomme 1 unité par produit
            foreach ($commande->getProduits() as $produit) {
                $stockActuel = $produit->getStock();
                if ($stockActuel !== null && $stockActuel > 0) {
                    $produit->setStock($stockActuel - 1);
                }
            }

            $entityManager->persist($commande);
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/new.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        // Conserver l'état initial des produits pour calculer le delta
        $produitsOriginaux = $commande->getProduits()->toArray();

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nouveauxProduits = $commande->getProduits()->toArray();

            // Produits ajoutés à la commande : stock -1
            foreach ($nouveauxProduits as $produit) {
                if (!in_array($produit, $produitsOriginaux, true)) {
                    $stockActuel = $produit->getStock();
                    if ($stockActuel !== null && $stockActuel > 0) {
                        $produit->setStock($stockActuel - 1);
                    }
                }
            }

            // Produits retirés de la commande : stock +1
            foreach ($produitsOriginaux as $produit) {
                if (!in_array($produit, $nouveauxProduits, true)) {
                    $stockActuel = $produit->getStock();
                    $produit->setStock(($stockActuel ?? 0) + 1);
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->getPayload()->getString('_token'))) {
            // Annulation de commande : on restitue 1 unité de stock par produit
            foreach ($commande->getProduits() as $produit) {
                $stockActuel = $produit->getStock();
                $produit->setStock(($stockActuel ?? 0) + 1);
            }

            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }
}

<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminProduitController extends AbstractController
{
    #[Route('/produits/export', name: 'admin_produit_export', methods: ['GET'])]
    public function exportCsv(ProduitRepository $repo): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($repo) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Nom', 'Description', 'Prix', 'Stock'], ';');

            foreach ($repo->findAll() as $produit) {
                fputcsv($handle, [
                    $produit->getId(),
                    $produit->getNom(),
                    $produit->getDescription(),
                    $produit->getPrix(),
                    $produit->getStock(),
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="produits_' . date('Y-m-d') . '.csv"');

        return $response;
    }

    #[Route('/marketplace', name: 'admin_marketplace')]
    public function marketplace(Request $request, ProduitRepository $repo): Response
    {
        $term = $request->query->get('q', '');
        $sort = $request->query->get('sort', '');

        $qb = $repo->createQueryBuilder('p');

        if ($term) {
            $qb
                ->andWhere('p.nom LIKE :t OR p.description LIKE :t')
                ->setParameter('t', '%'.$term.'%');
        }

        // Tri
        if ($sort === 'price_asc') {
            $qb->orderBy('p.prix', 'ASC');
        } elseif ($sort === 'price_desc') {
            $qb->orderBy('p.prix', 'DESC');
        } elseif ($sort === 'name_asc') {
            $qb->orderBy('p.nom', 'ASC');
        } elseif ($sort === 'name_desc') {
            $qb->orderBy('p.nom', 'DESC');
        } else {
            $qb->orderBy('p.nom', 'ASC');
        }

        $produits = $qb->getQuery()->getResult();

        return $this->render('admin/marketplace.html.twig', [
            'produits' => $produits,
            'searchTerm' => $term,
            'sort' => $sort,
        ]);
    }

    #[Route('/produit/add', name: 'admin_produit_add')]
    public function add(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $produit = new Produit();

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($produit);
            $em->flush();

            return $this->redirectToRoute('admin_marketplace');
        }

        return $this->render('admin/produit_form.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
        ]);
    }

    #[Route('/produit/{id}/edit', name: 'admin_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Le produit a bien été modifié.');

            return $this->redirectToRoute('admin_marketplace');
        }

        return $this->render('admin/produit_form.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
        ]);
    }

    #[Route('/produit/{id}/delete', name: 'admin_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $em): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete_admin_produit'.$produit->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($produit);
            $em->flush();
            $this->addFlash('success', 'Le produit a bien été supprimé.');
        } else {
            $this->addFlash('danger', 'Token CSRF invalide. Suppression refusée.');
        }

        return $this->redirectToRoute('admin_marketplace');
    }
}

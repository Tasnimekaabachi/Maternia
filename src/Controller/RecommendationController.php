<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RecommendationController extends AbstractController
{
    #[Route('/api/recommendations', name: 'api_recommendations', methods: ['GET'])]
    public function recommend(Request $request, ProduitRepository $produitRepository): JsonResponse
    {
        $ageBebe = $request->query->get('age_bebe');
        $trimestre = $request->query->get('trimestre');

        // Logique simple de recommandation simulant une API IA
        if ($ageBebe !== null) {
            // Exemple : pour un bébé jeune, on prend les derniers produits
            $produits = $produitRepository->createQueryBuilder('p')
                ->orderBy('p.id', 'DESC')
                ->setMaxResults(4)
                ->getQuery()
                ->getResult();
        } elseif ($trimestre !== null) {
            // Exemple : selon le trimestre, on trie par prix croissant
            $produits = $produitRepository->createQueryBuilder('p')
                ->orderBy('p.prix', 'ASC')
                ->setMaxResults(4)
                ->getQuery()
                ->getResult();
        } else {
            // Par défaut, on renvoie quelques produits récents
            $produits = $produitRepository->createQueryBuilder('p')
                ->orderBy('p.id', 'DESC')
                ->setMaxResults(4)
                ->getQuery()
                ->getResult();
        }

        $data = [];
        foreach ($produits as $produit) {
            $data[] = [
                'id' => $produit->getId(),
                'nom' => $produit->getNom(),
                'description' => $produit->getDescription(),
                'prix' => $produit->getPrix(),
            ];
        }

        return $this->json($data);
    }
}


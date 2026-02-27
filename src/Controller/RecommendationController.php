<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RecommendationController extends AbstractController
{
    #[Route('/api/recommendations', name: 'api_recommendations', methods: ['GET'])]
    public function recommend(
        Request $request,
        ProduitRepository $produitRepository,
        CommandeRepository $commandeRepository
    ): JsonResponse
    {
        $ageBebe = $request->query->get('age_bebe');
        $trimestre = $request->query->get('trimestre');
        $popular = $request->query->getBoolean('popular', false);

        // Intégration IA – logique de recommandation intelligente (règles métier, critère PIDEV)
        if ($ageBebe !== null) {
            // Bébé : priorité catégorie "bebe" et soins
            $produits = $produitRepository->createQueryBuilder('p')
                ->where('p.categorie IN (:cats)')
                ->setParameter('cats', ['bebe', 'soins'])
                ->orderBy('p.id', 'DESC')
                ->setMaxResults(4)
                ->getQuery()
                ->getResult();
            if (count($produits) < 4) {
                $fallback = $produitRepository->createQueryBuilder('p')
                    ->orderBy('p.id', 'DESC')
                    ->setMaxResults(4 - count($produits))
                    ->getQuery()
                    ->getResult();
                $produits = array_merge($produits, $fallback);
            }
        } elseif ($trimestre !== null) {
            // Grossesse : trimestre 1-2 -> grossesse, sinon équipement/bébé
            $cats = (int) $trimestre <= 2 ? ['grossesse', 'soins'] : ['equipement', 'bebe'];
            $produits = $produitRepository->createQueryBuilder('p')
                ->where('p.categorie IN (:cats)')
                ->setParameter('cats', $cats)
                ->orderBy('p.prix', 'ASC')
                ->setMaxResults(4)
                ->getQuery()
                ->getResult();
            if (count($produits) < 4) {
                $fallback = $produitRepository->createQueryBuilder('p')
                    ->orderBy('p.prix', 'ASC')
                    ->setMaxResults(4 - count($produits))
                    ->getQuery()
                    ->getResult();
                $produits = array_merge($produits, $fallback);
            }
        } else {
            // Fallback "produits populaires" basé sur les commandes validées
            $produits = [];
            if ($popular) {
                $top = $commandeRepository->topProduitsCommandes(4);
                $noms = array_values(array_filter(array_map(static fn ($r) => $r['produit'] ?? null, $top)));
                if ($noms) {
                    $produits = $produitRepository->createQueryBuilder('p')
                        ->where('p.nom IN (:noms)')
                        ->setParameter('noms', $noms)
                        ->setMaxResults(4)
                        ->getQuery()
                        ->getResult();
                }
            }

            if (!$produits) {
                $produits = $produitRepository->createQueryBuilder('p')
                    ->orderBy('p.id', 'DESC')
                    ->setMaxResults(4)
                    ->getQuery()
                    ->getResult();
            }
        }

        $data = [];
        foreach ($produits as $produit) {
            $data[] = [
                'id' => $produit->getId(),
                'nom' => $produit->getNom(),
                'description' => $produit->getDescription(),
                'prix' => $produit->getPrix(),
                'imageName' => $produit->getImageName(),
            ];
        }

        return $this->json($data);
    }
}


<?php

namespace App\DataFixtures;

use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Données de test pour la démo PIDEV – produits avec catégories.
 * Avec --append : ne crée pas de doublon si un produit avec le même nom existe déjà (préserve les images).
 * Sans --append : assigne une image par défaut quand elle existe dans public/img/.
 */
class ProduitFixtures extends Fixture implements FixtureGroupInterface
{
    /** Mapping nom produit => fichier image dans public/img/ (optionnel) */
    private const IMAGE_MAPPING = [
        'Atelier portage' => 'Atelier portage.jpg',
        'Biberon anti-colique' => 'Biberon anti-colique.jpg',
        'Body bébé 0-3 mois' => 'Body bébé 0-3 mois.jpg',
        'Consultation sage-femme' => 'Consultation sage-femme.jpg',
        'Crème stretch grossesse' => 'Crème stretch grossesse.jpg',
        'Huile de massage bébé' => 'Huile de massage bébé.jpg',
        'Lit cododo' => 'Lit cododo.jpg',
        'Poussette compacte' => 'Poussette compacte.jpg',
        'Pyjama bébé 3 pièces' => 'Pyjama bébé 3 pièces.jpg',
        'Robe grossesse été' => 'Robe grossesse été.jpg',
        'Tapis d\'éveil' => 'Tapis d\'éveil.jpg',
        'Coussin d\'allaitement' => 'Coussin d\'allaitement.jpg',
    ];

    public static function getGroups(): array
    {
        return ['produit'];
    }

    public function load(ObjectManager $manager): void
    {
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/produits';
        $imgDir = dirname(__DIR__, 2) . '/public/img';

        $items = [
            ['nom' => 'Coussin d\'allaitement', 'description' => 'Coussin ergonomique pour un confort optimal pendant l\'allaitement.', 'prix' => 89.99, 'stock' => 15, 'categorie' => 'grossesse'],
            ['nom' => 'Body bébé 0-3 mois', 'description' => 'Body en coton bio, manches longues, boutons pression.', 'prix' => 24.50, 'stock' => 30, 'categorie' => 'bebe'],
            ['nom' => 'Huile de massage bébé', 'description' => 'Huile douce pour le massage des tout-petits, sans parfum.', 'prix' => 18.00, 'stock' => 25, 'categorie' => 'soins'],
            ['nom' => 'Robe grossesse été', 'description' => 'Robe légère et confortable pour les futures mamans.', 'prix' => 55.00, 'stock' => 12, 'categorie' => 'mode'],
            ['nom' => 'Poussette compacte', 'description' => 'Poussette pliable pour les déplacements en ville.', 'prix' => 299.00, 'stock' => 8, 'categorie' => 'equipement'],
            ['nom' => 'Consultation sage-femme', 'description' => 'Suivi personnalisé avec une sage-femme à domicile.', 'prix' => 70.00, 'stock' => 99, 'categorie' => 'services'],
            ['nom' => 'Biberon anti-colique', 'description' => 'Biberon 250 ml avec valve anti-colique, stérilisable.', 'prix' => 14.99, 'stock' => 40, 'categorie' => 'bebe'],
            ['nom' => 'Crème stretch grossesse', 'description' => 'Crème pour prévenir les vergetures pendant la grossesse.', 'prix' => 32.00, 'stock' => 20, 'categorie' => 'grossesse'],
            ['nom' => 'Pyjama bébé 3 pièces', 'description' => 'Pyjama en coton, motifs doux, fermeture pratique.', 'prix' => 28.00, 'stock' => 18, 'categorie' => 'bebe'],
            ['nom' => 'Lit cododo', 'description' => 'Lit à poser contre le lit parental, barrière réglable.', 'prix' => 159.00, 'stock' => 10, 'categorie' => 'equipement'],
            ['nom' => 'Atelier portage', 'description' => 'Session découverte du portage en écharpe ou en sling.', 'prix' => 45.00, 'stock' => 99, 'categorie' => 'services'],
            ['nom' => 'Tapis d\'éveil', 'description' => 'Tapis avec arches et jouets amovibles pour l\'éveil de bébé.', 'prix' => 49.99, 'stock' => 14, 'categorie' => 'bebe'],
        ];

        $repo = $manager->getRepository(Produit::class);

        foreach ($items as $i => $item) {
            // Avec --append : éviter les doublons et préserver les produits existants (et leurs images)
            if ($repo->findOneBy(['nom' => $item['nom']])) {
                continue;
            }

            $p = new Produit();
            $p->setNom($item['nom']);
            $p->setDescription($item['description']);
            $p->setPrix($item['prix']);
            $p->setStock($item['stock']);
            $p->setCategorie($item['categorie']);
            $p->setPoidsKg(0.2 + (($i % 5) * 0.2)); // poids démo pour calcul livraison
            $p->setSku(sprintf('FIX-%03d', $i + 1));

            // Assigner une image par défaut si elle existe dans public/img/
            $sourceFile = self::IMAGE_MAPPING[$item['nom']] ?? null;
            if ($sourceFile) {
                $sourcePath = $imgDir . '/' . $sourceFile;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                if (is_file($sourcePath)) {
                    $ext = pathinfo($sourceFile, PATHINFO_EXTENSION);
                    $destFilename = strtolower(preg_replace('/[^a-z0-9-]/', '-', $item['nom'])) . '-fixture.' . $ext;
                    $destPath = $uploadDir . '/' . $destFilename;
                    if (copy($sourcePath, $destPath)) {
                        $p->setImageName($destFilename);
                    }
                }
            }

            $manager->persist($p);
        }

        $manager->flush();
    }
}

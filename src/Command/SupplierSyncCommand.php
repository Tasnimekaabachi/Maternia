<?php

namespace App\Command;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:supplier:sync',
    description: 'Synchronise stock/prix depuis un fournisseur (feed JSON).'
)]
final class SupplierSyncCommand extends Command
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ProduitRepository $produitRepository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('url', null, InputOption::VALUE_OPTIONAL, 'URL du feed fournisseur JSON (override SUPPLIER_FEED_URL)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Ne pas écrire en base (affiche uniquement)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = (string) ($input->getOption('url') ?: getenv('SUPPLIER_FEED_URL') ?: '');
        $dryRun = (bool) $input->getOption('dry-run');

        $items = $url !== '' ? $this->fetchFeed($url) : $this->demoFeed();
        if ($items === null) {
            $output->writeln('<error>Impossible de récupérer le feed fournisseur.</error>');
            return Command::FAILURE;
        }

        $created = 0;
        $updated = 0;

        foreach ($items as $item) {
            $sku = trim((string) ($item['sku'] ?? ''));
            if ($sku === '') {
                continue;
            }

            $prix = (float) ($item['prix'] ?? 0);
            $stock = (int) ($item['stock'] ?? 0);
            $nom = trim((string) ($item['nom'] ?? ('Produit '.$sku)));

            $produit = $this->produitRepository->findOneBy(['sku' => $sku]);
            if (!$produit) {
                $produit = new Produit();
                $produit->setSku($sku);
                $produit->setNom($nom);
                $produit->setDescription('Import fournisseur: '.$sku);
                $produit->setCategorie($item['categorie'] ?? 'bebe');
                $produit->setPoidsKg(isset($item['poidsKg']) ? (float) $item['poidsKg'] : null);
                $produit->setPrix($prix);
                $produit->setStock($stock);
                $this->em->persist($produit);
                $created++;
            } else {
                $produit->setNom($nom ?: (string) $produit->getNom());
                $produit->setPrix($prix);
                $produit->setStock($stock);
                $updated++;
            }

            $output->writeln(sprintf('%s %s (sku=%s) stock=%d prix=%.2f',
                $produit->getId() ? 'MAJ' : 'NEW',
                $produit->getNom(),
                $sku,
                $stock,
                $prix
            ));
        }

        if (!$dryRun) {
            $this->em->flush();
        }

        $output->writeln(sprintf('<info>Sync terminée.</info> Créés: %d | Mis à jour: %d | Mode: %s', $created, $updated, $dryRun ? 'dry-run' : 'write'));
        return Command::SUCCESS;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function fetchFeed(string $url): ?array
    {
        try {
            $response = $this->httpClient->request('GET', $url, ['timeout' => 5]);
            $data = $response->toArray();
            if (isset($data['items']) && is_array($data['items'])) {
                return $data['items'];
            }
            return is_array($data) ? $data : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Feed de démo (quand SUPPLIER_FEED_URL est vide).
     *
     * @return array<int, array<string, mixed>>
     */
    private function demoFeed(): array
    {
        return [
            ['sku' => 'MAT-001', 'nom' => 'Couches bébé (pack)', 'prix' => 29.9, 'stock' => 50, 'categorie' => 'bebe', 'poidsKg' => 1.2],
            ['sku' => 'MAT-002', 'nom' => 'Biberon anti-colique', 'prix' => 15.5, 'stock' => 120, 'categorie' => 'bebe', 'poidsKg' => 0.3],
            ['sku' => 'MAT-003', 'nom' => 'Crème grossesse', 'prix' => 32.0, 'stock' => 70, 'categorie' => 'grossesse', 'poidsKg' => 0.2],
        ];
    }
}


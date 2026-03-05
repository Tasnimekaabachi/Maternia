<?php

namespace App\Command;

use App\Service\Shipping\DhlQuoteClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:dhl-test',
    description: 'Teste l’appel à l’API DHL Rating (devis). Vérifie la config .env et affiche le résultat.',
)]
final class DhlTestCommand extends Command
{
    public function __construct(
        private readonly DhlQuoteClient $dhlQuoteClient,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->dhlQuoteClient->isConfigured()) {
            $io->warning('DHL non configuré. Renseigne DHL_API_BASE_URL, DHL_ACCOUNT_NUMBER, DHL_USERNAME et DHL_PASSWORD dans .env');
            return Command::FAILURE;
        }

        $io->info('Appel API DHL Rating (POST /rates) avec un colis de 2 kg, destination Tunis...');
        $destination = [
            'country' => 'TN',
            'postalCode' => '1000',
            'city' => 'Tunis',
            'address' => 'Avenue Habib Bourguiba',
        ];
        $quote = $this->dhlQuoteClient->getQuote(2.0, $destination);

        if ($quote === null) {
            $io->error('Aucun devis reçu. Vérifie les identifiants (demo-key + password pour le mock) et consulte var/log/dev.log pour les erreurs DHL.');
            return Command::FAILURE;
        }

        $io->success('API DHL OK.');
        $io->table(
            ['Champ', 'Valeur'],
            [
                ['Transporteur', $quote->carrier],
                ['Coût (TND)', (string) $quote->cost],
                ['Délai (jours)', (string) $quote->etaDays],
                ['Code produit', $quote->productCode ?? '–'],
            ]
        );
        return Command::SUCCESS;
    }
}

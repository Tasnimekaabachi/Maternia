<?php

namespace App\Service;

class BebeSemaineService
{
    private const IMAGES_DIR     = 'img/echographie/';
    private const EXTENSIONS     = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
    private const FALLBACK_IMAGE = 'img/echographie/default.jpg';

    private string $publicDir;

    /**
     * @var array<int, array{taille: float, poids: int, dev: string}>
     */
    private array $donnees = [
        1  => ['taille' => 0.1,  'poids' => 0,    'dev' => 'Les premières cellules se divisent. L\'aventure commence !'],
        2  => ['taille' => 0.2,  'poids' => 0,    'dev' => 'L\'embryon s\'implante dans l\'utérus.'],
        3  => ['taille' => 0.5,  'poids' => 0,    'dev' => 'Le tube neural commence à se former.'],
        4  => ['taille' => 1.0,  'poids' => 0,    'dev' => 'Le cœur commence à battre pour la première fois.'],
        5  => ['taille' => 1.5,  'poids' => 0,    'dev' => 'Le cerveau, la moelle épinière et le cœur se développent rapidement.'],
        6  => ['taille' => 2.0,  'poids' => 0,    'dev' => 'Les bourgeons des bras et des jambes apparaissent.'],
        7  => ['taille' => 2.5,  'poids' => 0,    'dev' => 'Le visage prend forme, les yeux et les oreilles se développent.'],
        8  => ['taille' => 3.2,  'poids' => 1,    'dev' => 'Les doigts commencent à se former. Bébé peut bouger !'],
        9  => ['taille' => 4.5,  'poids' => 2,    'dev' => 'Les muscles se développent, bébé peut faire de petits mouvements.'],
        10 => ['taille' => 6.0,  'poids' => 4,    'dev' => 'Tous les organes essentiels sont formés. La phase fœtale commence.'],
        11 => ['taille' => 7.4,  'poids' => 7,    'dev' => 'Bébé peut ouvrir et fermer les poings.'],
        12 => ['taille' => 9.0,  'poids' => 14,   'dev' => 'Les réflexes apparaissent. Bébé peut sucer son pouce !'],
        13 => ['taille' => 10.5, 'poids' => 23,   'dev' => 'Les empreintes digitales se forment. Bébé entend les sons.'],
        14 => ['taille' => 12.0, 'poids' => 43,   'dev' => 'Bébé peut faire des grimaces. Les cheveux poussent.'],
        15 => ['taille' => 13.5, 'poids' => 70,   'dev' => 'Bébé bouge beaucoup ! Vous pourrez bientôt sentir ses mouvements.'],
        16 => ['taille' => 15.0, 'poids' => 100,  'dev' => 'Le système nerveux se perfectionne. Bébé réagit à la lumière.'],
        17 => ['taille' => 16.5, 'poids' => 140,  'dev' => 'La graisse corporelle commence à s\'accumuler pour garder bébé au chaud.'],
        18 => ['taille' => 18.0, 'poids' => 190,  'dev' => 'Bébé développe ses sens : goût, toucher, ouïe, vue.'],
        19 => ['taille' => 20.0, 'poids' => 240,  'dev' => 'Le vernix caseosa protège la peau de bébé dans le liquide amniotique.'],
        20 => ['taille' => 25.0, 'poids' => 300,  'dev' => 'Mi-chemin ! Bébé avale du liquide amniotique et urine.'],
        21 => ['taille' => 27.0, 'poids' => 360,  'dev' => 'Bébé dort et se réveille à des heures régulières.'],
        22 => ['taille' => 29.0, 'poids' => 430,  'dev' => 'Les paupières et les sourcils sont bien formés.'],
        23 => ['taille' => 30.5, 'poids' => 500,  'dev' => 'Bébé entend votre voix et réagit aux sons forts.'],
        24 => ['taille' => 32.0, 'poids' => 600,  'dev' => 'Les poumons se développent. Bébé pratique la respiration.'],
        25 => ['taille' => 34.0, 'poids' => 700,  'dev' => 'Bébé répond aux sons en bougeant. Il reconnaît votre voix !'],
        26 => ['taille' => 36.0, 'poids' => 820,  'dev' => 'Les yeux s\'ouvrent pour la première fois. Bébé voit de la lumière.'],
        27 => ['taille' => 37.0, 'poids' => 950,  'dev' => 'Le cerveau se développe rapidement. Bébé rêve peut-être !'],
        28 => ['taille' => 38.0, 'poids' => 1100, 'dev' => 'Bébé cligne des yeux et peut voir la lumière à travers votre ventre.'],
        29 => ['taille' => 39.5, 'poids' => 1250, 'dev' => 'Les muscles et les poumons mûrissent rapidement.'],
        30 => ['taille' => 41.0, 'poids' => 1400, 'dev' => 'Bébé accumule de la graisse pour réguler sa température.'],
        31 => ['taille' => 42.0, 'poids' => 1600, 'dev' => 'Tous les sens sont développés. Bébé peut tourner la tête.'],
        32 => ['taille' => 43.0, 'poids' => 1800, 'dev' => 'Bébé se retourne pour se préparer à la naissance (tête en bas).'],
        33 => ['taille' => 44.5, 'poids' => 2000, 'dev' => 'Les os se durcissent mais le crâne reste souple pour l\'accouchement.'],
        34 => ['taille' => 46.0, 'poids' => 2200, 'dev' => 'Bébé reconnaît votre voix et votre musique préférée.'],
        35 => ['taille' => 47.0, 'poids' => 2400, 'dev' => 'Les reins sont complètement développés. Bébé prend du poids rapidement.'],
        36 => ['taille' => 48.0, 'poids' => 2600, 'dev' => 'Bébé est considéré à terme précoce. Presque prêt !'],
        37 => ['taille' => 49.0, 'poids' => 2900, 'dev' => 'Bébé est à terme ! Tous les organes sont prêts pour la vie.'],
        38 => ['taille' => 50.0, 'poids' => 3100, 'dev' => 'Bébé perd le vernix et le lanugo. Sa peau devient lisse.'],
        39 => ['taille' => 51.0, 'poids' => 3300, 'dev' => 'Bébé est prêt ! Il attend le bon moment pour arriver.'],
        40 => ['taille' => 51.5, 'poids' => 3500, 'dev' => 'Terme ! Bébé est complètement développé et prêt à rencontrer sa maman 💕'],
    ];

    public function __construct(string $publicDir)
    {
        $this->publicDir = rtrim($publicDir, '/\\');
    }

    /**
     * Convertit une semaine en mois de grossesse (1 à 9).
     *
     * Mois 1 → semaines  1 –  4
     * Mois 2 → semaines  5 –  8
     * Mois 3 → semaines  9 – 12
     * Mois 4 → semaines 13 – 16
     * Mois 5 → semaines 17 – 20
     * Mois 6 → semaines 21 – 24
     * Mois 7 → semaines 25 – 28
     * Mois 8 → semaines 29 – 32
     * Mois 9 → semaines 33 – 40
     */
    public function getSemaineEnMois(int $semaine): int
    {
        $map = [
            1 => [1,  4],
            2 => [5,  8],
            3 => [9,  12],
            4 => [13, 16],
            5 => [17, 20],
            6 => [21, 24],
            7 => [25, 28],
            8 => [29, 32],
            9 => [33, 40],
        ];

        foreach ($map as $mois => [$debut, $fin]) {
            if ($semaine >= $debut && $semaine <= $fin) {
                return $mois;
            }
        }

        return 9;
    }

    /**
     * Retourne toutes les données pour une semaine donnée.
     *
     * @return array<string, mixed>|null
     */
    public function getSemaine(int $semaine): ?array
    {
        if ($semaine < 1 || $semaine > 40) {
            return null;
        }

        $mois = $this->getSemaineEnMois($semaine);

        // ✅ $donnees est typé array<int, array{taille: float, poids: int, dev: string}>
        // PHPStan connaît exactement le type de chaque clé
        $raw = $this->donnees[$semaine];

        return [
            'taille'            => $raw['taille'],  // float
            'poids'             => $raw['poids'],   // int
            'dev'               => $raw['dev'],     // string
            'semaine'           => $semaine,
            'mois'              => $mois,
            'image_echographie' => $this->getImageParMois($mois),
        ];
    }

    /**
     * Cherche l'image selon le MOIS : mois_1.jpg … mois_9.jpg
     * dans public/img/echographie/
     * Fallback vers default.jpg si introuvable.
     *
     * @return string Chemin web utilisable dans src="..."
     */
    public function getImageParMois(int $mois): string
    {
        $mois     = max(1, min(9, $mois));
        $baseName = sprintf('mois_%d', $mois);

        foreach (self::EXTENSIONS as $ext) {
            $relativePath = self::IMAGES_DIR . $baseName . '.' . $ext;
            $fullPath     = $this->publicDir
                          . DIRECTORY_SEPARATOR
                          . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

            if (file_exists($fullPath)) {
                return '/' . str_replace('\\', '/', $relativePath);
            }
        }

        // Fallback : cherche avec glob (mounth_ ou autre nommage)
        $dirPath = $this->publicDir
                 . DIRECTORY_SEPARATOR . 'img'
                 . DIRECTORY_SEPARATOR . 'echographie';

        if (is_dir($dirPath)) {
            $files = glob($dirPath . DIRECTORY_SEPARATOR . '*' . $mois . '.*');
            if (is_array($files) && count($files) > 0) {
                return '/img/echographie/' . basename($files[0]);
            }
        }

        return '/' . self::FALLBACK_IMAGE;
    }
}
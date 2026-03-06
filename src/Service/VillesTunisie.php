<?php

namespace App\Service;

/**
 * Liste officielle des 24 gouvernorats de Tunisie avec noms reconnus pour le géocodage (Nominatim/OSM).
 * Utilisé pour normaliser les noms de ville (formulaire, recherche, distance).
 */
final class VillesTunisie
{
    /**
     * Noms canoniques pour affichage et géocodage (clé = valeur stockée en base, value = libellé pour géocodage).
     * Les noms avec accents et préfixes ("La Manouba", "Le Kef", "Béja", "Gabès", etc.) sont ceux reconnus par OSM.
     */
    private const VILLES = [
        'Tunis' => 'Tunis',
        'Ariana' => 'Ariana',
        'Ben Arous' => 'Ben Arous',
        'Manouba' => 'La Manouba',
        'La Manouba' => 'La Manouba',
        'Nabeul' => 'Nabeul',
        'Zaghouan' => 'Zaghouan',
        'Bizerte' => 'Bizerte',
        'Beja' => 'Béja',
        'Béja' => 'Béja',
        'Jendouba' => 'Jendouba',
        'Kef' => 'Le Kef',
        'Le Kef' => 'Le Kef',
        'Siliana' => 'Siliana',
        'Kairouan' => 'Kairouan',
        'Kasserine' => 'Kasserine',
        'Sidi Bouzid' => 'Sidi Bouzid',
        'Sousse' => 'Sousse',
        'Monastir' => 'Monastir',
        'Mahdia' => 'Mahdia',
        'Sfax' => 'Sfax',
        'Gafsa' => 'Gafsa',
        'Tozeur' => 'Tozeur',
        'Kebili' => 'Kébili',
        'Kébili' => 'Kébili',
        'Gabes' => 'Gabès',
        'Gabès' => 'Gabès',
        'Medenine' => 'Médenine',
        'Médenine' => 'Médenine',
        'Tataouine' => 'Tataouine',
    ];

    /**
     * Coordonnées fixes (centre ville) pour un calcul de distance fiable — évite les erreurs Nominatim (ex. Tunis).
     * lat, lon pour chaque nom canonique.
     */
    private const COORDONNEES = [
        'Tunis' => [36.8028, 10.1797],
        'Ariana' => [36.8601, 10.1934],
        'Ben Arous' => [36.7533, 10.2189],
        'La Manouba' => [36.8102, 10.0956],
        'Nabeul' => [36.4561, 10.7376],
        'Zaghouan' => [36.4029, 10.1429],
        'Bizerte' => [37.2744, 9.8739],
        'Béja' => [36.7256, 9.1814],
        'Jendouba' => [36.5011, 8.7803],
        'Le Kef' => [36.1822, 8.7147],
        'Siliana' => [36.0850, 9.3708],
        'Kairouan' => [35.6781, 10.0963],
        'Kasserine' => [35.1676, 8.8365],
        'Sidi Bouzid' => [35.0382, 9.4850],
        'Sousse' => [35.8256, 10.6346],
        'Monastir' => [35.7780, 10.8262],
        'Mahdia' => [35.5047, 11.0622],
        'Sfax' => [34.7406, 10.7603],
        'Gafsa' => [34.4250, 8.7842],
        'Tozeur' => [33.9197, 8.1333],
        'Kébili' => [33.7044, 8.9694],
        'Gabès' => [33.8815, 10.0982],
        'Médenine' => [33.3549, 10.5055],
        'Tataouine' => [32.9297, 10.4511],
    ];

    /**
     * Choix pour le formulaire : libellé affiché => valeur enregistrée (nom canonique pour géocodage).
     */
    public static function getChoicesForm(): array
    {
        $canonical = array_unique(array_values(self::VILLES));
        sort($canonical);
        $choices = [];
        foreach ($canonical as $nom) {
            $choices[$nom] = $nom;
        }
        return $choices;
    }

    /**
     * Retourne le nom canonique de la ville pour le géocodage (Nominatim).
     * Ex. "Manouba" -> "La Manouba", "Beja" -> "Béja", "Tunis" -> "Tunis".
     */
    public static function normalizePourGeocodage(string $ville): ?string
    {
        $trimmed = trim($ville);
        if ($trimmed === '') {
            return null;
        }
        return self::VILLES[$trimmed] ?? $trimmed;
    }

    /**
     * Indique si la chaîne correspond à une ville tunisienne connue.
     */
    public static function estVilleConnue(string $ville): bool
    {
        $trimmed = trim($ville);
        if ($trimmed === '') {
            return false;
        }
        return isset(self::VILLES[$trimmed]) || \in_array($trimmed, array_values(self::VILLES), true);
    }

    /**
     * Retourne les coordonnées fixes pour une ville tunisienne connue (centre ville).
     * Utilisé pour un calcul de distance fiable sans appeler Nominatim.
     *
     * @return array{lat: float, lon: float}|null
     */
    public static function getCoordonnees(string $ville): ?array
    {
        $trimmed = trim($ville);
        if ($trimmed === '') {
            return null;
        }
        $canonical = self::VILLES[$trimmed] ?? $trimmed;
        $coords = self::COORDONNEES[$canonical] ?? null;
        if ($coords === null) {
            return null;
        }
        return ['lat' => (float) $coords[0], 'lon' => (float) $coords[1]];
    }

    /**
     * Liste des noms canoniques (pour affichage, exports, etc.).
     *
     * @return list<string>
     */
    public static function getNomsCanoniques(): array
    {
        $noms = array_unique(array_values(self::VILLES));
        sort($noms);
        return array_values($noms);
    }
}

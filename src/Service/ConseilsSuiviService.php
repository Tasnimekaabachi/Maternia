<?php

namespace App\Service;

use App\Entity\Grosesse;

class ConseilsSuiviService
{
    public function conseilsGrossesse(int $semaines): string
    {
        if ($semaines < 12) {
            return 'Repos, hydratation et prise d’acide folique. Première échographie et déclaration de grossesse.';
        }
        if ($semaines < 24) {
            return 'Échographie morphologique, surveillance du poids et de la tension. Activité physique douce recommandée.';
        }
        if ($semaines < 32) {
            return 'Surveiller les mouvements de bébé, préparer le projet de naissance et discuter du lieu d’accouchement.';
        }

        return 'Préparer la valise maternité, organiser le retour à la maison et vérifier les derniers examens.';
    }

    public function conseilsBebe(int $ageMois): string
    {
        if ($ageMois < 2) {
            return 'Suivi néonatal, dépistages et vaccin BCG selon les recommandations locales.';
        }
        if ($ageMois < 6) {
            return 'Rappels de vaccins 2–3–4 mois, surveillance de la croissance et du sommeil.';
        }
        if ($ageMois < 12) {
            return 'Diversification alimentaire progressive, prévention des chutes et rappels vaccinaux des 12 mois.';
        }

        return 'Suivi pédiatrique régulier, rappels vaccinaux et accompagnement du développement psychomoteur.';
    }

    public function getAgeBebeEnMois(Grosesse $grossesse): ?int
    {
        $date = $grossesse->getDateAccouchementReelle();
        if (!$date instanceof \DateTimeInterface) {
            return null;
        }

        $today = new \DateTimeImmutable('today');
        $diff = $date->diff($today);
        $mois = $diff->y * 12 + $diff->m;

        return $mois >= 0 ? $mois : null;
    }
}


<?php

namespace App\Service;

class NutritionService
{
    /**
     * Calcule les besoins nutritionnels complets d'une maman enceinte.
     * Formule Mifflin-St Jeor + ajustements OMS grossesse.
     */
    /** @return array<mixed> */
    public function calculerBesoins(
        float $poids,
        float $taille,
        ?float $imc,
        int $trimestre = 1,
        int $age = 28
    ): array {
        // ── 1. BMR (Mifflin-St Jeor femme) ──────────────────────────
        $bmr = (10 * $poids) + (6.25 * $taille) - (5 * $age) - 161;

        // ── 2. Activité physique légère (facteur 1.375) ──────────────
        $tdee = $bmr * 1.375;

        // ── 3. Surplus calorique selon trimestre (OMS) ───────────────
        $surplusTrimestre = match($trimestre) {
            1 => 0,
            2 => 340,
            3 => 452,
            default => 0,
        };

        // ── 4. Ajustement selon IMC ──────────────────────────────────
        $ajustementImc = 0;
        $imcCategorie  = 'Normal';
        if ($imc !== null) {
            if ($imc < 18.5) {
                $ajustementImc = 200;
                $imcCategorie  = 'Maigreur';
            } elseif ($imc >= 25 && $imc < 30) {
                $ajustementImc = -150;
                $imcCategorie  = 'Surpoids';
            } elseif ($imc >= 30) {
                $ajustementImc = -300;
                $imcCategorie  = 'Obésité';
            }
        }

        // ── 5. Calories totales journalières ────────────────────────
        $caloriesTotal = round($tdee + $surplusTrimestre + $ajustementImc);

        // ── 6. Macronutriments ───────────────────────────────────────
        $proteines = round(($caloriesTotal * 0.20) / 4);
        $glucides  = round(($caloriesTotal * 0.50) / 4);
        $lipides   = round(($caloriesTotal * 0.30) / 9);

        // ── 7. Micronutriments + aliments + évaluation ───────────────
        return [
            'calories_base'     => round($bmr),
            'calories_total'    => $caloriesTotal,
            'surplus_trimestre' => $surplusTrimestre,
            'ajustement_imc'    => $ajustementImc,
            'imc_categorie'     => $imcCategorie,
            'proteines'         => $proteines,
            'glucides'          => $glucides,
            'lipides'           => $lipides,
            'micronutriments'   => $this->getMicronutriments($trimestre),
            'aliments_top'      => $this->getAlimentsTop($trimestre),
            'evaluation'        => $this->getEvaluation($imc, $trimestre),
            'trimestre'         => $trimestre,
        ];
    }
    /** @return array<mixed> */
    private function getMicronutriments(int $trimestre): array
    {
        return [
            [
                'nom'        => 'Acide folique',
                'valeur'     => $trimestre === 1 ? 600 : 400,
                'unite'      => 'µg',
                'icone'      => '🌿',
                'couleur'    => '#4caf50',
                'sources'    => 'Épinards, lentilles, avocat',
                'importance' => $trimestre === 1 ? 'Critique T1' : 'Important',
            ],
            [
                'nom'        => 'Fer',
                'valeur'     => $trimestre === 1 ? 27 : 30,
                'unite'      => 'mg',
                'icone'      => '🔴',
                'couleur'    => '#f44336',
                'sources'    => 'Viande rouge, lentilles, tofu',
                'importance' => 'Essentiel',
            ],
            [
                'nom'        => 'Calcium',
                'valeur'     => 1000,
                'unite'      => 'mg',
                'icone'      => '🥛',
                'couleur'    => '#2196f3',
                'sources'    => 'Lait, yaourt, amandes',
                'importance' => 'Essentiel',
            ],
            [
                'nom'        => 'Vitamine D',
                'valeur'     => 600,
                'unite'      => 'UI',
                'icone'      => '☀️',
                'couleur'    => '#ff9800',
                'sources'    => 'Poisson gras, œufs, soleil',
                'importance' => 'Important',
            ],
            [
                'nom'        => 'Oméga-3',
                'valeur'     => $trimestre === 3 ? 2.0 : 1.4,
                'unite'      => 'g',
                'icone'      => '🐟',
                'couleur'    => '#00bcd4',
                'sources'    => 'Saumon, noix, lin',
                'importance' => $trimestre === 3 ? 'Critique T3' : 'Important',
            ],
            [
                'nom'        => 'Iode',
                'valeur'     => 220,
                'unite'      => 'µg',
                'icone'      => '🧂',
                'couleur'    => '#9c27b0',
                'sources'    => 'Sel iodé, poisson, laitages',
                'importance' => 'Important',
            ],
        ];
    }
/** @return array<mixed> */
    private function getAlimentsTop(int $trimestre): array
    {
        return match($trimestre) {
            1 => [
                ['nom' => 'Épinards',  'raison' => 'Acide folique',       'icone' => '🥬'],
                ['nom' => 'Lentilles', 'raison' => 'Fer + folates',        'icone' => '🫘'],
                ['nom' => 'Avocat',    'raison' => 'Folates + bons lipides','icone' => '🥑'],
                ['nom' => 'Yaourt',    'raison' => 'Calcium + probiotiques','icone' => '🥛'],
                ['nom' => 'Noix',      'raison' => 'Oméga-3 + magnésium',  'icone' => '🥜'],
            ],
            2 => [
                ['nom' => 'Saumon',  'raison' => 'Oméga-3 + Vit D',      'icone' => '🐟'],
                ['nom' => 'Œufs',    'raison' => 'Protéines + choline',   'icone' => '🥚'],
                ['nom' => 'Brocoli', 'raison' => 'Calcium + Vit C',       'icone' => '🥦'],
                ['nom' => 'Quinoa',  'raison' => 'Protéines complètes',   'icone' => '🌾'],
                ['nom' => 'Banane',  'raison' => 'Potassium + énergie',   'icone' => '🍌'],
            ],
            3 => [
                ['nom' => 'Sardines',      'raison' => 'Calcium + Oméga-3',      'icone' => '🐠'],
                ['nom' => 'Dattes',        'raison' => 'Énergie + fer',           'icone' => '🍑'],
                ['nom' => 'Patate douce',  'raison' => 'Bêta-carotène + fibres', 'icone' => '🍠'],
                ['nom' => 'Poulet',        'raison' => 'Protéines légères',       'icone' => '🍗'],
                ['nom' => 'Amandes',       'raison' => 'Magnésium + calcium',     'icone' => '🌰'],
            ],
            default => [],
        };
    }
/** @return array<mixed> */
    private function getEvaluation(?float $imc, int $trimestre): array
    {
        if ($imc === null) {
            return [
                'niveau'  => 'info',
                'couleur' => 'primary',
                'icone'   => 'ℹ️',
                'message' => 'Calcul basé sur vos données disponibles.',
            ];
        }
        if ($imc < 18.5) {
            return [
                'niveau'  => 'attention',
                'couleur' => 'warning',
                'icone'   => '⚠️',
                'message' => 'Votre IMC indique une maigreur. Un apport calorique supplémentaire est recommandé. Consultez votre médecin.',
            ];
        }
        if ($imc >= 30) {
            return [
                'niveau'  => 'attention',
                'couleur' => 'warning',
                'icone'   => '⚠️',
                'message' => 'Un suivi nutritionnel personnalisé est recommandé. Parlez-en à votre sage-femme.',
            ];
        }
        return [
            'niveau'  => 'normal',
            'couleur' => 'success',
            'icone'   => '✅',
            'message' => 'Vos besoins nutritionnels sont adaptés à votre profil et votre trimestre. Continuez ainsi 💕',
        ];
    }
}
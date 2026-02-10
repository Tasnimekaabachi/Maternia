<?php

namespace App\Form;

use App\Entity\Grosesse;
use App\Entity\Maman;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GrosesseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $includeMaman = $options['include_maman'] ?? true;

        $builder
            ->add('connaitDDR', CheckboxType::class, [
                'required' => false,
                'label' => 'Je connais la date de mes dernières règles (DDR)',
            ])
            ->add('dateDernieresRegles', null, [
                'required' => false,
                'label' => 'Date des dernières règles (DDR)',
            ])
            ->add('dateDebutGrossesse', null, [
                'required' => false,
                'label' => 'Date de début de grossesse (si DDR inconnue)',
            ])
            ->add('statutGrossesse', ChoiceType::class, [
                'choices' => [
                    'En cours' => 'enCours',
                    'À risque' => 'aRisque',
                    'Terminée' => 'terminee',
                ],
                'label' => 'Statut de la grossesse',
            ])
            ->add('typeGrossesse', ChoiceType::class, [
                'choices' => [
                    'Simple' => 'simple',
                    'Multiple' => 'multiple',
                ],
                'label' => 'Type de grossesse',
            ])
            ->add('nombreBebes', null, [
                'required' => false,
                'label' => 'Nombre de bébés (si multiple)',
            ])
            ->add('poidsActuel', null, [
                'required' => false,
                'label' => 'Poids actuel (kg)',
            ])
            ->add('symptomes', TextareaType::class, [
                'required' => false,
                'label' => 'Symptômes',
                'attr' => ['rows' => 3],
            ])
            // indiceRisque : calculé automatiquement → pas saisi par la maman
            ->add('dateAccouchementReelle', null, [
                'required' => false,
                'label' => 'Date d’accouchement réelle',
            ])
            ->add('nomBebe', null, [
                'required' => false,
                'label' => 'Nom / prénom bébé',
            ])
            ->add('sexeBebe', null, [
                'required' => false,
                'label' => 'Sexe bébé',
            ])
            ->add('poidsNaissance', null, [
                'required' => false,
                'label' => 'Poids naissance (kg)',
            ])
            ->add('tailleNaissance', null, [
                'required' => false,
                'label' => 'Taille naissance (cm)',
            ])
            ->add('etatNaissance', null, [
                'required' => false,
                'label' => 'État naissance',
            ])
            ->add('commentaireGeneral', TextareaType::class, [
                'required' => false,
                'label' => 'Commentaire général',
                'attr' => ['rows' => 3],
            ])
        ;

        if ($includeMaman) {
            $builder->add('maman', EntityType::class, [
                'class' => Maman::class,
                'choice_label' => 'id',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Grosesse::class,
            'include_maman' => true,
        ]);
    }
}

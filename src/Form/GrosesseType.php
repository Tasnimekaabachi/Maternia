<?php

namespace App\Form;

use App\Entity\Grosesse;
use App\Entity\Maman;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('dateDernieresRegles', DateType::class, [
                'required' => false,
                'label' => 'Date des dernières règles (DDR)',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'html5' => true,
            ])
            ->add('dateDebutGrossesse', DateType::class, [
                'required' => false,
                'label' => 'Date de début de grossesse (si DDR inconnue)',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'html5' => true,
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
                // HTML5 : commence à 2 pour une grossesse multiple
                'attr' => [
                    'min' => 2,
                ],
            ])
            ->add('poidsActuel', null, [
                'required' => false,
                'label' => 'Poids actuel (kg)',
            ])
            ->add('nausee', CheckboxType::class, ['required' => false, 'label' => 'Nausée'])
            ->add('vomissement', CheckboxType::class, ['required' => false, 'label' => 'Vomissement'])
            ->add('saignement', CheckboxType::class, ['required' => false, 'label' => 'Saignement'])
            ->add('fievre', CheckboxType::class, ['required' => false, 'label' => 'Fièvre'])
            ->add('douleurAbdominale', CheckboxType::class, ['required' => false, 'label' => 'Douleur abdominale'])
            ->add('fatigue', CheckboxType::class, ['required' => false, 'label' => 'Fatigue'])
            ->add('vertiges', CheckboxType::class, ['required' => false, 'label' => 'Vertiges'])
            // indiceRisque : calculé automatiquement → pas saisi par la maman
            ->add('dateAccouchementReelle', DateType::class, [
                'required' => false,
                'label' => 'Date d’accouchement réelle',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'html5' => true,
            ])
            ->add('nomBebe', null, [
                'required' => false,
                'label' => 'Nom / prénom bébé',
            ])
            ->add('sexeBebe', ChoiceType::class, [
                'required' => false,
                'label' => 'Sexe du bébé',
                'choices' => [
                    'Fille' => 'F',
                    'Garçon' => 'M',
                ],
                // menu déroulant comme typeGrossesse (pas radio)
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Sélectionner',
            ])
            ->add('poidsNaissance', null, [
                'required' => false,
                'label' => 'Poids naissance (kg)',
            ])
            ->add('tailleNaissance', null, [
                'required' => false,
                'label' => 'Taille naissance (cm)',
            ])
            ->add('etatNaissance', ChoiceType::class, [
                'required' => false,
                'label' => 'État de naissance',
                'choices' => [
                    'Sain' => 'sain',
                    'Prématuré' => 'premature',
                    'Soins' => 'soins',
                    'Autre' => 'autre',
                ],
                // menu déroulant comme sexeBebe
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Sélectionner',
            ])
            ->add('commentaireGeneral', TextareaType::class, [
                'required' => false,
                'label' => 'Commentaire général',
                'attr' => ['rows' => 3],
            ])
            ->add('bebes', CollectionType::class, [
                'entry_type' => BebesType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'label' => 'Bébés (liste détaillée)',
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

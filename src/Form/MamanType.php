<?php

namespace App\Form;

use App\Entity\Maman;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
class MamanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroUrgence', TextType::class, [
                'label' => 'Numéro d\'urgence (téléphone Tunisie 🇹🇳)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'xx xxx xxx',
                    'maxlength' => 11,
                    'inputmode' => 'numeric',
                    'pattern' => '[0-9\s]*',
                    'data-prefix' => '+216',
                ],
                'help' => '8 chiffres, commençant par 2, 4, 5 ou 9 (ex. xx xxx xxx)',
            ])
            // Champ email en TextType pour éviter la validation HTML5.
            ->add('email', TextType::class, [
                'label' => 'Adresse email',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
                // validation faite uniquement dans l'entité Maman (Assert\Email) pour éviter le doublon de messages
                'help' => 'Un email de confirmation sera envoyé à cette adresse.',
            ])
            ->add('groupeSanguin', ChoiceType::class, [
                'label' => 'Groupe sanguin',
                'attr' => ['class' => 'form-select'],
                'placeholder' => '-- Choisissez votre groupe sanguin --',
                'choices' => [
                    'A+' => 'A+',
                    'A-' => 'A-',
                    'B+' => 'B+',
                    'B-' => 'B-',
                    'AB+' => 'AB+',
                    'AB-' => 'AB-',
                    'O+' => 'O+',
                    'O-' => 'O-',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez sélectionner votre groupe sanguin.',
                    ]),
                ],
            ])
            ->add('taille', NumberType::class, [
                'label' => 'Taille (cm)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex. 165',
                    'min' => 130,
                    'max' => 220,
                    'step' => 0.1,
                ],
                'help' => 'Entre 130 et 220 cm',
            ])
            ->add('poids', NumberType::class, [
                'label' => 'Poids (kg)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex. 65',
                    'min' => 30,
                    'max' => 140,
                    'step' => 0.1,
                ],
                'help' => 'Entre 30 et 140 kg',
            ])
            ->add('allergies', TextareaType::class, [
                'label' => 'Allergies connues',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'Indiquez vos allergies éventuelles'],
            ])
            ->add('antecedentsMedicaux', TextareaType::class, [
                'label' => 'Antécédents médicaux',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 2],
            ])
            ->add('maladiesChroniques', TextareaType::class, [
                'label' => 'Maladies chroniques',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 2],
            ])
            ->add('medicamentsActuels', TextareaType::class, [
                'label' => 'Médicaments actuels',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 2],
            ])
            ->add('fumeur', ChoiceType::class, [
                'label' => 'Fumeuse',
                'attr' => ['class' => 'form-select'],
                'choices' => ['Non' => false, 'Oui' => true],
                'placeholder' => false,
            ])
            ->add('consommationAlcool', ChoiceType::class, [
                'label' => 'Consommation d\'alcool',
                'attr' => ['class' => 'form-select'],
                'choices' => ['Non' => false, 'Oui' => true],
                'placeholder' => false,
            ])
            ->add('niveauActivitePhysique', ChoiceType::class, [
                'label' => 'Niveau d\'activité physique',
                'attr' => ['class' => 'form-select'],
                'choices' => [
                    'Sédentaire' => 'Sédentaire',
                    'Léger (1-2 fois/semaine)' => 'Léger',
                    'Modéré (3-4 fois/semaine)' => 'Modéré',
                    'Actif (5+ fois/semaine)' => 'Actif',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez sélectionner votre niveau d\'activité physique.',
                    ]),
                ],
            ])
            ->add('habitudesAlimentaires', TextType::class, [
                'label' => 'Habitudes alimentaires (régime, préférences)',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex. végétarienne, sans gluten…'],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez indiquer vos habitudes alimentaires.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Maman::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Consultation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ConsultationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categorie', TextType::class, [
                'label' => 'Catégorie *',
                'required' => true,
                'attr' => [
                    'maxlength' => 100,
                    'placeholder' => 'Ex: Pédiatrie, Gynécologie, Nutrition...'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Description détaillée de la consultation...'
                ]
            ])
            ->add('pour', ChoiceType::class, [
                'label' => 'Public cible *',
                'required' => true,
                'choices' => [
                    '👶 Bébé' => 'BEBE',
                    '🤰 Maman' => 'MAMAN',
                    '👨‍👩‍👧 Les deux' => 'LES_DEUX'
                ]
            ])
            ->add('image', TextType::class, [
                'label' => 'Image (nom du fichier)',
                'required' => false,
                'attr' => [
                    'maxlength' => 255,
                    'placeholder' => 'pediatrie.jpg, gynecologie.png...'
                ]
            ])
            ->add('icon', TextType::class, [
                'label' => 'Icône FontAwesome *',
                'required' => false,
                'attr' => [
                    'maxlength' => 255,
                    'placeholder' => 'fas fa-baby, fas fa-heart, fas fa-stethoscope...'
                ],
                'help' => 'Utilisez les classes FontAwesome. Ex: fas fa-baby, fas fa-heartbeat'
            ])
            ->add('statut', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('ordreAffichage', IntegerType::class, [
                'label' => 'Ordre d\'affichage *',
                'required' => true,
                'attr' => [
                    'min' => 1,
                    'max' => 100,
                    'placeholder' => '1, 2, 3...'
                ],
                'help' => 'Détermine l\'ordre d\'affichage dans la liste (1 = premier)'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Consultation::class,
        ]);
    }
}
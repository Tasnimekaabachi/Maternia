<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BebesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => false,
                'label' => 'Prénom du bébé',
            ])
            ->add('sexe', ChoiceType::class, [
                'required' => false,
                'label' => 'Sexe',
                'choices' => [
                    'Fille' => 'F',
                    'Garçon' => 'M',
                ],
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Sélectionner',
            ])
            ->add('poids', NumberType::class, [
                'required' => false,
                'label' => 'Poids (kg)',
                'scale' => 2,
                'attr' => ['min' => 0, 'step' => '0.01'],
            ])
            ->add('taille', NumberType::class, [
                'required' => false,
                'label' => 'Taille (cm)',
                'scale' => 1,
                'attr' => ['min' => 0, 'step' => '0.1'],
            ])
            ->add('etat', ChoiceType::class, [
                'required' => false,
                'label' => 'État',
                'choices' => [
                    'Sain' => 'sain',
                    'Prématuré' => 'premature',
                    'Soins' => 'soins',
                    'Autre' => 'autre',
                ],
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Sélectionner',
            ])
        ;
    }
}


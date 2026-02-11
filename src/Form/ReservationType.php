<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [new NotBlank(['message' => 'Le nom est obligatoire'])],
                'attr' => ['class' => 'form-control']
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [new NotBlank(['message' => 'Le prénom est obligatoire'])],
                'attr' => ['class' => 'form-control']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est obligatoire']),
                    new Email(['message' => 'Veuillez entrer un email valide'])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'constraints' => [new NotBlank(['message' => 'Le téléphone est obligatoire'])],
                'attr' => ['class' => 'form-control']
            ])
            ->add('typePatient', ChoiceType::class, [
    'label' => 'Pour qui ?',
    'choices' => [
        'Maman' => 'MAMAN',
        'Bébé' => 'BEBE',
    ],
    'expanded' => false,  // CHANGÉ DE true À false
    'multiple' => false,
    'constraints' => [new NotBlank(['message' => 'Veuillez sélectionner une option'])],
            ])
            ->add('moisGrossesse', IntegerType::class, [
                'label' => 'Mois de grossesse',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 9
                ]
            ])
            ->add('dateNaissanceBebe', DateType::class, [
                'label' => 'Date de naissance du bébé',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])

            ->add('notes', TextareaType::class, [
                'label' => 'Notes supplémentaires',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_protection' => true,
        ]);
    }
}
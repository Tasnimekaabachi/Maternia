<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class , [
            'label' => 'Nom',
            'attr' => ['class' => 'form-control'],
            'empty_data' => '',
            'constraints' => [
                new NotBlank(['message' => 'Le nom est obligatoire']),
            ]
        ])
            ->add('prenom', TextType::class , [
            'label' => 'Prénom',
            'attr' => ['class' => 'form-control'],
            'empty_data' => '',
            'constraints' => [
                new NotBlank(['message' => 'Le prénom est obligatoire']),
            ]
        ])
            ->add('email', EmailType::class , [
            'label' => 'Email',
            'attr' => ['class' => 'form-control'],
            'empty_data' => '',
            'constraints' => [
                new NotBlank(['message' => 'L\'email est obligatoire']),
                new Email(['message' => 'Email invalide']),
            ]
        ])
            ->add('plainPassword', RepeatedType::class , [
            'type' => PasswordType::class ,
            'mapped' => false,
            'required' => false,
            'first_options' => ['label' => 'Nouveau mot de passe (laisser vide pour ne pas changer)', 'attr' => ['class' => 'form-control', 'autocomplete' => 'new-password']],
            'second_options' => ['label' => 'Confirmer le mot de passe', 'attr' => ['class' => 'form-control', 'autocomplete' => 'new-password']],
            'invalid_message' => 'Les mots de passe doivent correspondre.',
            'constraints' => [
                new Length([
                    'min' => 6,
                    'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} caractères',
                    'max' => 4096,
                ]),
            ],
        ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Decoupled from User entity to allow safe manual mapping in controller
        ]);
    }
}

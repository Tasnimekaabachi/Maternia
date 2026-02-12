<?php

namespace App\Form;

use App\Entity\ReservationClient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomClient', TextType::class, [
                'label' => 'Nom du patient',
                'attr' => ['class' => 'form-control']
            ])
            ->add('prenomClient', TextType::class, [
                'label' => 'Prénom du patient',
                'attr' => ['class' => 'form-control']
            ])
            ->add('emailClient', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control']
            ])
            ->add('telephoneClient', TextType::class, [
                'label' => 'Téléphone',
                'attr' => ['class' => 'form-control']
            ])
            ->add('typePatient', ChoiceType::class, [
                'label' => 'Type de patient',
                'choices' => [
                    'Maman' => 'MAMAN',
                    'Bébé' => 'BEBE'
                ],
                'required' => false,
                'placeholder' => 'Non défini',
                'attr' => ['class' => 'form-select']
            ])
            ->add('moisGrossesse', IntegerType::class, [
                'label' => 'Mois de grossesse',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateNaissanceBebe', DateType::class, [
                'label' => 'Date naissance bébé',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes internes',
                'required' => false,
                'attr' => ['rows' => 3]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReservationClient::class,
        ]);
    }
}

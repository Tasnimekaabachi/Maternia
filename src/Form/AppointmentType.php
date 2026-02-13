<?php

namespace App\Form;

use App\Form\Model\AppointmentData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => false,
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
                'required' => false,
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('dateSouhaitee', TextType::class, [
                'label' => 'Date souhaitée',
                'required' => false,
            ])
            ->add('typeConsultation', ChoiceType::class, [
                'label' => 'Type de consultation',
                'required' => false,
                'placeholder' => 'Choisir...',
                'choices' => [
                    'Suivi de grossesse' => 'suivi_grossesse',
                    'Consultation postnatale' => 'consultation_postnatale',
                    'Conseil allaitement' => 'allaitement',
                    'Bilan' => 'bilan',
                    'Autre' => 'autre',
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AppointmentData::class,
        ]);
    }
}


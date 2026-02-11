<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\EventCat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'label' => 'Titre de l\'événement',
                'attr' => ['placeholder' => 'ex: Atelier Yoga Prénatal']
            ])
            ->add('description', null, [
                'label' => 'Description détaillée',
                'attr' => ['rows' => 5, 'placeholder' => 'Décrivez votre événement...']
            ])
            ->add('startAt', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
            ])
            ->add('endAt', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
            ])
            ->add('location', null, [
                'label' => 'Lieu',
                'attr' => ['placeholder' => 'ex: Salle Polyvalente']
            ])
            ->add('eventCat', EntityType::class, [
                'class' => EventCat::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisissez une catégorie',
                'label' => 'Catégorie',
            ])
            // Add field for selecting existing images
            ->add('selectedImage', HiddenType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner une image']),
                ],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
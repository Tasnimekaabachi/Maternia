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

use App\Entity\Requirement;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'label' => 'Titre de l\'événement',
                'attr' => ['placeholder' => 'ex: Yoga prénatal']
            ])
            ->add('description', null, [
                'label' => 'Description détaillée',
                'attr' => ['placeholder' => 'Décrivez l\'activité...', 'rows' => 4]
            ])
            ->add('startAt', null, [
                'label' => 'Date et heure de début',
                'widget' => 'single_text',
            ])
            ->add('endAt', null, [
                'label' => 'Date et heure de fin',
                'widget' => 'single_text',
            ])
            ->add('location', null, [
                'label' => 'Lieu',
                'attr' => ['placeholder' => 'ex: Salle A, Rez-de-chaussée']
            ])
            ->add('eventCat', EntityType::class, [
                'class' => EventCat::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'placeholder' => 'Choisir une catégorie',
            ])
            ->add('isWeekly', null, [
                'label' => 'Événement hebdomadaire',
                'required' => false,
                'attr' => ['class' => 'weekly-toggle']
            ])
            ->add('dayOfWeek', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'label' => 'Jour de la semaine',
                'required' => false,
                'choices' => [
                    'Lundi' => 'Monday',
                    'Mardi' => 'Tuesday',
                    'Mercredi' => 'Wednesday',
                    'Jeudi' => 'Thursday',
                    'Vendredi' => 'Friday',
                    'Samedi' => 'Saturday',
                    'Dimanche' => 'Sunday',
                ],
                'placeholder' => 'Choisir un jour',
                'attr' => ['class' => 'weekly-field form-select']
            ])
            ->add('startTime', null, [
                'label' => 'Heure de début',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'weekly-field']
            ])
            ->add('endTime', null, [
                'label' => 'Heure de fin',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'weekly-field']
            ])
            ->add('capacity', null, [
                'label' => 'Nombre de places',
                'attr' => ['placeholder' => 'ex: 20', 'min' => 2],
                'required' => true,
            ])
            ->add('showRequirements', CheckboxType::class, [
                'label' => 'Besoin de matériel spécifique ?',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'requirements-toggle']
            ])
            ->add('requirements', EntityType::class, [
                'class' => Requirement::class,
                'label' => 'Matériel nécessaire',
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'name',
                'attr' => ['class' => 'requirements-list-container']
            ])
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
<?php

namespace App\Form;

use App\Entity\ConsultationCreneau;
use App\Entity\Consultation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class ConsultationCreneauType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('consultation', EntityType::class, [
                'label' => 'Consultation',
                'class' => Consultation::class,
                'choices' => $options['consultations'] ?? [],
                'choice_label' => 'categorie',
                'placeholder' => 'Sélectionnez une consultation',
                'attr' => ['class' => 'form-select']
            ])
            ->add('nomMedecin', TextType::class, [
                'label' => 'Nom du médecin',
                'attr' => ['maxlength' => 100]
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Photo du médecin',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez envoyer une image (JPEG, PNG, WebP ou GIF).',
                    ]),
                ],
                'attr' => ['accept' => 'image/*'],
            ])
            ->add('descriptionMedecin', TextareaType::class, [
                'label' => 'Description du médecin',
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('specialiteMedecin', TextType::class, [
                'label' => 'Spécialité',
                'required' => false,
                'attr' => ['maxlength' => 100]
            ])
            ->add('jour', DateType::class, [
                'label' => 'Jour',
                'widget' => 'single_text',
                'required' => false,
                'html5' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('heureDebut', \Symfony\Component\Form\Extension\Core\Type\TimeType::class, [
                'label' => 'Heure de début',
                'widget' => 'single_text',
                'required' => false,
                'html5' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('heureFin', \Symfony\Component\Form\Extension\Core\Type\TimeType::class, [
                'label' => 'Heure de fin',
                'widget' => 'single_text',
                'required' => false,
                'html5' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('dureeMinutes', IntegerType::class, [
                'label' => 'Durée (minutes)',
                'required' => true,
                'attr' => ['min' => 5, 'max' => 480],
                'data' => 30
            ])
            ->add('nombrePlaces', IntegerType::class, [
                'label' => 'Capacité (Nombre de patients)',
                'required' => true,
                'attr' => ['min' => 1, 'max' => 100],
                'data' => 1
            ])
            ->add('statutReservation', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Disponible' => 'DISPONIBLE',
                    'Réservé' => 'RESERVE',
                    'Annulé' => 'ANNULE',
                    'Confirmé' => 'CONFIRME'
                ],
                'attr' => ['class' => 'form-select']
            ])

            ->add('creneauxHoraires', CollectionType::class, [
                'entry_type' => CreneauHoraireType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'mapped' => false,
                'attr' => ['class' => 'creneaux-collection'],
                'prototype' => true,
                'prototype_name' => '__name__',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConsultationCreneau::class,
            'consultations' => [],
        ]);
        
        $resolver->setAllowedTypes('consultations', 'array');
    }
}
<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class CreneauHoraireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('jour', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'label' => 'Jour',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control modern-input']
            ])
            ->add('heureDebut', \Symfony\Component\Form\Extension\Core\Type\TimeType::class, [
                'label' => 'Heure de début',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control modern-input']
            ])
            ->add('heureFin', \Symfony\Component\Form\Extension\Core\Type\TimeType::class, [
                'label' => 'Heure de fin',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control modern-input']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Ce formulaire ne sera pas mappé directement à une entité
            'data_class' => null,
        ]);
    }
}

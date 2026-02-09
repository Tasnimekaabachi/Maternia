<?php

namespace App\Form;

use App\Entity\OffreBabySitter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OffreBabySitterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomBabysitter')
            ->add('telephone')
            ->add('experience')
            ->add('ville')
            ->add('tarif')
            ->add('description')
            ->add('disponibilite')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OffreBabySitter::class,
        ]);
    }
}

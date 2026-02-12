<?php

namespace App\Form;

use App\Entity\DemandeBabySitter;
use App\Entity\OffreBabySitter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeBabySitterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomParent')
            ->add('emailParent')
            ->add('message')
            ->add('offre', EntityType::class, [
                'class' => OffreBabySitter::class,
                'choice_label' => 'nomBabysitter',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandeBabySitter::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\OffreBabySitter;
use App\Service\VillesTunisie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OffreBabySitterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomBabysitter', TextType::class)
            ->add('telephone', TextType::class)
            ->add('email', EmailType::class, ['required' => false, 'label' => 'Email (pour alertes)'])
            ->add('experience', IntegerType::class)
            ->add('ville', ChoiceType::class, [
                'placeholder' => 'Choisir une ville',
                'choices' => VillesTunisie::getChoicesForm(),
            ])
            ->add('tarif', NumberType::class, [
                'scale' => 2,
            ])
            ->add('description', TextareaType::class)
            ->add('disponibilite', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OffreBabySitter::class,
        ]);
    }
}

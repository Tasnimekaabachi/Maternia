<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'required' => false, // Contrôle côté serveur uniquement
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
                'required' => false,
                'html5' => false, // Pas d\'attributs min/max/step HTML5
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'required' => false,
            ])
            ->add('ratingAverage', NumberType::class, [
                'label' => 'Note moyenne (0–5, optionnel)',
                'required' => false,
                'html5' => false,
            ])
            ->add('categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'required' => false,
                'placeholder' => '— Aucune —',
                'choices' => [
                    'Grossesse' => 'grossesse',
                    'Bébé' => 'bebe',
                    'Soins' => 'soins',
                    'Mode' => 'mode',
                    'Équipement' => 'equipement',
                    'Services' => 'services',
                ],
            ])
            ->add('poidsKg', NumberType::class, [
                'label' => 'Poids (kg)',
                'required' => false,
                'html5' => false,
            ])
            ->add('sku', TextType::class, [
                'label' => 'SKU (optionnel)',
                'required' => false,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image du produit',
                'required' => false,
                'mapped' => false,
                'attr' => ['accept' => 'image/*'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}

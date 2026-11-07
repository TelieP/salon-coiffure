<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
            ->add('rating', ChoiceType::class, [
                'label' => 'Note (de 1 à 5 étoiles)',
                'choices' => [
                    '⭐⭐⭐⭐⭐ 5 - Excellent' => 5,
                    '⭐⭐⭐⭐ 4 - Très bien' => 4,
                    '⭐⭐⭐ 3 - Bien' => 3,
                    '⭐⭐ 2 - Moyen' => 2,
                    '⭐ 1 - Décevant' => 1,
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Votre commentaire / Expérience',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Partagez votre expérience au salon...']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
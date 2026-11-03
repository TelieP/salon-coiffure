<?php

namespace App\Form;

use App\Entity\Appointment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startAt', DateTimeType::class, [
                'label' => 'Date et heure du rendez-vous',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('clientName', TextType::class, [
                'label' => 'Votre Nom & Prénom',
                //'mapped' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'ex: Jean Dupont'],
            ])
            ->add('clientEmail', EmailType::class, [
                'label' => 'Votre Adresse Email',
                //'mapped' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'ex: jean.dupont@email.com'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
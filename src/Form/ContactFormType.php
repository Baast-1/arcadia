<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('who', ChoiceType::class, [
                'choices' => [
                    'Particulier' => 'Particulier',
                    'Société' => 'Société',
                    'Association' => 'Association',
                    'Institution' => 'Institution',
                ],
                'multiple' => false,
            ])
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('email', EmailType::class)
            ->add('telephone', TelType::class)
            ->add('recontact', ChoiceType::class, [
                'choices' => [
                    'Téléphone' => 'Téléphone',
                    'Email' => 'Email',
                ],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('demande', TextareaType::class)
            ->add('convives', IntegerType::class)
            ->add('date', DateType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}

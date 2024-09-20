<?php

namespace App\Form;

use App\Entity\Animals;
use App\Entity\Feed;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('food')
            ->add('quantity')
            ->add('time', DateTimeType::class, [
            ])
            ->add('animal', EntityType::class, [
                'class' => Animals::class,
                'choice_label' => 'name',
                'disabled' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Feed::class,
        ]);
    }
}

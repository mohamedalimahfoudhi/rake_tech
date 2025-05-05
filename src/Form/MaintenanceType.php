<?php

namespace App\Form;

use App\Entity\Maintenance;
use App\Entity\Materiel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaintenanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('datemaintenance', DateType::class, [
                'label' => 'Date de maintenance',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => ['rows' => 4],
            ])
            ->add('statutmaintenance', ChoiceType::class, [
                'choices' => [
                    'Planifié' => 'Planifié',
                    'En cours' => 'En cours',
                    'Terminé' => 'Terminé',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('materielid', EntityType::class, [
                'class' => Materiel::class,
                'choice_label' => 'type',
                'label' => 'Matériel',
                'required' => true,
                'placeholder' => 'Sélectionnez un matériel',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Maintenance::class,
        ]);
    }
} 
<?php

namespace App\Form;

use App\Entity\Materiel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaterielType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', TextType::class, [
                'label' => 'Type de matériel',
                'required' => true,
                'attr' => ['minlength' => 2, 'maxlength' => 255],
            ])
            ->add('typesport', TextType::class, [
                'label' => 'Type de sport',
                'required' => true,
                'attr' => ['minlength' => 2, 'maxlength' => 255],
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Prix',
                'required' => false,
                'currency' => 'EUR',
                'attr' => ['min' => 0],
            ])
            ->add('datereservation', DateType::class, [
                'label' => 'Date de réservation',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Disponible' => 'Disponible',
                    'Réservé' => 'Réservé',
                    'Sous maintenance' => 'Sous maintenance',
                ],
                'required' => false,
            ])
            ->add('ownertype', ChoiceType::class, [
                'label' => 'Type de propriétaire',
                'choices' => [
                    'Club' => 'Club',
                    'Fédération' => 'Fédération',
                    'Privé' => 'Privé'
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Materiel::class,
        ]);
    }
} 
<?php

namespace App\Form;

use App\Entity\Emprunt;
use App\Entity\Materiel;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmpruntType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateemprunt', DateType::class, [
                'label' => 'Date d\'emprunt',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('dateretour', DateType::class, [
                'label' => 'Date de retour',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('statutemprunt', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Emprunté' => 'Emprunté',
                    'Retourné' => 'Retourné',
                    'En retard' => 'En retard',
                ],
                'required' => true,
            ])
            ->add('materielid', EntityType::class, [
                'class' => Materiel::class,
                'choice_label' => 'type',
                'label' => 'Matériel',
                'required' => true,
                'placeholder' => 'Sélectionnez un matériel',
            ])
            ->add('userid', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $user) {
                    return $user->getPrenom() . ' ' . $user->getNom();
                },
                'label' => 'user',
                'required' => true,
                'placeholder' => 'Sélectionnez un user',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Emprunt::class,
        ]);
    }
} 
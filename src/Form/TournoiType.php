<?php

namespace App\Form;

use App\Entity\Tournoi;
use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class TournoiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du tournoi'
            ])
            ->add('datedebut', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Date de début'
            ])
            ->add('datefin', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Date de fin'
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu du tournoi'
            ])
            // Changed to a text field for 'typesport' (any text input)
            ->add('typesport', TextType::class, [
                'label' => 'Type de sport'
            ])
            // Updated 'statut' field with specific choices: 'Prévu', 'En_cours', 'Terminé', 'Annulé'
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Prévu' => 'Prévu',
                    'En cours' => 'En_cours',
                    'Terminé' => 'Terminé',
                    'Annulé' => 'Annulé',
                ],
                'label' => 'Statut du tournoi'
            ])
            ->add('recompense', TextType::class, [
                'label' => 'Récompense'
            ])
            ->add('eventid', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'name',  // Assuming 'nom' is a field in Evenement
                'label' => 'Événement associé'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tournoi::class,
        ]);
    }
}

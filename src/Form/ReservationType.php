<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\User;
use App\Entity\Billet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Expression;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Date;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('datereservation', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de réservation',
                'required' => true,
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => new \DateTime(),
                    ])
                ]
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Confirmée' => 'Confirmée',
                    'Annulée' => 'Annulée',
                    'En attente' => 'En_attente',
                    'Terminée' => 'Terminée',   
                ],
                'label' => 'Statut de la réservation'
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'TERRAIN' => 'TERRAIN',
                    'BILLET' => 'BILLET',
                ],
                'label' => 'Type de réservation'
            ])
            ->add('utilisateurid', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Utilisateur',
                'placeholder' => 'Sélectionnez un utilisateur',
                'required' => true
            ])
            ->add('billetid', EntityType::class, [
                'class' => Billet::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('b')
                        ->where('b.statut = :statut')
                        ->setParameter('statut', 'Valide')
                        ->orderBy('b.typebillet', 'ASC');
                },
                'group_by' => function($billet) {
                    return $billet->getTypeBillet();
                },
                'choice_label' => function($billet) {
                    return sprintf('Prix: %.2f€ - Qté: %d', $billet->getPrix(), $billet->getQuantite());
                },
                'multiple' => true,
                'expanded' => true,
                'label' => 'Billets disponibles',
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}

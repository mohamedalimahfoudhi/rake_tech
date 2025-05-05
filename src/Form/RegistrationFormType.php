<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Email'
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'label' => 'Password',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('prenom', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'First Name'
            ])
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Last Name'
            ])
            ->add('genre', ChoiceType::class, [
                'choices' => [
                    'Male' => 'Male',
                    'Female' => 'Female',
                    'Other' => 'Other'
                ],
                'attr' => ['class' => 'form-control'],
                'label' => 'Gender',
                'required' => false
            ])
            ->add('numeroTelephone', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Phone Number',
                'required' => false
            ])
            ->add('adresse', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Address',
                'required' => false
            ])
            ->add('nomOrganisation', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Organization Name',
                'required' => false
            ])
            ->add('role', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'roleNom',
                'attr' => ['class' => 'form-control'],
                'label' => 'Role',
                'required' => true,
                'placeholder' => 'Select your role'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
} 
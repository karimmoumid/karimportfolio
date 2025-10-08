<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom complet',
                'attr' => [
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Votre nom complet'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Votre nom complet est requis.'])
                ]
            ])
            ->add('email', EmailType::class, ['label' => 'Adresse email',
                'attr' => [
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'votre.email@exemple.com'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est obligatoire.']),
                    new Email(['message' => 'Votre email n\'est pas valide.']),
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Les mots de passe doivent être identiques.',
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Mot de passe'
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez saisir un mot de passe',
                        ]),
                        new Regex([
                            'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/',
                            'message' => 'Le mot de passe doit contenir exactement 16 caractères, dont au moins une majuscule, une minuscule, un chiffre et un symbole.',
                        ]),
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => [
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Confirmer le mot de passe'
                    ]
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J\'accepte les conditions d\'utilisation',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions d\'utilisation.',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => '🚀 Créer mon compte',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg w-100'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

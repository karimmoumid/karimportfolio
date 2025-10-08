<?php
namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('senderName', TextType::class, [
                'label' => 'Votre nom *',
                'attr' => [
                    'placeholder' => 'Votre nom complet',
                    'class' => 'form-control form-control-lg'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Votre nom ne peut pas être vide'
                    ])
                ]
            ])
            ->add('senderEmail', EmailType::class, [
                'label' => 'Votre email *',
                'attr' => [
                    'placeholder' => 'votre.email@exemple.com',
                    'class' => 'form-control form-control-lg'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner votre adresse email.'
                    ]),
                    new Email([
                        'message' => 'L\'adresse email "{{ value }}" n\'est pas valide.'
                    ])
                ]
            ])
            ->add('company', TextType::class, [
                'label' => 'Entreprise (optionnel)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nom de votre entreprise',
                    'class' => 'form-control form-control-lg'
                ]
            ])
            ->add('subject', TextType::class, [
                'label' => 'Sujet *',
                'attr' => [
                    'placeholder' => 'Sujet de votre message',
                    'class' => 'form-control form-control-lg'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Votre sujet ne peut pas être vide'
                    ])
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Message *',
                'attr' => [
                    'placeholder' => 'Décrivez votre projet, vos besoins...',
                    'rows' => 6,
                    'class' => 'form-control form-control-lg'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Votre message ne peut pas être vide'
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => '📧 Envoyer le message',
                'attr' => ['class' => 'btn btn-primary btn-lg w-100']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}

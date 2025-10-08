<?php
// src/Form/ProjectType.php
namespace App\Form;

use App\Entity\Project;
use App\Entity\Skill;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProjectType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options): void
{
$builder
->add('title', TextType::class, [
'label' => 'Titre du projet',
'attr' => [
'class' => 'form-control',
'placeholder' => 'Ex: Site e-commerce responsive'
],
'constraints' => [
new NotBlank(['message' => 'Le titre est requis'])
]
])
->add('description', TextareaType::class, [
'label' => 'Description courte',
'attr' => [
'class' => 'form-control',
'rows' => 3,
'placeholder' => 'Une courte description du projet (max 200 caractères)'
],
'constraints' => [
new NotBlank(['message' => 'La description est requise'])
]
])
->add('fullDescription', TextareaType::class, [
'label' => 'Description complète',
'attr' => [
'class' => 'form-control',
'rows' => 6,
'placeholder' => 'Description détaillée du projet, technologies utilisées, défis relevés...'
],
'required' => false
])
->add('imageFile', FileType::class, [
'label' => 'Image du projet',
'mapped' => false,
'required' => false,
'attr' => [
'class' => 'form-control',
'accept' => 'image/*'
],
'constraints' => [
new File([
'maxSize' => '2048k',
'mimeTypes' => [
'image/jpeg',
'image/png',
'image/webp',
],
'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG, WebP)',
])
],
'help' => 'Formats acceptés : JPG, PNG, WebP (max 2MB)'
])
    ->add('galleryImagesFiles', FileType::class, [
        'label' => 'Images de la galerie',
        'mapped' => false,
        'required' => false,
        'multiple' => true, // IMPORTANT : permet la sélection multiple
        'attr' => [
            'class' => 'form-control',
            'accept' => 'image/*',
            'multiple' => 'multiple' // Attribut HTML pour sélection multiple
        ],
        'constraints' => [
            new All([ // Validation pour chaque fichier
                new File([
                    'maxSize' => '2048k',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                    ],
                    'mimeTypesMessage' => 'Chaque image doit être valide (JPG, PNG, WebP)',
                ])
            ])
        ],
        'help' => 'Images supplémentaires pour la galerie du projet - Vous pouvez sélectionner plusieurs images (Ctrl+Click)'
    ])
->add('skill', EntityType::class, [
'class' => Skill::class,
'label' => 'Technologies utilisées',
'choice_label' => 'name',
'multiple' => true,
'expanded' => true,
'attr' => [
'class' => 'form-check'
],
'required' => false
])
->add('submit', SubmitType::class, [
'label' => 'Enregistrer',
'attr' => [
'class' => 'btn btn-success'
]
])
;
}

public function configureOptions(OptionsResolver $resolver): void
{
$resolver->setDefaults([
'data_class' => Project::class,
]);
}
}

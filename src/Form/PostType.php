<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\PostMedia;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label'       => 'Content',
                'constraints' => [new NotBlank()],
                'attr'        => ['rows' => 5, 'placeholder' => 'What\'s on your mind?'],
            ])
            ->add('visibility', ChoiceType::class, [
                'label'   => 'Visibility',
                'choices' => Post::getVisibilityChoices(),
            ])
            ->add('mediaList', CollectionType::class, [
                'label'         => 'Media',
                'entry_type'    => PostMediaType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'required'      => false,
                'prototype'     => true,
                'entry_options' => ['label' => false],
            ])
            ->add('mediaFiles', FileType::class, [
                'label'       => 'Upload Media',
                'mapped'      => false,
                'required'    => false,
                'multiple'    => true,
                'constraints' => [
                    new All([
                        new File([
                            'maxSize'          => '50M',
                            'mimeTypes'        => ['image/*', 'video/*'],
                            'mimeTypesMessage' => 'Please upload a valid image or video file.',
                        ]),
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'csrf_token_id' => 'post',
        ]);
    }
}
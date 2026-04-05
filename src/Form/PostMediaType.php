<?php

namespace App\Form;

use App\Entity\PostMedia;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PostMediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mediaUrl', TextType::class, [
                'label'       => 'Media URL',
                'required'    => false,
                'empty_data'  => '',
                'attr'        => ['placeholder' => 'https://...'],
            ])
            ->add('mediaFile', FileType::class, [
                'label'       => 'Upload Media',
                'mapped'      => false,
                'required'    => false,
                'constraints' => [
                    new File([
                        'maxSize'          => '50M',
                        'mimeTypes'        => ['image/*', 'video/*'],
                        'mimeTypesMessage' => 'Please upload a valid image or video file.',
                    ]),
                ],
            ])
            ->add('mediaType', ChoiceType::class, [
                'label'   => 'Type',
                'choices' => PostMedia::getTypeChoices(),
            ])
            ->add('displayOrder', IntegerType::class, [
                'label' => 'Order',
                'data'  => 0,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => PostMedia::class]);
    }
}
<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('post', HiddenType::class, [
                'mapped' => false,   // we resolve the entity manually in the controller
            ])
            ->add('parentComment', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('content', TextareaType::class, [
                'label'       => false,
                'constraints' => [
                    new NotBlank(['message' => 'Comment cannot be empty.']),
                    new Length([
                        'min'        => 1,
                        'max'        => 2000,
                        'minMessage' => 'Comment must be at least {{ limit }} character.',
                        'maxMessage' => 'Comment cannot exceed {{ limit }} characters.',
                    ]),
                ],
                'attr' => ['placeholder' => 'Write a comment…'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => Comment::class,
            'csrf_token_id' => 'comment',
        ]);
    }
}

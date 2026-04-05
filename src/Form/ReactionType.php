<?php

namespace App\Form;

use App\Entity\Reaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['include_post']) {
            $builder->add('post', HiddenType::class, [
                'mapped' => false,  // resolved manually in controller
            ]);
        }

        $builder->add('reactionType', ChoiceType::class, [
            'label'   => 'Reaction',
            'choices' => Reaction::getTypeChoices(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => Reaction::class,
            'include_post'  => true,
            'csrf_token_id' => 'reaction',
        ]);

        $resolver->setAllowedTypes('include_post', 'bool');
    }
}
<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;

class PsychiatristRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Full Name',
                'constraints' => [new NotBlank(['message' => 'Full name is required'])]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [new NotBlank(['message' => 'Email is required'])]
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password',
                'mapped' => false,
                'constraints' => [new NotBlank(['message' => 'Password is required'])]
                // NO password validators - removed Length and Regex
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => 'Phone',
                'required' => false
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Gender',
                'choices' => [
                    'Male' => 'Male',
                    'Female' => 'Female',
                    'Other' => 'Other'
                ],
                'required' => false,
                'placeholder' => 'Select gender'
            ])
            ->add('dateOfBirth', DateType::class, [
                'label' => 'Date of Birth',
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Address',
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('specialization', TextType::class, [
                'label' => 'Specialization',
                'constraints' => [new NotBlank(['message' => 'Specialization is required'])]
            ])
            ->add('licenseNumber', TextType::class, [
                'label' => 'License Number',
                'constraints' => [new NotBlank(['message' => 'License number is required'])]
            ])
            ->add('emergencyContact', TextType::class, [
                'label' => 'Emergency Contact',
                'required' => false,
                'help' => 'Format: Name - Phone number (e.g., John Doe - 555-123-4567)',
                'attr' => ['placeholder' => 'Emergency contact person and phone']
            ])
            ->add('privacyConsent', CheckboxType::class, [
                'label' => 'I agree to the Privacy Policy and Terms of Service',
                'mapped' => false,
                'constraints' => [new IsTrue(['message' => 'You must agree to the terms'])]
            ])
            ->add('professionalConsent', CheckboxType::class, [
                'label' => 'I confirm that my professional credentials are valid and accurate',
                'mapped' => false,
                'constraints' => [new IsTrue(['message' => 'You must confirm your credentials'])]
            ])
            ->add('ageConsent', CheckboxType::class, [
                'label' => 'I confirm that I am 18 years or older',
                'mapped' => false,
                'constraints' => [new IsTrue(['message' => 'You must be 18 years or older'])]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'psychiatrist_registration'
        ]);
    }
}
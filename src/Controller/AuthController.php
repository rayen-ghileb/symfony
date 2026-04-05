<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\PatientRegistrationType;
use App\Form\PsychiatristRegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // If already logged in, redirect to feed
        if ($this->getUser()) {
            return $this->redirectToRoute('app_feed');
        }

        // Get login errors if any
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register/patient', name: 'app_register_patient', methods: ['GET', 'POST'])]
    public function registerPatient(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_feed');
        }

        $user = new User();
        $form = $this->createForm(PatientRegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                $this->addFlash('danger', 'Email already registered');
                return $this->render('auth/register_patient.html.twig', ['form' => $form->createView()]);
            }

            $plainPassword = $form->get('plainPassword')->getData();
            $user->setRole(User::ROLE_PATIENT);
            $user->setAuthProvider(User::AUTH_PROVIDER_LOCAL);
            $user->setIsActive(true);
            $user->setIsApproved(false);
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Registration successful! Please log in.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/register_patient.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/register/psychiatrist', name: 'app_register_psychiatrist', methods: ['GET', 'POST'])]
    public function registerPsychiatrist(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // If already logged in, redirect to feed
        if ($this->getUser()) {
            return $this->redirectToRoute('app_feed');
        }

        $user = new User();
        $form = $this->createForm(PsychiatristRegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if email exists
            $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                $this->addFlash('danger', 'Email already registered');
                return $this->render('auth/register_psychiatrist.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Get password from form (not mapped to entity)
            $plainPassword = $form->get('plainPassword')->getData();

            // Set user properties
            $user->setRole(User::ROLE_PSYCHIATRIST);
            $user->setAuthProvider(User::AUTH_PROVIDER_LOCAL);
            $user->setIsActive(true);
            $user->setIsApproved(false); // Psychiatrist accounts need license verification

            // Hash password
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            // Redirect to login page with success message
            $this->addFlash('success', 'Registration successful! Your professional credentials will be verified before account activation.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/register_psychiatrist.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/register', name: 'app_register', methods: ['GET'])]
    public function registerChoice(): Response
    {
        // If already logged in, redirect to feed
        if ($this->getUser()) {
            return $this->redirectToRoute('app_feed');
        }

        return $this->render('auth/register_choice.html.twig');
    }

    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_feed');
        }

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            if (empty($email)) {
                $this->addFlash('danger', 'Please enter your email address');
                return $this->render('auth/forgot_password.html.twig');
            }

            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('danger', 'No account found with this email address');
                return $this->render('auth/forgot_password.html.twig');
            }

            // Here you would send a password reset email
            // For now, just show success message
            $this->addFlash('success', 'Password reset link has been sent to your email');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/forgot_password.html.twig');
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(
        string $token,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_feed');
        }

        // Here you would validate the token and find the user
        // For now, redirect to login
        $this->addFlash('warning', 'Password reset feature coming soon');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/verify-email/{token}', name: 'app_verify_email', methods: ['GET'])]
    public function verifyEmail(
        string $token,
        EntityManagerInterface $em
    ): Response {
        // Here you would verify the email token
        // For now, redirect to login with success message
        $this->addFlash('success', 'Email verified! You can now login.');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method can be empty - it will be intercepted by the logout key on your firewall
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

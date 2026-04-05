<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users')]
class UserController
{
    #[Route('', methods: ['GET'])]
    public function list(UserRepository $repo): JsonResponse
    {
        return new JsonResponse($repo->findAll());
    }

    #[Route('/{id}', methods: ['GET'])]
    public function get(User $user): JsonResponse
    {
        return new JsonResponse($user);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setFullName($data['fullName']);
        $user->setEmail($data['email']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        $user->setPhoneNumber($data['phoneNumber'] ?? null);
        $user->setAddress($data['address'] ?? null);
        $user->setRole($data['role'] ?? $data['userType'] ?? User::ROLE_PATIENT);
        $user->setIsActive((bool)($data['isActive'] ?? true));
        $user->setIsApproved((bool)($data['isApproved'] ?? true));
        $user->setProfilePicture($data['profilePicture'] ?? null);
        $user->setGender($data['gender'] ?? null);
        $user->setEmergencyContact($data['emergencyContact'] ?? null);
        $user->setSpecialization($data['specialization'] ?? null);
        $user->setLicenseNumber($data['licenseNumber'] ?? null);
        $user->setGoogleId($data['googleId'] ?? null);

        if (!empty($data['dateOfBirth'])) {
            try {
                $user->setDateOfBirth(new \DateTime($data['dateOfBirth']));
            } catch (\Exception $e) {
                return new JsonResponse(['message' => 'Invalid dateOfBirth format. Expected YYYY-MM-DD.'], 400);
            }
        }

        if (!empty($data['authProvider'])) {
            $user->setAuthProvider($data['authProvider']);
        } elseif (!empty($data['googleId'])) {
            $user->setAuthProvider(User::AUTH_PROVIDER_GOOGLE);
        } else {
            $user->setAuthProvider(User::AUTH_PROVIDER_LOCAL);
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse($user, 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(User $user, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user->setFullName($data['fullName']);
        $user->setEmail($data['email']);
        $user->setPhoneNumber($data['phoneNumber'] ?? null);
        $user->setAddress($data['address'] ?? null);
        $user->setRole($data['role'] ?? $data['userType'] ?? User::ROLE_PATIENT);

        if (array_key_exists('isActive', $data)) {
            $user->setIsActive((bool)$data['isActive']);
        }
        if (array_key_exists('isApproved', $data)) {
            $user->setIsApproved((bool)$data['isApproved']);
        }
        if (array_key_exists('profilePicture', $data)) {
            $user->setProfilePicture($data['profilePicture']);
        }
        if (array_key_exists('gender', $data)) {
            $user->setGender($data['gender']);
        }
        if (array_key_exists('emergencyContact', $data)) {
            $user->setEmergencyContact($data['emergencyContact']);
        }
        if (array_key_exists('specialization', $data)) {
            $user->setSpecialization($data['specialization']);
        }
        if (array_key_exists('licenseNumber', $data)) {
            $user->setLicenseNumber($data['licenseNumber']);
        }
        if (array_key_exists('googleId', $data)) {
            $user->setGoogleId($data['googleId']);
        }
        if (array_key_exists('authProvider', $data)) {
            $user->setAuthProvider($data['authProvider']);
        }
        if (array_key_exists('dateOfBirth', $data)) {
            if (empty($data['dateOfBirth'])) {
                $user->setDateOfBirth(null);
            } else {
                try {
                    $user->setDateOfBirth(new \DateTime($data['dateOfBirth']));
                } catch (\Exception $e) {
                    return new JsonResponse(['message' => 'Invalid dateOfBirth format. Expected YYYY-MM-DD.'], 400);
                }
            }
        }

        $em->flush();

        return new JsonResponse($user);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'User deleted']);
    }
}
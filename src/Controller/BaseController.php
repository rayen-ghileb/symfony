<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    protected function getCurrentUser(): ?User
    {
        $user = $this->getUser();
        return $user instanceof User ? $user : null;
    }
}
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // si l'utilisateur est déjà connecté, on le redirige
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // obtenir l'erreur de login s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // dernier email utilisé
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(AuthenticationUtils $authenticationUtils): Response
    {
        // si l'utilisateur est déjà connecté, on le redirige
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // obtenir l'erreur de login s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // dernier email utilisé
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }


    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Ce code ne sera jamais exécuté : Symfony intercepte cette route automatiquement
        throw new \Exception('Ne pas oublier d’activer le logout dans security.yaml');
    }
}
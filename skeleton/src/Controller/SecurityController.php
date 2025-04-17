<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use App\Form\RegistrationFormType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

final class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
public function login(AuthenticationUtils $authenticationUtils): Response
{
    if ($this->getUser()) {
        return $this->redirectToRoute('app_home');
    }

    // Récupère l'erreur de connexion s'il y en a une
    $error = $authenticationUtils->getLastAuthenticationError();
    
    // Récupère le dernier nom d'utilisateur saisi
    $lastUsername = $authenticationUtils->getLastUsername();

    return $this->render('security/login.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error, // Peut être null
        'registrationForm' => $this->createForm(RegistrationFormType::class)->createView(),
    ]);
}


    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPlainPassword()));
            $user->setRoles(['ROLE_USER']); // Set default role
            $entityManager->persist($user);
            $entityManager->flush();

            // Send confirmation email
            $email = (new TemplatedEmail())
                ->from('no-reply@yourdomain.com')
                ->to($user->getEmail())
                ->subject('Confirm your email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
                ->context([
                    'user' => $user,
                ]);

            $mailer->send($email);

            // Add a success message or redirect to login page
            $this->addFlash('success', 'Your account has been created successfully. Please check your email to confirm your account.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/login.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/confirm/{token}', name: 'app_confirm_email')]
    public function confirmEmail(string $token, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['confirmationToken' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('The confirmation token is invalid.');
        }

        $user->setIsVerified(true);
        $user->setConfirmationToken(null);
        $entityManager->flush();

        // Add a success message or redirect to login page
        $this->addFlash('success', 'Your email has been confirmed successfully. You can now log in.');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Ce code ne sera jamais exécuté : Symfony intercepte cette route automatiquement
        throw new \Exception('Ne pas oublier d’activer le logout dans security.yaml');
    }
}
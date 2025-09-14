<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use App\Repository\PanierRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CartSubscriber implements EventSubscriberInterface
{
    private Environment $twig;
    private PanierRepository $panierRepository;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        Environment $twig,
        PanierRepository $panierRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->twig = $twig;
        $this->panierRepository = $panierRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $token = $this->tokenStorage->getToken();
        $user = $token ? $token->getUser() : null;

        $panier = null;
        $count = 0;

        if ($user && is_object($user)) {
            $panier = $this->panierRepository->findOneBy(['user' => $user]);

            if ($panier) {
                $count = count($panier->getProduits());
            }
        }

        // Injecte les infos globales dans Twig
        $this->twig->addGlobal('panier', [
            'entity' => $panier,
            'count'  => $count,
        ]);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}

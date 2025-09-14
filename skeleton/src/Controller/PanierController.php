<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\PanierProduit;
use App\Entity\Produit;
use App\Repository\CategorieRepository;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PanierController extends AbstractController
{

    public function __construct(private CategorieRepository $categorieRepository,
    private ProduitRepository $produitRepository)
    {
        
    }

    #[Route('/panier/add/{id}', name: 'app_panier_add', methods: ['POST'])]
    public function add(
        Produit $produit,
        Request $request,
        EntityManagerInterface $em,
        PanierRepository $panierRepository,
        TokenStorageInterface $tokenStorage
    ): Response {
        $user = $tokenStorage->getToken()?->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // 🔹 Chercher un panier existant pour cet utilisateur
        $panier = $panierRepository->findOneBy(['user' => $user]);
        if (!$panier) {
            $panier = new Panier();
            $panier->setUser($user);
            $em->persist($panier);
            $em->flush();
        }

        // 🔹 Quantité envoyée par le formulaire
        $quantite = (int) $request->request->get('quantite', 1);

        // 🔹 Vérifier si le produit est déjà dans le panier
        $panierProduit = $em->getRepository(PanierProduit::class)->findOneBy([
            'panier' => $panier,
            'produit' => $produit,
        ]);

        if ($panierProduit) {
            $panierProduit->setQuantite($panierProduit->getQuantite() + $quantite);
        } else {
            $panierProduit = new PanierProduit();
            $panierProduit->setPanier($panier);
            $panierProduit->setProduit($produit);
            $panierProduit->setQuantite($quantite);
            $panierProduit->setPrix($produit->getPrix());
            $em->persist($panierProduit);
        }

        $em->flush();

        $this->addFlash('success', 'Produit ajouté au panier');

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/remove/{id}', name: 'app_panier_remove')]
    public function remove(PanierProduit $panierProduit, EntityManagerInterface $em)
    {
        // On supprime le produit du panier
        $em->remove($panierProduit);
        $em->flush();

        $this->addFlash('success', 'Produit retiré du panier');

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/update/{id}', name: 'app_panier_update', methods: ['POST'])]
    public function update(
        PanierProduit $panierProduit,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (isset($data['quantite'])) {
            $quantite = max(1, (int) $data['quantite']); 
            $panierProduit->setQuantite($quantite);
            $em->flush();
        }

        // recalculer le total du panier de l’utilisateur connecté
        $panier = $panierProduit->getPanier();

        $total = 0;
        foreach ($panier->getProduits() as $pp) {
            $total += $pp->getQuantite() * $pp->getProduit()->getPrix();
        }

        $count = 0;
        foreach ($panier->getProduits() as $panierProduit) {
            $count += $panierProduit->getQuantite();
        }

        return new JsonResponse([
            'quantite'   => $panierProduit->getQuantite(),
            'ligneTotal' => $panierProduit->getQuantite() * $panierProduit->getProduit()->getPrix(),
            'total'      => $total,
            'count'      => $count,
        ]);
    }

    #[Route('/panier', name: 'app_panier')]
    public function panier(): Response
    {
        $categorieRepository = $this->categorieRepository->findAll();
        $produitRepository = $this->produitRepository->findLatestProducts();

        // Simuler des options de livraison UPS
        $deliveryOptions = [
            [
                'code' => 'standard',
                'description' => 'UPS Standard (2-5 jours)',
                'price' => 10,
            ],
            [
                'code' => 'express',
                'description' => 'UPS Express (1-2 jours)',
                'price' => 20,
            ],
            [
                'code' => 'pickup',
                'description' => 'UPS Point Relais',
                'price' => 8,
            ],
        ];

        return $this->render('home/panier.html.twig', [
            'controller_name' => 'HomeController',
            'produitList' => $produitRepository,
            'categorieList' => $categorieRepository,
            'deliveryOptions' => $deliveryOptions, 
        ]);
    }



}

<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{

    public function __construct(
        private CategorieRepository $categorieRepository, 
        private ProduitRepository $produitRepository,
        private EntityManagerInterface $entityManager
    )
    {
        
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {

        $categorieList = $this->categorieRepository->findAll();
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'categorie_list' => $categorieList
        ]);


        
    }
}

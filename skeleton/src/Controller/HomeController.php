<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Produit;

final class HomeController extends AbstractController
{
    public function __Construct(private CategorieRepository $categorieRepository,
    private ProduitRepository $produitRepository,
    private PaginatorInterface $paginator,
    private EntityManagerInterface $entityManager ){

    }


    #[Route('/', name: 'app_home')]
    public function index(): Response
    {

       $categorieRepository = $this->categorieRepository->findAll();
       $produitRepository = $this->produitRepository->findLatestProducts();


        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'produitList' => $produitRepository,
            'categorieList' => $categorieRepository
        ]);
    }

    #[Route('/shop-details', name: 'app_shop_details')]
    public function shopDetails(Request $request): Response
    {

        $categoryId = $request->query->get('category');
        $categories = $this->categorieRepository->findAll();

        if ($categoryId) {
            $produits = $this->produitRepository->findByCategoryId($categoryId);
        } else {
            $produits = $this->produitRepository->findAll();
        }

        
        $pagination = $this->paginator->paginate(
            $produits,
            $request->query->getInt('page', 1),
            12
        );


        return $this->render('home/shopDetails.html.twig', [
            'controller_name' => 'HomeController',
            'produitList' => $pagination,
            'categorieList' => $categories
        ]);
    }

    #[Route('/search', name: 'app_search')]
    public function search(Request $request): Response
    {
        $query = $request->query->get('query');
        $categories = $this->categorieRepository->findAll();
        $produits = $this->produitRepository->searchProducts($query);

        $pagination = $this->paginator->paginate(
            $produits,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('home/shopDetails.html.twig', [
            'produitList' => $pagination,
            'categorieList' => $categories
        ]);
    }

    
    #[Route('/panier', name: 'app_panier')]
    public function panierr(): Response
    {

       $categorieRepository = $this->categorieRepository->findAll();
       $produitRepository = $this->produitRepository->findLatestProducts();


        return $this->render('home/panier.html.twig', [
            'controller_name' => 'HomeController',
            'produitList' => $produitRepository,
            'categorieList' => $categorieRepository
        ]);
    }

    #[Route("/get-produit-details/{id}", name: 'get_produit_details', methods:"GET")]
    public function getProduitDetails(Produit $produit): Response
    {
        // Récupérer les détails du produit à partir de son ID
        $produit = $this->entityManager->getRepository(Produit::class)->find($produit->getId());

        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        return $this->json([
            'name' => $produit->getName(),
            'image' => $produit->getThumbnail(),
            'prix' => $produit->getPrix(),
            'description' => $produit->getDescription(),
        ]);
    }

}

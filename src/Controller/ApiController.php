<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Produit;
use App\Utils\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }
    #[Route('/api/produits', name: 'app_api_produits')]
    public function getProduits(Request $request, ProduitRepository $produitRepository): Response
    {
        $response = new Utils();
        $produits = $produitRepository->findAll();
        return $response->GetJsonResponse($request,$produits);
        
    }

    #[Route('/api/product/add', name: 'app_api_products_add')]
    public function AddProduits(Request $request, EntityManagerInterface $entityManager ) : JsonResponse
    {

        $data = json_decode($request->getContent(), true);
       
       $produit = new Produit();
       $produit->setLibelle($data['libelle']);
       $produit->setDescription($data['description']);
       $produit->setPrixPlancher($data['prixPlancher']);

       $entityManager->persist($produit);
       $entityManager->flush();
       return $this->json(['message' => 'Produit créé avec succès.'], Response::HTTP_CREATED);
    }

}

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
    // Route pour récupérer toutes les enchères
    #[Route('/api/encheres', name: 'api_encheres', methods: ['GET'])]
    public function getEncheres(EnchereRepository $enchereRepository): JsonResponse
    {
        $encheres = $enchereRepository->findAll();
        return $this->json($encheres, 200, [], ['groups' => 'enchere:read']);
    }

    // Route pour ajouter une enchère
    #[Route('/api/encheres/add', name: 'api_encheres_add', methods: ['POST'])]
    public function addEnchere(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        // Création de l'entité Enchère
        $enchere = new Enchere();
        $enchere->setTitre($data['titre']);
        $enchere->setDescription($data['description']);
        $enchere->setDateHeureDebut(new \DateTime($data['dateHeureDebut']));
        $enchere->setDateHeureFin(new \DateTime($data['dateHeureFin']));
        $enchere->setPrixDebut($data['prixDebut']);
        $enchere->setStatut('Active'); // Exemple de statut par défaut

        // Sauvegarde en base de données
        $entityManager->persist($enchere);
        $entityManager->flush();

        return $this->json(['message' => 'Enchère ajoutée avec succès'], 201);
    }
}

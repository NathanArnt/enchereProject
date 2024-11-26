<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Produit;
use App\Entity\Enchere;
use App\Repository\EnchereRepository;
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

    #[Route('/api/produits', name: 'app_api_produits', methods: ['GET'])]
    public function getProduits(Request $request, ProduitRepository $produitRepository): Response
    {
        $response = new Utils();
        $produits = $produitRepository->findAll();
        return $response->GetJsonResponse($request, $produits);
    }

    #[Route('/api/produits/add', name: 'app_api_add_produit', methods: ['POST'])]
    public function addProduit(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $produit = new Produit();
        $produit->setLibelle($data['libelle']);
        $produit->setDescription($data['description']);
        $produit->setPrixPlancher($data['prixPlancher']);

        $entityManager->persist($produit);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Produit ajouté avec succès'], Response::HTTP_CREATED);
    }

    #[Route('/api/produits/update/{id}', name: 'app_api_update_produit', methods: ['PUT'])]
    public function updateProduit(Request $request, $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $produit = $entityManager->getRepository(Produit::class)->find($id);

        if (!$produit) {
            return new JsonResponse(['status' => 'Produit non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $produit->setLibelle($data['libelle']);
        $produit->setDescription($data['description']);
        $produit->setPrixPlancher($data['prixPlancher']);

        $entityManager->flush();

        return new JsonResponse(['status' => 'Produit mis à jour avec succès']);
    }

    #[Route('/api/produits/delete/{id}', name: 'app_api_delete_produit', methods: ['DELETE'])]
    public function deleteProduit($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $produit = $entityManager->getRepository(Produit::class)->find($id);

        if (!$produit) {
            return new JsonResponse(['status' => 'Produit non trouvé'], 404);
        }

        $entityManager->remove($produit);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Produit supprimé avec succès']);
    }

    #[Route('/api/encheres', name: 'api_encheres', methods: ['GET'])]
    public function getEncheres(EnchereRepository $enchereRepository): JsonResponse
    {
        $encheres = $enchereRepository->findAll();

        // Transformez les enchères en tableau adapté pour l'API
        $data = array_map(function ($enchere) {
            return [
                'id' => $enchere->getId(),
                'titre' => $enchere->getTitre(),
                'dateHeureDebut' => $enchere->getDateHeureDebut()->format('Y-m-d H:i:s'),
                'dateHeureFin' => $enchere->getDateHeureFin()->format('Y-m-d H:i:s'),
                'prixDebut' => $enchere->getPrixDebut(),
                'statut' => $enchere->getStatut(),
                'produitLibelle' => $enchere->getLeProduit() ? $enchere->getLeProduit()->getLibelle() : null,
            ];
        }, $encheres);

        return new JsonResponse($data);
    }

    #[Route('/api/encheres/add', name: 'app_api_add_enchere', methods: ['POST'])]
    public function addEnchere(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $enchere = new Enchere();
        $enchere->setTitre($data['titre']);
        // Utilisez le fuseau horaire par défaut du serveur ou un spécifique comme 'Europe/Paris'
        $dateHeureDebut = new \DateTime($data['dateHeureDebut'], new \DateTimeZone('UTC'));
        $dateHeureFin = new \DateTime($data['dateHeureFin'], new \DateTimeZone('UTC'));
        $enchere->setDateHeureDebut($dateHeureDebut);
        $enchere->setDateHeureFin($dateHeureFin);
        $enchere->setPrixDebut($data['prixDebut']);
        $enchere->setStatut($this->calculateStatus($dateHeureDebut, $dateHeureFin));

        $produit = $entityManager->getRepository(Produit::class)->find($data['produitId']);
        if ($produit) {
            $enchere->setLeProduit($produit);
        }

        $entityManager->persist($enchere);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Enchère ajoutée avec succès'], Response::HTTP_CREATED);
    }

    #[Route('/api/encheres/update/{id}', name: 'app_api_update_enchere', methods: ['PUT'])]
    public function updateEnchere(Request $request, $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $enchere = $entityManager->getRepository(Enchere::class)->find($id);

        if (!$enchere) {
            return new JsonResponse(['status' => 'Enchère non trouvée'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $enchere->setTitre($data['titre']);
        $dateHeureDebut = new \DateTime($data['dateHeureDebut'], new \DateTimeZone('UTC'));
        $dateHeureFin = new \DateTime($data['dateHeureFin'], new \DateTimeZone('UTC'));
        $enchere->setDateHeureDebut($dateHeureDebut);
        $enchere->setDateHeureFin($dateHeureFin);
        $enchere->setPrixDebut($data['prixDebut']);
        $enchere->setStatut($this->calculateStatus($dateHeureDebut, $dateHeureFin));

        $produit = $entityManager->getRepository(Produit::class)->find($data['produitId']);
        if ($produit) {
            $enchere->setLeProduit($produit);
        }

        $entityManager->flush();

        return new JsonResponse(['status' => 'Enchère mise à jour avec succès']);
    }
    
    #[Route('/api/encheres/delete/{id}', name: 'app_api_delete_enchere', methods: ['DELETE'])]
    public function deleteEnchere($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $enchere = $entityManager->getRepository(Enchere::class)->find($id);

        if (!$enchere) {
            return new JsonResponse(['status' => 'Enchère non trouvée'], 404);
        }

        $entityManager->remove($enchere);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Enchère supprimée avec succès']);
    }
    #[Route('/api/encheresuser', name: 'app_api_encheres_user')]
    public function getEncheresUser(Request $request, EnchereRepository $enchereRepository): Response
    {
        $response =new Utils();
        $encheres = $enchereRepository->findAll();
        return $response->GetJsonResponse($request,$encheres);
    }   


    // Ajoutez cette méthode privée pour calculer le statut de l'enchère 
    private function calculateStatus(\DateTime $dateHeureDebut, \DateTime $dateHeureFin): string 
    { 
        $now = new \DateTime(); 
        if ($now < $dateHeureDebut) 
        { 
            return 'incoming'; 
        } 
        elseif ($now > $dateHeureFin) 
        { 
            return 'over'; 
        } 
        else 
        { 
            return 'live'; 
        }
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Produit;
use App\Entity\Enchere;
use App\Entity\Participation;
use App\Repository\EnchereRepository;
use App\Utils\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController extends AbstractController
{
    // Route principale de l'API
    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }

    // Récupère la liste des produits
    #[Route('/api/produits', name: 'app_api_produits', methods: ['GET'])]
    public function getProduits(Request $request, ProduitRepository $produitRepository): Response
    {
        $response = new Utils();
        // Récupère tous les produits de la base de données
        $produits = $produitRepository->findAll();
        // Renvoie les produits sous forme de réponse JSON
        return $response->GetJsonResponse($request, $produits);
    }

    // Ajoute un nouveau produit
    #[Route('/api/produits/add', name: 'app_api_add_produit', methods: ['POST'])]
    public function addProduit(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $produit = new Produit();
        // Définit les propriétés du produit avec les données reçues
        $produit->setLibelle($data['libelle']);
        $produit->setDescription($data['description']);
        $produit->setPrixPlancher($data['prixPlancher']);

        // Persist et sauvegarde le produit dans la base de données
        $entityManager->persist($produit);
        $entityManager->flush();

        // Renvoie une réponse JSON indiquant que le produit a été ajouté avec succès
        return new JsonResponse(['status' => 'Produit ajouté avec succès'], Response::HTTP_CREATED);
    }

    // Met à jour un produit existant
    #[Route('/api/produits/update/{id}', name: 'app_api_update_produit', methods: ['PUT'])]
    public function updateProduit(Request $request, $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $produit = $entityManager->getRepository(Produit::class)->find($id);

        if (!$produit) {
            return new JsonResponse(['status' => 'Produit non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        // Met à jour les propriétés du produit avec les nouvelles données
        $produit->setLibelle($data['libelle']);
        $produit->setDescription($data['description']);
        $produit->setPrixPlancher($data['prixPlancher']);

        // Sauvegarde les modifications dans la base de données
        $entityManager->flush();

        // Renvoie une réponse JSON indiquant que le produit a été mis à jour avec succès
        return new JsonResponse(['status' => 'Produit mis à jour avec succès']);
    }

    // Supprime un produit
    #[Route('/api/produits/delete/{id}', name: 'app_api_delete_produit', methods: ['DELETE'])]
    public function deleteProduit($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $produit = $entityManager->getRepository(Produit::class)->find($id);

        if (!$produit) {
            return new JsonResponse(['status' => 'Produit non trouvé'], 404);
        }

        // Supprime le produit de la base de données
        $entityManager->remove($produit);
        $entityManager->flush();

        // Renvoie une réponse JSON indiquant que le produit a été supprimé avec succès
        return new JsonResponse(['status' => 'Produit supprimé avec succès']);
    }

    // Récupère la liste des enchères
    #[Route('/api/encheres', name: 'api_encheres', methods: ['GET'])]
    public function getEncheres(EnchereRepository $enchereRepository): JsonResponse
    {
        $encheres = $enchereRepository->findAll();

        // Transforme les enchères en tableau adapté pour l'API
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

        // Renvoie les enchères sous forme de réponse JSON
        return new JsonResponse($data);
    }
    // Ajoute une nouvelle enchère
    #[Route('/api/encheres/add', name: 'app_api_add_enchere', methods: ['POST'])]
    public function addEnchere(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $enchere = new Enchere();
        $enchere->setTitre($data['titre']);
        // Conversion explicite en UTC+1
        $dateDebut = new \DateTime($data['dateHeureDebut'], new \DateTimeZone('UTC'));
        $dateDebut->setTimezone(new \DateTimeZone('Europe/Paris'));
        $enchere->setDateHeureDebut($dateDebut);

        $dateFin = new \DateTime($data['dateHeureFin'], new \DateTimeZone('UTC'));
        $dateFin->setTimezone(new \DateTimeZone('Europe/Paris'));
        $enchere->setDateHeureFin($dateFin);        
        $enchere->setPrixDebut($data['prixDebut']);
        // Calcule et définit le statut de l'enchère
        $enchere->setStatut($this->calculateStatus($dateDebut, $dateFin));

        // Récupère le produit associé à l'enchère
        $produit = $entityManager->getRepository(Produit::class)->find($data['produitId']);
        if ($produit) {
            $enchere->setLeProduit($produit);
        }

        // Persist et sauvegarde l'enchère dans la base de données
        $entityManager->persist($enchere);
        $entityManager->flush();

        // Renvoie une réponse JSON indiquant que l'enchère a été ajoutée avec succès
        return new JsonResponse(['status' => 'Enchère ajoutée avec succès'], Response::HTTP_CREATED);
    }

        #[Route('/api/encheres/update/{id}', name: 'app_api_update_enchere', methods: ['PUT'])]
        public function updateEnchere(Request $request, $id, EntityManagerInterface $entityManager): JsonResponse
        {
            // Récupère l'enchère à partir de la base de données
            $enchere = $entityManager->getRepository(Enchere::class)->find($id);
    
            if (!$enchere) {
                // Si l'enchère n'est pas trouvée, renvoie une réponse JSON avec un message d'erreur
                return new JsonResponse(['status' => 'Enchère non trouvée'], 404);
            }
    
            // Récupère les nouvelles données de la requête
            $data = json_decode($request->getContent(), true);
            // Met à jour les propriétés de l'enchère avec les nouvelles données
            $enchere->setTitre($data['titre']);
            $dateDebut = new \DateTime($data['dateHeureDebut'], new \DateTimeZone('UTC'));
            $dateDebut->setTimezone(new \DateTimeZone('Europe/Paris'));
            $enchere->setDateHeureDebut($dateDebut);
    
            $dateFin = new \DateTime($data['dateHeureFin'], new \DateTimeZone('UTC'));
            $dateFin->setTimezone(new \DateTimeZone('Europe/Paris'));
            $enchere->setDateHeureFin($dateFin);        
            $enchere->setPrixDebut($data['prixDebut']);
            // Calcule et met à jour le statut de l'enchère
            $enchere->setStatut($this->calculateStatus($dateDebut, $dateFin));
    
            // Récupère le produit associé à l'enchère
            $produit = $entityManager->getRepository(Produit::class)->find($data['produitId']);
            if ($produit) {
                $enchere->setLeProduit($produit);
            }
    
            // Sauvegarde les modifications dans la base de données
            $entityManager->flush();
    
            // Renvoie une réponse JSON indiquant que l'enchère a été mise à jour avec succès
            return new JsonResponse(['status' => 'Enchère mise à jour avec succès']);
        }
    
        // Met à jour le statut d'une enchère
        #[Route('/api/encheres/update-status/{id}', name: 'app_api_update_enchere_status', methods: ['PUT'])]
        public function updateEnchereStatus(Request $request, $id, EntityManagerInterface $entityManager): JsonResponse
        {
            // Récupère l'enchère à partir de la base de données
            $enchere = $entityManager->getRepository(Enchere::class)->find($id);
    
            if (!$enchere) {
                // Si l'enchère n'est pas trouvée, renvoie une réponse JSON avec un message d'erreur
                return new JsonResponse(['status' => 'Enchère non trouvée'], 404);
            }
    
            // Récupère le nouveau statut de la requête
            $data = json_decode($request->getContent(), true);
            $enchere->setStatut($data['statut']);
    
            // Sauvegarde les modifications dans la base de données
            $entityManager->flush();
    
            // Renvoie une réponse JSON indiquant que le statut de l'enchère a été mis à jour avec succès
            return new JsonResponse(['status' => 'Statut de l\'enchère mis à jour avec succès']);
        }
    
        // Supprime une enchère
        #[Route('/api/encheres/delete/{id}', name: 'app_api_delete_enchere', methods: ['DELETE'])]
        public function deleteEnchere($id, EntityManagerInterface $entityManager): JsonResponse
        {
            // Récupère l'enchère à partir de la base de données
            $enchere = $entityManager->getRepository(Enchere::class)->find($id);
    
            if (!$enchere) {
                // Si l'enchère n'est pas trouvée, renvoie une réponse JSON avec un message d'erreur
                return new JsonResponse(['status' => 'Enchère non trouvée'], 404);
            }
    
            // Supprime l'enchère de la base de données
            $entityManager->remove($enchere);
            $entityManager->flush();
    
            // Renvoie une réponse JSON indiquant que l'enchère a été supprimée avec succès
            return new JsonResponse(['status' => 'Enchère supprimée avec succès']);
        }
    
        // Récupère les enchères pour un utilisateur spécifique
        #[Route('/api/encheresuser', name: 'app_api_encheres_user')]
        public function getEncheresUser(Request $request, EnchereRepository $enchereRepository): Response
        {
            $response = new Utils();
            // Récupère toutes les enchères de la base de données
            $encheres = $enchereRepository->findAll();
            // Renvoie les enchères sous forme de réponse JSON
            return $response->GetJsonResponse($request, $encheres);
        }
    
        // Méthode privée pour calculer le statut de l'enchère
        private function calculateStatus(\DateTime $dateHeureDebut, \DateTime $dateHeureFin): string 
        {
            $now = new \DateTime();
            if ($now < $dateHeureDebut) {
                return 'incoming'; 
            } elseif ($now > $dateHeureFin) { 
                return 'over';
            } else {
                return 'live';
            }
        }
    
    #[Route('/api/participation/budgetmax/add', name: 'app_api_add_participation_budget_max', methods: ['POST', 'GET'])]
    public function addBudgetMax(
    EnchereRepository $enchereRepository,
    Request $request,
    EntityManagerInterface $entityManager
): JsonResponse {
    $util = new Utils();

    $data = json_decode($request->getContent(), true);

    // Récupération de l'utilisateur actuel
    $user = $this->getUser();

    // Création d'une nouvelle participation
    $participation = new Participation();
    $participation->setLeUser($user);

    // Association avec l'enchère correspondante
    $laEnchere = $enchereRepository->find($data['laEnchere']);
    if (!$laEnchere) {
        return new JsonResponse(['error' => 'Enchère non trouvée'], Response::HTTP_NOT_FOUND);
    }
    $participation->setLaEnchere($laEnchere);

    // Définition du budget maximum
    $participation->setBudgetMaximum($data['budgetMaximum']);

    // Vérification et définition du prix encheri
    $prixEncheri = isset($data['prixEncheri']) ? $data['prixEncheri'] : 0;
    $participation->setPrixEncheri($prixEncheri);

    // Persistance de la participation
    $entityManager->persist($participation);
    $entityManager->flush();

    // Retourner une réponse JSON avec l'ID et d'autres informations
    return new JsonResponse([
        'id' => $participation->getId(),
        'budgetMaximum' => $participation->getBudgetMaximum(),
        'prixEncheri' => $participation->getPrixEncheri(),
        'laEnchere' => $participation->getLaEnchere()->getId(), // Optionnel, pour confirmation
        'leUser' => $participation->getLeUser()->getId() // Optionnel, pour confirmation
    ], Response::HTTP_CREATED);
}

#[Route('/api/participation/update/{id}', name: 'app_api_update_participation', methods: ['PUT'])]
public function updatePrixEncheri($id, Request $request, EntityManagerInterface $entityManager)
{
    $participation = $entityManager->getRepository(Participation::class)->find($id);
    if (!$participation) {
        return new JsonResponse(['error' => 'Participation non trouvée'], Response::HTTP_NOT_FOUND);
    }

    $enchere = $participation->getLaEnchere();
    if (!$enchere) {
        return new JsonResponse(['error' => 'Enchère associée non trouvée'], Response::HTTP_NOT_FOUND);
    }

    $data = json_decode($request->getContent(), true);
    if (!isset($data['prixEncheri']) || !is_numeric($data['prixEncheri'])) {
        return new JsonResponse(['error' => 'Le champ "prixEncheri" doit être un nombre valide'], Response::HTTP_BAD_REQUEST);
    }

    $prixEncheri = (float) $data['prixEncheri'];

    if ($prixEncheri < $enchere->getPrixDebut()) {
        return new JsonResponse(['error' => 'Le prix enchéri ne peut pas être inférieur au prix de départ'], Response::HTTP_BAD_REQUEST);
    }

    $budgetMaximum = $participation->getBudgetMaximum();
    if ($budgetMaximum && $prixEncheri > $budgetMaximum) {
        return new JsonResponse(['error' => 'Le prix enchéri dépasse le budget maximum autorisé'], Response::HTTP_BAD_REQUEST);
    }

    $participation->setPrixEncheri($prixEncheri);
    $enchere->setPrixDebut($prixEncheri);
    $entityManager->flush();

    return new JsonResponse(['status' => 'Prix enchéri mis à jour avec succès'], Response::HTTP_OK);
}


}

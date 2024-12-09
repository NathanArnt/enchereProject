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
use App\Repository\ParticipationRepository;
use App\Repository\UserRepository;
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
    #[Route('/api/participation/budgetmax/add', name: 'app_api_add_participation_budget_max', methods: ['POST'])]
    public function addBudgetMax(
        EnchereRepository $enchereRepository,
        ParticipationRepository $participationRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();
    
        // Vérification des données reçues
        if (!isset($data['laEnchere']) || !isset($data['budgetMaximum'])) {
            return new JsonResponse(['error' => 'Les champs "laEnchere" et "budgetMaximum" sont requis'], Response::HTTP_BAD_REQUEST);
        }
    
        // Association avec l'enchère correspondante
        $laEnchere = $enchereRepository->find($data['laEnchere']);
        if (!$laEnchere) {
            return new JsonResponse(['error' => 'Enchère non trouvée'], Response::HTTP_NOT_FOUND);
        }
    
        // Vérification si une participation existe déjà
        $existingParticipation = $participationRepository->findOneBy(['leUser' => $user, 'laEnchere' => $laEnchere]);
        if ($existingParticipation) {
            return new JsonResponse([
                'error' => 'Un budget maximum a déjà été défini pour cette enchère'
            ], Response::HTTP_CONFLICT);
        }
    
        // Création de la participation
        $budgetMaximum = (float) $data['budgetMaximum'];
        if ($budgetMaximum < $laEnchere->getPrixDebut()) {
            return new JsonResponse(['error' => 'Le budget maximum ne peut pas être inférieur au prix de départ'], Response::HTTP_BAD_REQUEST);
        }
    
        $participation = new Participation();
        $participation->setLeUser($user);
        $participation->setLaEnchere($laEnchere);
        $participation->setBudgetMaximum($budgetMaximum);
        $participation->setPrixEncheri(0);
    
        $entityManager->persist($participation);
        $entityManager->flush();
    
        return new JsonResponse([
            'id' => $participation->getId(),
            'budgetMaximum' => $participation->getBudgetMaximum(),
            'prixEncheri' => $participation->getPrixEncheri()
        ], Response::HTTP_CREATED);
    }
    

    #[Route('/api/participation/{enchereId}', name: 'app_api_get_participation', methods: ['GET'])]
    public function getParticipation(
        $enchereId,
        EnchereRepository $enchereRepository,
        ParticipationRepository $participationRepository
    ): JsonResponse {
        $user = $this->getUser();
    
        $laEnchere = $enchereRepository->find($enchereId);
        if (!$laEnchere) {
            return new JsonResponse(['error' => 'Enchère non trouvée'], Response::HTTP_NOT_FOUND);
        }
    
        $participation = $participationRepository->findOneBy(['leUser' => $user, 'laEnchere' => $laEnchere]);
        if (!$participation) {
            return new JsonResponse(['error' => 'Participation non trouvée'], Response::HTTP_NOT_FOUND);
        }
    
        return new JsonResponse([
            'id' => $participation->getId(),
            'budgetMaximum' => $participation->getBudgetMaximum(),
            'prixEncheri' => $participation->getPrixEncheri()
        ], Response::HTTP_OK);
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

// Détermine le gagnant d'une enchère via la dernière participation
// Détermine le gagnant d'une enchère via la dernière participation
// Détermine le gagnant d'une enchère via la dernière participation
#[Route('/api/encheres/winner/{id}', name: 'app_api_terminer_enchere', methods: ['POST', 'GET'])]
public function winnerId(Enchere $enchere, ParticipationRepository $participationRepository, EntityManagerInterface $em): JsonResponse
{
    // Vérifie si l'enchère est terminée
    if ($enchere->getStatut() !== 'over') {
        return new JsonResponse(['error' => 'L\'enchère n\'est pas encore terminée'], Response::HTTP_BAD_REQUEST);
    }

    // Vérifie si l'enchère est concluante (si le prix de l'enchère dépasse le prix plancher)
    if ($enchere->getPrixDebut() < $enchere->getLeProduit()->getPrixPlancher()) {
        return new JsonResponse(['error' => 'L\'enchère n\'a pas atteint le prix plancher'], Response::HTTP_BAD_REQUEST);
    }

    // Trouve la dernière participation pour cette enchère
    $participations = $participationRepository->findBy(
        ['laEnchere' => $enchere], 
        ['id' => 'DESC'] // Trie par ID décroissant pour obtenir la dernière participation
    );

    if (empty($participations)) {
        return new JsonResponse(['message' => 'Aucune participation pour cette enchère'], Response::HTTP_OK);
    }

    // Récupère la dernière participation (la première dans le tableau trié)
    $lastParticipation = $participations[0];

    // Vérifie si le prix enchéri de la dernière participation est supérieur au prix de départ
    if ($lastParticipation->getPrixEncheri() < $enchere->getPrixDebut()) {
        return new JsonResponse(['error' => 'Le prix enchéri de la dernière participation est inférieur au prix de départ'], Response::HTTP_BAD_REQUEST);
    }

    // Assigner le gagnant à l'attribut gagnantId de l'enchère en utilisant l'ID de l'utilisateur
    $enchere->setGagnantId($lastParticipation->getLeUser()->getId()); // Assigne l'ID du User gagnant

    // Sauvegarder les modifications de l'enchère
    $em->flush(); // Persiste les modifications

    // Renvoie les détails du gagnant (la dernière participation)
    return new JsonResponse([
        'message' => 'Le gagnant a été déterminé et enregistré avec succès.',
        'gagnant' => [
            'id' => $lastParticipation->getLeUser()->getId(),
            'nom' => $lastParticipation->getLeUser()->getNom(),
            'prenom' => $lastParticipation->getLeUser()->getPrenom(),
        ],
        'prixEncheri' => $lastParticipation->getPrixEncheri(),
    ], Response::HTTP_OK);
}






}

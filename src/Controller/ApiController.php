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
use App\Repository\UserRepository;
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

        // Conversion explicite en UTC+1
        $dateDebut = new \DateTime($data['dateHeureDebut'], new \DateTimeZone('UTC'));
        $dateDebut->setTimezone(new \DateTimeZone('Europe/Paris'));
        $enchere->setDateHeureDebut($dateDebut);

        $dateFin = new \DateTime($data['dateHeureFin'], new \DateTimeZone('UTC'));
        $dateFin->setTimezone(new \DateTimeZone('Europe/Paris'));
        $enchere->setDateHeureFin($dateFin);

        $enchere->setPrixDebut($data['prixDebut']);
        $enchere->setStatut($data['statut']);
    
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
        $enchere->setDateHeureDebut(new \DateTime($data['dateHeureDebut']));
        $enchere->setDateHeureFin(new \DateTime($data['dateHeureFin']));
        $enchere->setPrixDebut($data['prixDebut']);
        $enchere->setStatut($data['statut']);
    
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
    // Récupérer la participation
    $participation = $entityManager->getRepository(Participation::class)->find($id);
    if (!$participation) {
        return new JsonResponse(['error' => 'Participation non trouvée'], Response::HTTP_NOT_FOUND);
    }

    // Récupérer l'enchère associée
    $enchere = $participation->getLaEnchere();
    if (!$enchere) {
        return new JsonResponse(['error' => 'Enchère associée non trouvée'], Response::HTTP_NOT_FOUND);
    }

    // Décoder les données JSON reçues
    $data = json_decode($request->getContent(), true);
    if (!isset($data['prixEncheri'])) {
        return new JsonResponse(['error' => 'Le champ "prixEncheri" est manquant'], Response::HTTP_BAD_REQUEST);
    }

    // Valider que le prixEncheri est un nombre
    if (!is_numeric($data['prixEncheri'])) {
        return new JsonResponse(['error' => 'Le "prixEncheri" doit être un nombre'], Response::HTTP_BAD_REQUEST);
    }

    $prixEncheri = (float)$data['prixEncheri'];

    // Validation : le prixEncheri ne peut pas être inférieur au prixDebut
    if ($prixEncheri < $enchere->getPrixDebut()) {
        return new JsonResponse(['error' => 'Le prix enchéri ne peut pas être inférieur au prix de départ'], Response::HTTP_BAD_REQUEST);
    }

    // Mettre à jour le prixEncheri
    $participation->setPrixEncheri($prixEncheri);

    // Mise à jour du prixDebut dans l'enchère associée
    $enchere->setPrixDebut($prixEncheri);
    $entityManager->flush();

    // Répondre avec succès
    return new JsonResponse(['status' => 'Prix enchéri et prixDebut mis à jour avec succès'], Response::HTTP_OK);
}

}

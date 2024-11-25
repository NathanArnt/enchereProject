<?php

namespace App\Controller;

use App\Repository\EnchereRepository;
use App\Utils\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }
    #[Route('/api/encheres', name: 'app_api_encheres')]
    public function getProduits(Request $request, EnchereRepository $enchereRepository): Response
    {
        $response =new Utils();
        $encheres = $enchereRepository->findAll();
        return $response->GetJsonResponse($request,$encheres);
    }
}

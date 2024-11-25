<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        $admin = ["ROLE_ADMIN","ROLE_ETUDIANT","ROLE_USER"];         
        $etudiant = ["ROLE_ETUDIANT","ROLE_USER"];
        $user = [];

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/produits', name: 'app_admin_produits')]
    public function getProduits(): Response
    {

        return $this->render('admin/produit.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShopController extends AbstractController
{
    #[Route('/boutique', name: 'app_shop')]
    public function index(ProductRepository $productRepository): Response
    {
        // Récupère tous les produits de la base de données pour la mini-boutique
        $products = $productRepository->findAll();

        return $this->render('shop/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/produit/{id}', name: 'app_product_show')]
    public function show(Product $product): Response
    {
        // Affiche la page de détail d'un produit spécifique
        return $this->render('shop/show.html.twig', [
            'product' => $product,
        ]);
    }
}
<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CartController extends AbstractController
{
    #[Route('/panier/ajouter/{id}', name: 'app_cart_add')]
    public function add(Product $product, Request $request, SessionInterface $session): Response
    {
        $quantity = max(1, (int)$request->request->get('quantity', 1));
        $cart = $session->get('cart', []);

        if (isset($cart[$product->getId()])) {
            $cart[$product->getId()] += $quantity;
        } else {
            $cart[$product->getId()] = $quantity;
        }

        $session->set('cart', $cart);

        $this->addFlash('success', 'Produit ajouté au panier avec succès !');

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/panier', name: 'app_cart_index')]
    public function index(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', []);
        $cartData = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            if ($product) {
                $cartData[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
                $total += $product->getPrice() * $quantity;
            }
        }

        return $this->render('cart/index.html.twig', [
            'items' => $cartData,
            'total' => $total
        ]);
    }

    #[Route('/panier/supprimer/{id}', name: 'app_cart_remove')]
    public function remove(int $id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            $session->set('cart', $cart);
        }

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/panier/commander', name: 'app_cart_stripe_checkout')]
    #[IsGranted('ROLE_USER', message: 'Vous devez vous connecter pour procéder au paiement.')]
    public function stripeCheckout(SessionInterface $session, ProductRepository $productRepository, UrlGeneratorInterface $router): Response
    {
        $cart = $session->get('cart', []);
        if (empty($cart)) {
            return $this->redirectToRoute('app_cart_index');
        }

        \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY'] ?? 'votre_cle_secrete_par_defaut');

        $lineItems = [];

        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            if ($product) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $product->getName(),
                        ],
                        'unit_amount' => (int)($product->getPrice() * 100),
                    ],
                    'quantity' => $quantity,
                ];
            }
        }

       $checkoutSession = \Stripe\Checkout\Session::create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $router->generate('app_cart_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $router->generate('app_cart_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($checkoutSession->url, 303);
    }

    #[Route('/panier/succes', name: 'app_cart_success')]
    public function success(SessionInterface $session): Response
    {
        $session->remove('cart');

        return $this->render('cart/success.html.twig');
    }

    #[Route('/panier/annulation', name: 'app_cart_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé.');
        return $this->redirectToRoute('app_cart_index');
    }
}
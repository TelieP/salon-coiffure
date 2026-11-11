<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\AppointmentRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('/mon-compte', name: 'app_account')]
    public function index(AppointmentRepository $appointmentRepository, OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        
        // Récupère les rendez-vous du client connecté, du plus récent au plus ancien
        $appointments = $appointmentRepository->findBy(['client' => $user], ['id' => 'DESC']);
        
        // Récupère les commandes de la boutique du client connecté
        $orders = $orderRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('front/account.html.twig', [
            'appointments' => $appointments,
            'orders' => $orders,
        ]);
    }

    #[Route('/compte/commande/{id}', name: 'app_account_order_show')]
    public function orderShow(Order $order): Response
    {
        // Sécurité : Vérifie que la commande appartient bien à l'utilisateur connecté
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette commande.");
        }

        return $this->render('account/order_show.html.twig', [
            'order' => $order,
        ]);
    }
}
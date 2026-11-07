<?php

namespace App\Controller;

use App\Repository\AppointmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('/mon-compte', name: 'app_account')]
    public function index(AppointmentRepository $appointmentRepository): Response
    {
        $user = $this->getUser();
        // Récupère les rendez-vous du client connecté, du plus récent au plus ancien
        $appointments = $appointmentRepository->findBy(['client' => $user], ['id' => 'DESC']);

        return $this->render('front/account.html.twig', [
            'appointments' => $appointments,
        ]);
    }
}
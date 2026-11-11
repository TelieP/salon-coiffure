<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/rendez-vous')]
#[IsGranted('ROLE_USER')]
class AppointmentController extends AbstractController
{
    #[Route('/checkout/{id}', name: 'app_appointment_checkout', methods: ['POST'])]
    public function checkout(Service $service, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $totalPrice = $service->getPrice();
        $depositAmount = round($totalPrice * 0.20, 2);

        // Récupération sécurisée des données du formulaire (nom, email et date/heure)
        $formData = $request->request->all('appointment');
        $clientName = $formData['clientName'] ?? ($user->getFirstname() . ' ' . $user->getLastname());
        $clientEmail = $formData['clientEmail'] ?? $user->getEmail();
        $startAtStr = $formData['startAt'] ?? null;

        // 1. Pré-enregistrement du rendez-vous
        $appointment = new Appointment();
        $appointment->setClient($user);
        $appointment->setClientName(trim($clientName));
        $appointment->setClientEmail($clientEmail);
        $appointment->setService($service);
        $appointment->setTotalPrice($totalPrice);
        $appointment->setDepositAmount($depositAmount);
        $appointment->setStatus('pending');
        $appointment->setDepositPaid(false);
        
        // Récupération de la date et l'heure précise choisie par l'utilisateur
        if ($startAtStr) {
            $appointmentDate = new \DateTime($startAtStr);
        } else {
            $appointmentDate = new \DateTime('now');
        }
        
        $appointment->setStartAt($appointmentDate);
        
        $endAt = clone $appointmentDate;
        $endAt->modify('+1 hour');
        $appointment->setEndAt($endAt);

        $em->persist($appointment);
        $em->flush();

        // 2. Initialisation de la session Stripe Checkout
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $session = Session::create([
            'customer_email' => $clientEmail,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Acompte (20%) - ' . $service->getName(),
                        'description' => 'Réservation du ' . $appointmentDate->format('d/m/Y à H:i'),
                    ],
                    'unit_amount' => (int)($depositAmount * 100), // En centimes
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_appointment_success', ['id' => $appointment->getId()], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        $appointment->setStripeSessionId($session->id);
        $em->flush();

        return $this->redirect($session->url, 303);
    }

    #[Route('/succes/{id}', name: 'app_appointment_success')]
    public function success(Appointment $appointment, Request $request, EntityManagerInterface $em): Response
    {
        $sessionId = $request->query->get('session_id');

        if ($sessionId && $appointment->getStripeSessionId() === $sessionId) {
            $appointment->setStatus('confirmed');
            $appointment->setDepositPaid(true);
            $em->flush();

            $this->addFlash('success', 'Votre acompte de 20% a été réglé avec succès. Votre rendez-vous est confirmé !');
        }

        return $this->redirectToRoute('app_account');
    }
}
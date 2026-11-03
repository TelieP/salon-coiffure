<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Service;
use App\Entity\Review;
use App\Form\AppointmentType;
use App\Form\ReviewType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class FrontController extends AbstractController
{
  #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $em): Response
    {
        $services = $em->getRepository(Service::class)->findAll();
        $reviews = $em->getRepository(Review::class)->findBy([], ['createdAt' => 'DESC']);

        $totalRating = 0;
        $countReviews = count($reviews);
        foreach ($reviews as $review) {
            $totalRating += $review->getRating();
        }
        $averageRating = $countReviews > 0 ? round($totalRating / $countReviews, 1) : 0;

        return $this->render('front/index.html.twig', [
            'services' => $services,
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            'countReviews' => $countReviews,
        ]);
    }

    #[Route('/laisser-un-avis', name: 'app_review_new')]
    #[IsGranted('ROLE_USER')] // Bloque l'accès si l'utilisateur n'est pas connecté
    public function newReview(Request $request, EntityManagerInterface $em): Response
    {
        $review = new Review();
// Pré-remplit le nom avec l'utilisateur connecté (adaptez selon les méthodes de votre entité User)
    $user = $this->getUser();
    if ($user) {
        $review->setClientName($user->getUserIdentifier()); // Ou $user->getNom() si le champ existe
    }

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($review);
            $em->flush();

            $this->addFlash('success', 'Merci ! Votre avis a bien été publié.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('front/new_review.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reserver/{id}', name: 'app_booking')]
    #[IsGranted('ROLE_USER', message: 'Vous devez vous connecter pour prendre un rendez-vous.')]
    public function book(Service $service, Request $request, EntityManagerInterface $em): Response
    {
        $dateParam = $request->query->get('date', date('Y-m-d'));
        $selectedDate = new \DateTime($dateParam);

        // Gestion du mois et de l'année pour le calendrier complet
        $year = (int)$request->query->get('year', $selectedDate->format('Y'));
        $month = (int)$request->query->get('month', $selectedDate->format('m'));

        $firstDayOfMonth = new \DateTime("$year-$month-01");
        $daysInMonth = (int)$firstDayOfMonth->format('t');

        // Mois précédent et suivant pour la navigation
        $prevMonth = (clone $firstDayOfMonth)->modify('-1 month');
        $nextMonth = (clone $firstDayOfMonth)->modify('+1 month');

        // Générer les jours du mois avec leur statut (disponible/passé)
        $monthDays = [];
        $today = new \DateTime('today');

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $loopDate = new \DateTime("$year-$month-$day");
            $isPast = $loopDate < $today;
            
            // Exemple : Le salon est fermé le dimanche (jour 7) ou les jours passés
            $isClosed = ($loopDate->format('N') == 7); 
            $isAvailable = !$isPast && !$isClosed;

            $monthDays[] = [
                'date' => $loopDate,
                'isPast' => $isPast,
                'isAvailable' => $isAvailable,
                'isSelected' => ($loopDate->format('Y-m-d') == $selectedDate->format('Y-m-d'))
            ];
        }

        // Générer les créneaux horaires pour la date sélectionnée
        $slots = [];
        if ($selectedDate >= $today) {
            $startHour = 9;
            $endHour = 17;
            
            for ($hour = $startHour; $hour < $endHour; $hour++) {
                for ($minute = 0; $minute < 60; $minute += 30) {
                    $slotTime = (clone $selectedDate)->setTime($hour, $minute);
                    
                    $existing = $em->getRepository(Appointment::class)->findOneBy([
                        'startAt' => $slotTime
                    ]);

                    if (!$existing && $slotTime > new \DateTime()) {
                        $slots[] = $slotTime->format('H:i');
                    }
                }
            }
        }

        $appointment = new Appointment();
        $appointment->setService($service);

        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $duration = $service->getDuration() ?? 30;
            $endAt = (clone $appointment->getStartAt())->modify("+{$duration} minutes");
            $appointment->setEndAt($endAt);
            $appointment->setStatus('pending');
            $appointment->setDepositPaid(false);

            if ($this->getUser()) {
                $appointment->setClient($this->getUser());
            }

            $em->persist($appointment);
            $em->flush();

            $this->addFlash('success', 'Votre demande de rendez-vous a bien été envoyée et est en attente de validation !');
            return $this->redirectToRoute('app_booking_success', ['id' => $appointment->getId()]);
        }

        return $this->render('front/booking.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
            'selectedDate' => $selectedDate,
            'monthDays' => $monthDays,
            'firstDayOfMonth' => $firstDayOfMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'slots' => $slots,
        ]);
    }

    #[Route('/reservation-confirmee/{id}', name: 'app_booking_success')]
    public function success(Appointment $appointment): Response
    {
        return $this->render('front/success.html.twig', [
            'appointment' => $appointment,
        ]);
    }

    #[Route('/mentions-legales', name: 'app_legal_mentions')]
    public function legalMentions(): Response
    {
        return $this->render('front/legal_mentions.html.twig');
    }

    #[Route('/politique-confidentialite', name: 'app_privacy_policy')]
    public function privacyPolicy(): Response
    {
        return $this->render('front/privacy_policy.html.twig');
    }

    #[Route('/cgv', name: 'app_cgv')]
    public function cgv(): Response
    {
        return $this->render('front/cgv.html.twig');
    }

}
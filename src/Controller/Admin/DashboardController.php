<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Entity\Product;
use App\Entity\Service;
use App\Entity\User;
use App\Entity\Review;
use App\Repository\AppointmentRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    private AppointmentRepository $appointmentRepository;
    private ReviewRepository $reviewRepository;
    private UserRepository $userRepository;

    public function __construct(
        AppointmentRepository $appointmentRepository,
        ReviewRepository $reviewRepository,
        UserRepository $userRepository
    ) {
        $this->appointmentRepository = $appointmentRepository;
        $this->reviewRepository = $reviewRepository;
        $this->userRepository = $userRepository;
    }

    public function index(): Response
    {
        // RDV en attente
        $pendingAppointments = $this->appointmentRepository->createQueryBuilder('a')
            ->select('count(a.id)')
            ->where('LOWER(a.status) LIKE :status')
            ->setParameter('status', '%pending%')
            ->getQuery()
            ->getSingleScalarResult();

        // RDV confirmés
        $confirmedAppointments = $this->appointmentRepository->createQueryBuilder('a')
            ->select('count(a.id)')
            ->where('LOWER(a.status) LIKE :status')
            ->setParameter('status', '%confirm%')
            ->getQuery()
            ->getSingleScalarResult();
        
        // Avis non vérifiés
        $pendingReviews = $this->reviewRepository->count(['isVerified' => false]);

        // Nombre total d'utilisateurs inscrits
        $totalUsers = $this->userRepository->count([]);

        // --- PARTIE GRAPHIQUE ---
        // Utilisation correcte de a.startAt basé sur votre entité Appointment
        $stats = $this->appointmentRepository->createQueryBuilder('a')
            ->select("SUBSTRING(a.startAt, 1, 7) as period, COUNT(a.id) as total") 
            ->groupBy('period')
            ->orderBy('period', 'ASC')
            ->getQuery()
            ->getResult();

        $months = [];
        $counts = [];

        foreach ($stats as $stat) {
            $months[] = $stat['period']; // Ex: '2026-11'
            $counts[] = $stat['total'];
        }
        // -------------------------

        return $this->render('admin/dashboard.html.twig', [
            'pendingAppointments' => $pendingAppointments,
            'confirmedAppointments' => $confirmedAppointments,
            'pendingReviews' => $pendingReviews,
            'totalUsers' => $totalUsers,
            'chartMonths' => $months,
            'chartCounts' => $counts,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Salon de Coiffure')
            ->renderSidebarMinimized(false);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Gestion du Salon');
        yield MenuItem::linkToRoute('Rendez-vous', 'fa fa-calendar', 'admin_appointment_index');
        yield MenuItem::linkToRoute('Prestations', 'fa fa-scissors', 'admin_service_index');
        yield MenuItem::linkToRoute('Produits', 'fa fa-box', 'admin_product_index');
        
        yield MenuItem::linkToRoute('Avis clients', 'fas fa-star', 'admin_review_index');

        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkToRoute('Clients & Staff', 'fa fa-users', 'admin_user_index');
    }
}
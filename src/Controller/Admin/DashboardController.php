<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Entity\Product;
use App\Entity\Service;
use App\Entity\User;
use App\Entity\Review;
use App\Controller\Admin\ReviewCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(AppointmentCrudController::class)->generateUrl());
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
        
       // Lien direct vers le CRUD EasyAdmin
       yield MenuItem::linkToRoute('Avis clients', 'fas fa-star', 'admin_review_index');

        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkToRoute('Clients & Staff', 'fa fa-users', 'admin_user_index');
    }
}
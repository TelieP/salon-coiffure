<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AppointmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Appointment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            DateTimeField::new('startAt', 'Date et heure du RDV')->setFormat('dd/MM/yyyy HH:mm'),
            AssociationField::new('service', 'Prestation'),
            TextField::new('clientName', 'Nom du client'),
            TextField::new('clientEmail', 'Email du client'),
            ChoiceField::new('status', 'Statut')->setChoices([
                'Confirmé' => 'confirmed',
                'En attente' => 'pending',
                'Annulé' => 'cancelled',
            ])->renderAsBadges([
                'confirmed' => 'success',
                'pending' => 'warning',
                'cancelled' => 'danger',
            ]),
            BooleanField::new('depositPaid', 'Acompte payé ?'),
        ];
    }
}
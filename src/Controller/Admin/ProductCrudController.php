<?php

// src/Controller/Admin/ProductCrudController.php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom du produit'),
            TextEditorField::new('description', 'Description'),
            NumberField::new('price', 'Prix (€)'),
            IntegerField::new('stock', 'Stock'),
            
            // --- CHAMP D'UPLOAD D'IMAGE ---
            ImageField::new('image', 'Photo du produit')
                ->setBasePath('images')                    // Chemin public pour afficher l'image
                ->setUploadDir('public/images')            // Dossier de stockage physique
                ->setUploadedFileNamePattern('[randomhash].[extension]') // Sécurise le nom du fichier
                ->setRequired(false),
        ];
    }
}

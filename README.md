# Flore Brésilienne - Salon de Coiffure & Beauté

Application web professionnelle développée pour le salon de coiffure **Flore Brésilienne**, situé à La Louvière. Le site propose un système complet de présentation des prestations, de réservation en ligne avec paiement sécurisé d'un acompte, un espace client dédié et un module d'avis vérifiés.

## 🚀 Fonctionnalités principales

- **Catalogue & Horaires :** Présentation des prestations, des horaires d'ouverture et des informations de contact.
- **Réservation en ligne :** Tunnel de réservation interactif par date et créneau horaire.
- **Paiement Sécurisé (Stripe) :** Règlement automatique d'un acompte de 20 % via Stripe Checkout pour valider la prise de rendez-vous.
- **Espace Client (`/mon-compte`) :** Tableau de bord personnel permettant aux clients de suivre l'historique de leurs rendez-vous.
- **Avis Clients Vérifiés :** Publication de commentaires et notes étoilées reliés directement aux comptes utilisateurs authentifiés.
- **Galerie de Réalisations :** Vitrine visuelle mettant en valeur les coiffures du salon.

## 🛠️ Stack Technique

- **Backend :** PHP 8.2+, Symfony 7, Doctrine ORM
- **Frontend :** Twig, Bootstrap 5, JavaScript (AJAX)
- **Base de données :** MySQL / MariaDB
- **Paiement :** API Stripe (Checkout Sessions)

## ⚙️ Installation et Configuration locale

1. **Cloner le projet :**
   ```bash
   git clone [https://github.com/votre-nom-utilisateur/flore-bresilienne.git](https://github.com/votre-nom-utilisateur/flore-bresilienne.git)
   cd flore-bresilienne
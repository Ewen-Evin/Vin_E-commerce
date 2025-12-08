<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$page = $_GET['page'] ?? 'accueil'; // Changé : défaut = accueil

switch ($page) {
    case 'accueil':
        require 'app/views/accueil.php';
        break;

    case 'produits':
        require 'app/controllers/ProduitController.php';
        $controller = new ProduitController();
        $controller->index();
        break;

    case 'panier':
        require 'app/controllers/PanierController.php';
        $controller = new PanierController();
        $controller->index();
        break;

    case 'vider-panier':
        require 'app/controllers/PanierController.php';
        $controller = new PanierController();
        $controller->vider();
        break;

    case 'panier-ajax':
        require 'app/controllers/PanierController.php';
        $controller = new PanierController();
        $controller->ajaxAdd();
        break;

    case 'ajax-commande-produits':
        require 'app/controllers/AjaxController.php';
        $controller = new AjaxController();
        $controller->commandeProduits();
        break;

    case 'remove-item':
        require 'app/controllers/PanierController.php';
        $controller = new PanierController();
        $controller->removeItem();
        break;

    case 'update-qty':
        require 'app/controllers/PanierController.php';
        $controller = new PanierController();
        $controller->updateQty();
        break;

    case 'commande':
        require 'app/controllers/CommandeController.php';
        $controller = new CommandeController();
        $controller->form();
        break;

    case 'traiter-commande':
        require 'app/controllers/CommandeController.php';
        $controller = new CommandeController();
        $controller->traiterCommande();
        break;

    case 'login':
        require 'app/controllers/LoginController.php';
        $controller = new LoginController();
        $controller->form();
        break;

    case 'login-check':
        require 'app/controllers/LoginController.php';
        $controller = new LoginController();
        $controller->check();
        break;

    case 'logout':
        require 'app/controllers/LoginController.php';
        $controller = new LoginController();
        $controller->logout();
        break;

    case 'admin':
        require 'app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->index();
        break;

    case 'admin-produits':
        require 'app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->produits();
        break;

    case 'admin-clients':
        require 'app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->clients();
        break;

    case 'admin-commandes':
        require 'app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->commandes();
        break;

    case 'admin-update-commande':
        require 'app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->updateCommandeStatut();
        break;

    case 'envoyer-email-confirmation':
        $controller = new CommandeController();
        $controller->envoyerEmailConfirmation();
        break;

    case 'admin-send-delivery-email':
        require 'app/controllers/AdminEmailController.php';
        $controller = new AdminEmailController();
        $controller->sendDeliveryEmail();
        break;

    default:
        require 'app/views/accueil.php';
        break;
}

<?php
require_once "app/models/Produit.php";

class ProduitController {
    public function index() {
        $produits = Produit::getAll();   // récupération en BDD
        require "app/views/produits.php"; // on passe la variable $produits à la vue
    }
}

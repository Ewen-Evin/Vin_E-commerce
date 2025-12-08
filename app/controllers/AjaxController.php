<?php
require_once "/home/ewenevh/config/database.php";

class AjaxController {
    public function commandeProduits() {
        if (!isset($_SESSION['admin'])) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'Accès non autorisé']);
            exit;
        }
        
        $commandeId = $_GET['id'] ?? 0;
        
        if (!$commandeId) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'ID commande manquant']);
            exit;
        }
        
        $db = Database::getConnection();
        $query = "SELECT p.nom, p.categorie, p.quantite_par_carton, cp.quantite, cp.prix_unitaire 
                  FROM vin_commandes_produits cp 
                  JOIN vin_produits p ON cp.id_produit = p.id 
                  WHERE cp.id_commande = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$commandeId]);
        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($produits);
        exit;
    }
}
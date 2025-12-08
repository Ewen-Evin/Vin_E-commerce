<?php
require_once __DIR__ . '/../../config/database.php';

class AdminProduitsController {
    public function index() {
        if (!isset($_SESSION['admin'])) {
            header("Location: index.php?page=login");
            exit;
        }
        
        $db = Database::getConnection();
        
        // Gestion de l'ajout de produit
        if (($_POST['action'] ?? '') === 'add_product') {
            $this->addProduct($db);
        }
        
        // Gestion de la modification de produit
        if (($_POST['action'] ?? '') === 'update_product') {
            $this->updateProduct($db);
        }
        
        // Gestion de la suppression de produit
        if (($_POST['action'] ?? '') === 'delete_product') {
            $this->deleteProduct($db);
        }
        
        // Récupérer tous les produits
        $stmt = $db->query("SELECT * FROM vin_produits ORDER BY id");
        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require "app/views/admin_produits.php";
    }
    
    private function addProduct($db) {
        $reference = $_POST['reference'] ?? '';
        $nom = $_POST['nom'] ?? '';
        $description = $_POST['description'] ?? '';
        $categorie = $_POST['categorie'] ?? '';
        $prix_unitaire = $_POST['prix_unitaire'] ?? 0;
        $quantite_par_carton = $_POST['quantite_par_carton'] ?? 1;
        
        // Gestion de l'image avec le nouveau format
        $image_name = $this->handleImageUpload('image', $nom, $categorie);
        
        $stmt = $db->prepare("
            INSERT INTO vin_produits (reference, nom, description, categorie, prix_unitaire, quantite_par_carton, image) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$reference, $nom, $description, $categorie, $prix_unitaire, $quantite_par_carton, $image_name]);
        
        // Stocker le message de succès en session pour SweetAlert
        $_SESSION['success_message'] = "Produit ajouté avec succès";
        
        header("Location: index.php?page=admin-produits");
        exit;
    }
    
    private function updateProduct($db) {
        // Vérifier que l'ID est bien présent
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            $_SESSION['error_message'] = "ID produit manquant";
            header("Location: index.php?page=admin-produits");
            exit;
        }
        
        $id = $_POST['id'];
        $reference = $_POST['reference'] ?? '';
        $nom = $_POST['nom'] ?? '';
        $description = $_POST['description'] ?? '';
        $categorie = $_POST['categorie'] ?? '';
        $prix_unitaire = $_POST['prix_unitaire'] ?? 0;
        $quantite_par_carton = $_POST['quantite_par_carton'] ?? 1;
        
        // Vérifier si le produit existe
        $checkStmt = $db->prepare("SELECT id FROM vin_produits WHERE id = ?");
        $checkStmt->execute([$id]);
        $produitExists = $checkStmt->fetch();
        
        if (!$produitExists) {
            $_SESSION['error_message'] = "Produit non trouvé";
            header("Location: index.php?page=admin-produits");
            exit;
        }
        
        // Récupérer l'ancien produit pour le nom de l'image
        $stmt = $db->prepare("SELECT image FROM vin_produits WHERE id = ?");
        $stmt->execute([$id]);
        $old_product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Gestion de l'image avec le nouveau format
        $image_name = $old_product['image'];
        if (!empty($_FILES['image']['name'])) {
            // Supprimer l'ancienne image si elle existe
            if ($image_name && file_exists("public/images/produits/" . $image_name)) {
                unlink("public/images/produits/" . $image_name);
            }
            $image_name = $this->handleImageUpload('image', $nom, $categorie);
        }
        
        $stmt = $db->prepare("
            UPDATE vin_produits 
            SET reference = ?, nom = ?, description = ?, categorie = ?, prix_unitaire = ?, quantite_par_carton = ?, image = ?
            WHERE id = ?
        ");
        $stmt->execute([$reference, $nom, $description, $categorie, $prix_unitaire, $quantite_par_carton, $image_name, $id]);
        
        $_SESSION['success_message'] = "Produit modifié avec succès";
        header("Location: index.php?page=admin-produits");
        exit;
    }
    
    private function deleteProduct($db) {
        // Vérifier que l'ID est bien présent
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            $_SESSION['error_message'] = "ID produit manquant";
            header("Location: index.php?page=admin-produits");
            exit;
        }
        
        $id = $_POST['id'];
        
        // Récupérer l'image pour la supprimer
        $stmt = $db->prepare("SELECT image FROM vin_produits WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Supprimer l'image si elle existe
        if ($product && $product['image'] && file_exists("public/images/produits/" . $product['image'])) {
            unlink("public/images/produits/" . $product['image']);
        }
        
        $stmt = $db->prepare("DELETE FROM vin_produits WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success_message'] = "Produit supprimé avec succès";
        header("Location: index.php?page=admin-produits");
        exit;
    }
    
    private function handleImageUpload($field_name, $nom = '', $categorie = '') {
        if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $file = $_FILES[$field_name];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        // Vérification du type et de la taille
        if (!in_array($file['type'], $allowed_types) || $file['size'] > $max_size) {
            return null;
        }
        
        // Créer le dossier s'il n'existe pas
        $upload_dir = "public/images/produits/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Générer un nom selon le format "nomVin_categorieVin.extension"
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        // Nettoyer le nom et la catégorie pour le nom de fichier
        $nom_clean = $this->cleanFileName($nom);
        $categorie_clean = $this->cleanFileName($categorie);
        
        // Créer le nom de fichier
        if (!empty($nom_clean) && !empty($categorie_clean)) {
            $image_name = $nom_clean . '_' . $categorie_clean . '.' . $extension;
            
            // Vérifier si le fichier existe déjà et ajouter un suffixe si nécessaire
            $counter = 1;
            $original_name = $image_name;
            while (file_exists($upload_dir . $image_name)) {
                $image_name = pathinfo($original_name, PATHINFO_FILENAME) . '_' . $counter . '.' . $extension;
                $counter++;
            }
        } else {
            // Fallback si pas de nom ou catégorie
            $image_name = uniqid() . '_' . time() . '.' . $extension;
        }
        
        $destination = $upload_dir . $image_name;
        
        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $image_name;
        }
        
        return null;
    }
    
    private function cleanFileName($string) {
        // Supprimer les accents
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        // Remplacer les espaces et caractères spéciaux
        $string = preg_replace('/[^a-zA-Z0-9_-]/', '_', $string);
        // Supprimer les underscores multiples
        $string = preg_replace('/_{2,}/', '_', $string);
        // Supprimer les underscores en début et fin
        $string = trim($string, '_');
        // Convertir en minuscules
        $string = strtolower($string);
        // Limiter la longueur
        $string = substr($string, 0, 50);
        
        return $string;
    }
}
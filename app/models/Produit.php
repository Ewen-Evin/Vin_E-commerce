<?php
require_once __DIR__ . '/../../config/database.php';


class Produit {
    public static function getAll() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM vin_produits");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByIds($idArray) {
        if (empty($idArray)) return [];

        $db = Database::getConnection();
        $placeholders = implode(',', array_fill(0, count($idArray), '?'));
        $stmt = $db->prepare("SELECT * FROM vin_produits WHERE id IN ($placeholders)");
        $stmt->execute($idArray);
        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Indexer par ID pour accès rapide
        $result = [];
        foreach ($produits as $p) {
            $result[$p['id']] = $p;
        }
        return $result;
    }

}

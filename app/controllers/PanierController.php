<?php
require_once "app/models/Produit.php";

class PanierController {
    public function index() {
        $panier = $_SESSION['panier'] ?? [];

        // Récupération des produits pour affichage
        $produits = [];
        $total = 0;

        if (!empty($panier)) {
            $ids = array_keys($panier);
            $produitsDB = Produit::getByIds($ids); // retourne tableau indexé par id
            foreach ($ids as $id) {
                if (isset($produitsDB[$id])) {
                    $prod = $produitsDB[$id];
                    $qte = $panier[$id];
                    $prod['quantite'] = $qte;
                    $prod['sous_total'] = $prod['prix_unitaire'] * $qte;
                    $produits[] = $prod;
                    $total += $prod['sous_total'];
                }
            }
        }

        require "app/views/panier.php";
    }

    public function vider() {
        unset($_SESSION['panier']);
        header("Location: index.php?page=panier");
        exit;
    }

    // Ajout via Ajax depuis la page produits
    public function ajaxAdd() {
        header('Content-Type: application/json; charset=utf-8');
        $panier = $_SESSION['panier'] ?? [];

        // Accepter soit 'id_produit' soit 'id'
        $id = intval($_POST['id_produit'] ?? $_POST['id'] ?? 0);
        $quantite = intval($_POST['quantite'] ?? 0);

        if ($id > 0 && $quantite > 0) {
            if (isset($panier[$id])) {
                $panier[$id] += $quantite;
            } else {
                $panier[$id] = $quantite;
            }
            $_SESSION['panier'] = $panier;
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false]);
        }
        exit;
    }

    // Mise à jour quantité (Ajax)
    public function updateQty() {
        header('Content-Type: application/json; charset=utf-8');
        $panier = $_SESSION['panier'] ?? [];

        $id = intval($_POST['id_produit'] ?? 0);
        $quantite = intval($_POST['quantite'] ?? 0);

        if ($id > 0 && $quantite > 0) {
            $panier[$id] = $quantite;
            $_SESSION['panier'] = $panier;

            $produitsDB = Produit::getByIds([$id]);
            $produit = $produitsDB[$id] ?? null;

            if ($produit) {
                $sousTotal = number_format($produit['prix_unitaire'] * $quantite, 2);

                // recalcul du total
                $total = 0;
                foreach ($panier as $pid => $qte) {
                    $pdb = Produit::getByIds([$pid]);
                    if (isset($pdb[$pid])) {
                        $total += $pdb[$pid]['prix_unitaire'] * $qte;
                    }
                }

                echo json_encode([
                    "success" => true,
                    "sous_total" => $sousTotal,
                    "total" => number_format($total, 2)
                ]);
                exit;
            }
        }

        echo json_encode(["success" => false]);
        exit;
    }

    // Suppression d'un produit (Ajax)
    public function removeItem() {
        header('Content-Type: application/json; charset=utf-8');
        $id = intval($_POST['id_produit'] ?? 0);

        if ($id > 0 && isset($_SESSION['panier'][$id])) {
            unset($_SESSION['panier'][$id]);

            // recalcul du total
            $total = 0;
            foreach ($_SESSION['panier'] ?? [] as $pid => $qte) {
                $pdb = Produit::getByIds([$pid]);
                if (isset($pdb[$pid])) {
                    $total += $pdb[$pid]['prix_unitaire'] * $qte;
                }
            }

            echo json_encode([
                "success" => true,
                "total" => number_format($total, 2)
            ]);
            exit;
        }

        echo json_encode(["success" => false, "message" => "Produit non trouvé dans le panier"]);
        exit;
    }
}

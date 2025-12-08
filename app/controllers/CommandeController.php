<?php
require_once __DIR__ . '/../../config/database.php';
require_once "app/models/Produit.php";

class CommandeController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    public function form() {
        if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
            header("Location: index.php?page=panier");
            exit;
        }
        
        $produits = [];
        $total = 0;
        
        foreach ($_SESSION['panier'] as $id => $quantite) {
            $stmt = $this->db->prepare("SELECT * FROM vin_produits WHERE id = ?");
            $stmt->execute([$id]);
            $produit = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($produit) {
                $sous_total = $produit['prix_unitaire'] * $quantite;
                $produits[] = [
                    'id' => $produit['id'],
                    'nom' => $produit['nom'],
                    'image' => $produit['image'] ?? 'default.png',
                    'prix_unitaire' => $produit['prix_unitaire'],
                    'quantite' => $quantite,
                    'sous_total' => $sous_total,
                    'categorie' => $produit['categorie'],
                    'quantite_par_carton' => $produit['quantite_par_carton']
                ];
                $total += $sous_total;
            }
        }
        
        require "app/views/commande.php";
    }
    
    public function traiterCommande() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=panier");
            exit;
        }
        
        // Validation des données
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $adresse = trim($_POST['adresse'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $meme_personne = isset($_POST['meme_personne']) ? 1 : 0;
        $nom_recup = trim($_POST['nom_recup'] ?? '');
        $prenom_recup = trim($_POST['prenom_recup'] ?? '');
        
        // Validation des champs obligatoires
        if (empty($nom) || empty($prenom) || empty($email) || empty($telephone) || empty($adresse)) {
            $_SESSION['error_message'] = "Tous les champs obligatoires doivent être remplis";
            header("Location: index.php?page=commande");
            exit;
        }
        
        // Validation si personne différente
        if ($meme_personne == 0 && (empty($nom_recup) || empty($prenom_recup))) {
            $_SESSION['error_message'] = "Veuillez remplir les informations de la personne qui récupère la commande";
            header("Location: index.php?page=commande");
            exit;
        }
        
        // Si même personne, copier les informations
        if ($meme_personne == 1) {
            $nom_recup = $nom . ' ' . $prenom;
            $prenom_recup = '';
        } else {
            // Si personne différente, concaténer nom et prénom dans nom_recup
            $nom_recup = $nom_recup . ' ' . $prenom_recup;
            $prenom_recup = '';
        }
        
        if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
            header("Location: index.php?page=panier");
            exit;
        }
        
        $produits = [];
        $total = 0;
        $total_cartons = 0;
        
        foreach ($_SESSION['panier'] as $id => $quantite) {
            $stmt = $this->db->prepare("SELECT * FROM vin_produits WHERE id = ?");
            $stmt->execute([$id]);
            $produit = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($produit) {
                // CALCUL CORRIGÉ : Utiliser la même logique que dans l'email
                $categorie = $produit['categorie'] ?? '';
                $bouteilles_par_carton = $produit['quantite_par_carton'] ?? 1;
                $prix_unitaire = $produit['prix_unitaire'];
                
                $est_coffret = in_array($categorie, ['Coffrets']);
                $est_vin_biere = in_array($categorie, ['Rouges', 'Blancs', 'Rosés', 'Bulles', 'Bière']) && !$est_coffret;
                
                if ($est_coffret) {
                    // COFFRETS : Le prix unitaire est pour le coffret complet
                    $sous_total = $prix_unitaire * $quantite;
                }
                elseif ($est_vin_biere) {
                    // VINS/BIERES : Le prix unitaire est par bouteille, on calcule le prix du carton
                    $prix_carton = $prix_unitaire * $bouteilles_par_carton;
                    $sous_total = $prix_carton * $quantite;
                }
                else {
                    // ACCESSOIRES : Le prix unitaire est pour l'unité (ou le lot)
                    $sous_total = $prix_unitaire * $quantite;
                }
                
                $produits[] = [
                    'id' => $produit['id'],
                    'nom' => $produit['nom'],
                    'image' => $produit['image'] ?? 'default.png',
                    'prix_unitaire' => $produit['prix_unitaire'],
                    'quantite' => $quantite,
                    'sous_total' => $sous_total,
                    'categorie' => $produit['categorie'],
                    'quantite_par_carton' => $produit['quantite_par_carton']
                ];
                $total += $sous_total;
                $total_cartons += $quantite;
            }
        }
        
        // Sauvegarde de la commande en base
        $stmt = $this->db->prepare("
            INSERT INTO vin_commandes 
            (nom_client, email, telephone, adresse, meme_personne, nom_recup, message, total, statut, date_commande) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW())
        ");
        $stmt->execute([
            $nom . ' ' . $prenom,
            $email, 
            $telephone, 
            $adresse, 
            $meme_personne, 
            $nom_recup,
            $message, 
            $total
        ]);
        $commande_id = $this->db->lastInsertId();
        
        // Sauvegarde des produits de la commande
        foreach ($produits as $produit) {
            $stmt = $this->db->prepare("
                INSERT INTO vin_commandes_produits 
                (id_commande, id_produit, quantite, prix_unitaire) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $commande_id, 
                $produit['id'], 
                $produit['quantite'], 
                $produit['prix_unitaire']
            ]);
        }
        
        // Préparer les données pour la vue
        $commande = [
            'id' => $commande_id,
            'nom_client' => $nom . ' ' . $prenom,
            'email' => $email,
            'telephone' => $telephone,
            'adresse' => $adresse,
            'meme_personne' => $meme_personne,
            'nom_recup' => $nom_recup,
            'message' => $message,
            'total' => $total
        ];
        
        // Stocker la commande en session pour l'email
        $this->stockerCommandeSession($commande_id, [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'meme_personne' => $meme_personne,
            'nom_recup' => $nom_recup
        ]);
        
        // Envoyer l'email de confirmation immédiatement
        $this->envoyerEmailConfirmationImmediate($commande_id);
        
        // Vider le panier après confirmation
        unset($_SESSION['panier']);
        
        // Afficher la page de confirmation
        $this->confirmation($commande, $produits, $total, $total_cartons);
    }
    
    private function confirmation($commande, $produits, $total, $total_cartons) {
        require "app/views/confirmation.php";
    }

    public function envoyerEmailConfirmation() {
        header('Content-Type: application/json; charset=utf-8');
        
        $commandeId = intval($_POST['commande_id'] ?? 0);
        
        if ($commandeId > 0) {
            $envoye = $this->envoyerEmailConfirmationImmediate($commandeId);
            
            if ($envoye) {
                echo json_encode(["success" => true, "message" => "Email envoyé avec succès"]);
            } else {
                echo json_encode(["success" => false, "message" => "Erreur lors de l'envoi de l'email"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "ID de commande invalide"]);
        }
        exit;
    }

    private function envoyerEmailConfirmationImmediate($commandeId) {
        // Récupérer les données de la commande
        $commande = $this->getCommandeById($commandeId);
        $produits = $this->getProduitsCommande($commandeId);
        $total = $this->calculerTotalCommande($produits);
        
        // Construire le contenu HTML de l'email
        $sujet = "Confirmation de votre commande #" . $commandeId . " - Ewen et Vins";
        $message = $this->construireEmailConfirmation($commande, $produits, $total);
        
        // Envoyer l'email
        return $this->envoyerEmail($commande['email'], $sujet, $message);
    }

    private function getCommandeById($commandeId) {
        // Récupérer la commande depuis la session
        if (isset($_SESSION['derniere_commande']) && $_SESSION['derniere_commande']['id'] == $commandeId) {
            return $_SESSION['derniere_commande'];
        }
        
        // Sinon, récupérer depuis la base de données
        try {
            $stmt = $this->db->prepare("SELECT * FROM vin_commandes WHERE id = ?");
            $stmt->execute([$commandeId]);
            $commande = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($commande) {
                return [
                    'id' => $commande['id'],
                    'nom_client' => $commande['nom_client'],
                    'email' => $commande['email'],
                    'meme_personne' => $commande['meme_personne'],
                    'nom_recup' => $commande['nom_recup']
                ];
            }
        } catch (Exception $e) {
            error_log("Erreur récupération commande: " . $e->getMessage());
        }
        
        // Retourner des données par défaut si la commande n'est pas trouvée
        return [
            'id' => $commandeId,
            'nom_client' => 'Client',
            'email' => $_SESSION['user_email'] ?? 'client@example.com',
            'meme_personne' => 1,
            'nom_recup' => ''
        ];
    }

    private function getProduitsCommande($commandeId) {
        // Récupérer depuis la base de données
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, cp.quantite 
                FROM vin_commandes_produits cp 
                JOIN vin_produits p ON cp.id_produit = p.id 
                WHERE cp.id_commande = ?
            ");
            $stmt->execute([$commandeId]);
            $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculer les sous-totaux avec la même logique que dans la confirmation
            foreach ($produits as &$prod) {
                $categorie = $prod['categorie'] ?? '';
                $bouteilles_par_carton = $prod['quantite_par_carton'] ?? 1;
                $prix_unitaire = $prod['prix_unitaire'];
                
                $est_coffret = in_array($categorie, ['Coffrets']);
                $est_vin_biere = in_array($categorie, ['Rouges', 'Blancs', 'Rosés', 'Bulles', 'Bière']) && !$est_coffret;
                
                if ($est_coffret) {
                    // COFFRETS : Le prix unitaire est pour le coffret complet
                    $prod['sous_total'] = $prix_unitaire * $prod['quantite'];
                }
                elseif ($est_vin_biere) {
                    // VINS/BIERES : Le prix unitaire est par bouteille, on calcule le prix du carton
                    $prix_carton = $prix_unitaire * $bouteilles_par_carton;
                    $prod['sous_total'] = $prix_carton * $prod['quantite'];
                }
                else {
                    // ACCESSOIRES : Le prix unitaire est pour l'unité (ou le lot)
                    $prod['sous_total'] = $prix_unitaire * $prod['quantite'];
                }
            }
            
            return $produits;
        } catch (Exception $e) {
            error_log("Erreur récupération produits commande: " . $e->getMessage());
            return [];
        }
    }

    private function calculerTotalCommande($produits) {
        $total = 0;
        foreach ($produits as $prod) {
            $total += $prod['sous_total'];
        }
        return $total;
    }

    private function envoyerEmail($destinataire, $sujet, $message) {
        $headers = "From: Ewen et Vins <vin-contact@ewenevin.fr>\r\n";
        $headers .= "Reply-To: vin-contact@ewenevin.fr\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        
        try {
            // Envoi réel de l'email
            $envoye = mail($destinataire, $sujet, $message, $headers);
            
            if ($envoye) {
                error_log("EMAIL ENVOYÉ - À: $destinataire, Sujet: $sujet");
            } else {
                error_log("ERREUR ENVOI EMAIL - À: $destinataire, Sujet: $sujet");
            }
            
            return $envoye;
        } catch (Exception $e) {
            error_log("EXCEPTION ENVOI EMAIL: " . $e->getMessage());
            return false;
        }
    }

    private function construireEmailConfirmation($commande, $produits, $total) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Confirmation de commande - Ewen et Vins</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background: white; }
                .header { background: #5a0c24; color: white; padding: 30px 20px; text-align: center; }
                .logo { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
                .content { padding: 30px 20px; background: #f9f9f9; }
                .section { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                
                /* Styles pour le tableau minimaliste comme dans la confirmation */
                .confirmation-table { width: 100%; border-collapse: collapse; margin: 20px 0; background: white; border-radius: 8px; overflow: hidden; }
                .confirmation-table th { background: #34495e; color: white; padding: 12px 15px; text-align: left; font-weight: 600; font-size: 0.9em; }
                .confirmation-table td { padding: 12px 15px; border-bottom: 1px solid #ecf0f1; }
                .confirmation-table tr:last-child td { border-bottom: none; }
                .confirmation-table tr:hover { background: #f8f9fa; }
                .produit-nom { font-weight: 600; color: #2c3e50; }
                .produit-info { color: #7f8c8d; font-size: 0.85em; margin-top: 3px; display: block; }
                .conditionnement { color: #3498db; font-size: 0.9em; }
                .quantite { font-weight: 500; }
                .details-quantite { color: #95a5a6; font-size: 0.8em; display: block; margin-top: 2px; }
                .sous-total { color: #27ae60; font-weight: 600; text-align: right; }
                .total { text-align: right; font-size: 1.2em; color: #c0392b; margin-top: 15px; padding-top: 15px; border-top: 2px solid #bdc3c7; }
                
                .footer { background: #2c3e50; color: white; padding: 20px; text-align: center; font-size: 14px; }
                .highlight { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; border-radius: 4px; }
                .info-box { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0; border-radius: 4px; }
                .warning-box { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0; border-radius: 4px; }
                .virement-box { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 15px 0; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="logo">🍷 Ewen et Vins</div>
                    <h1 style="margin: 10px 0; font-size: 28px;">✅ Votre commande est confirmée !</h1>
                    <p style="font-size: 18px; margin: 10px 0; opacity: 0.9;">Numéro de commande : <strong>#<?= $commande['id'] ?></strong></p>
                </div>
                
                <div class="content">
                    <div class="section">
                        <h2>Cher(e) <?= htmlspecialchars($commande['nom_client']) ?>,</h2>
                        <p>Votre commande a bien été enregistrée chez <strong>Ewen et Vins</strong>. Nous vous tiendrons informé de son avancement par email.</p>
                    </div>

                    <div class="section">
                        <h3 style="color: #5a0c24; border-bottom: 2px solid #5a0c24; padding-bottom: 10px;">📋 Récapitulatif de votre commande</h3>
                        
                        <table class="confirmation-table">
                            <tr>
                                <th>Produit</th>
                                <th>Conditionnement</th>
                                <th>Quantité</th>
                                <th>Sous-total</th>
                            </tr>
                            <?php foreach ($produits as $prod): ?>
                                <?php
                                $categorie = $prod['categorie'] ?? '';
                                $bouteilles_par_carton = $prod['quantite_par_carton'] ?? 1;
                                $prix_unitaire = $prod['prix_unitaire'];
                                
                                // Déterminer le type de produit
                                $est_accessoire = in_array($categorie, ['Accessoires']);
                                $est_coffret = in_array($categorie, ['Coffrets']);
                                $est_vin_biere = in_array($categorie, ['Rouges', 'Blancs', 'Rosés', 'Bulles', 'Bière']) && !$est_coffret;
                                
                                // Calculs selon le type de produit
                                if ($est_coffret) {
                                    $conditionnement = "Coffret de " . $bouteilles_par_carton . " bouteille" . ($bouteilles_par_carton > 1 ? 's' : '');
                                    $details_quantite = $prod['quantite'] . " coffret" . ($prod['quantite'] > 1 ? 's' : '');
                                }
                                elseif ($est_vin_biere) {
                                    $conditionnement = $bouteilles_par_carton . " bouteille" . ($bouteilles_par_carton > 1 ? 's' : '');
                                    $total_bouteilles = $prod['quantite'] * $bouteilles_par_carton;
                                    $details_quantite = $prod['quantite'] . " carton" . ($prod['quantite'] > 1 ? 's' : '');
                                }
                                else {
                                    $conditionnement = $bouteilles_par_carton > 1 ? "Lot de " . $bouteilles_par_carton . " pièces" : "À l'unité";
                                    $details_quantite = $prod['quantite'] . ($bouteilles_par_carton > 1 ? " lot" . ($prod['quantite'] > 1 ? 's' : '') : " pièce" . ($prod['quantite'] > 1 ? 's' : ''));
                                }
                                ?>
                                <tr>
                                    <td>
                                        <div class="produit-nom"><?= htmlspecialchars($prod['nom']) ?></div>
                                        <small class="produit-info">
                                            <?php if ($est_coffret): ?>
                                                🎁 Coffret
                                            <?php elseif ($est_vin_biere): ?>
                                                📦 Vin/Bière
                                            <?php else: ?>
                                                🔧 Accessoire
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td class="conditionnement">
                                        <?= $conditionnement ?>
                                    </td>
                                    <td class="quantite">
                                        <?= $details_quantite ?>
                                        <?php if ($est_vin_biere): ?>
                                            <br>
                                            <small class="details-quantite">
                                                (<?= $total_bouteilles ?> bouteille<?= $total_bouteilles > 1 ? 's' : '' ?>)
                                            </small>
                                        <?php elseif ($est_accessoire && $bouteilles_par_carton > 1): ?>
                                            <br>
                                            <small class="details-quantite">
                                                (<?= $total_pieces = $prod['quantite'] * $bouteilles_par_carton ?> pièce<?= $total_pieces > 1 ? 's' : '' ?>)
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="sous-total">
                                        <strong><?= number_format($prod['sous_total'], 2) ?> €</strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                        
                        <p class="total">💵 Total : <strong><?= number_format($total, 2) ?> €</strong></p>
                    </div>

                    <div class="highlight">
                        <h3>📦 Informations de Récupération</h3>
                        <?php if ($commande['meme_personne'] == 1): ?>
                            <p><strong>🔄 La commande sera récupérée par :</strong> <?= htmlspecialchars($commande['nom_client']) ?> (même personne)</p>
                        <?php else: ?>
                            <p><strong>👤 La commande sera récupérée par :</strong> <?= htmlspecialchars($commande['nom_recup']) ?></p>
                        <?php endif; ?>
                        <p><strong>📅 Livraison prévue :</strong> <span style="color: #d35400; font-weight: bold;">Début Décembre 2025</span></p>
                    </div>

                    <div class="virement-box">
                        <h3>💳 Paiement par Virement Instantané</h3>
                        <p><strong>Veuillez régler par virement instantané à :</strong></p>
                        <div class="virement-details">
                            <p><strong>🏦 IBAN :</strong> FR76 1470 6000 0273 9957 4572 816</p>
                            <p><strong>👤 Bénéficiaire :</strong> Ewen Evin</p>
                            <p><strong>💰 Montant :</strong> <span style="color: #c0392b; font-weight: bold;"><?= number_format($total, 2) ?> €</span></p>
                            <p><strong>🏷️ Référence :</strong> "<?= htmlspecialchars($commande['nom_client']) ?> – Commande : #<?= $commande['id'] ?>"</p>
                        </div>
                    </div>

                    <div class="info-box">
                        <h3>📋 Instructions Importantes</h3>
                        <ul>
                            <li>✅ Seule la commande validée par virement reçu est prise en compte</li>
                            <li>⏰ Votre commande sera confirmée dès réception du virement</li>
                            <li>📝 Utilisez exactement la référence indiquée pour le traitement</li>
                            <li>📦 <strong>Livraison prévue : Début Décembre 2025</strong></li>
                            <li>📞 Vous serez contacté pour organiser la récupération</li>
                            <li>🔄 Délai de préparation : 2-3 jours ouvrables après réception du paiement</li>
                        </ul>
                    </div>

                    <div class="section">
                        <h3>📧 Suivi de Votre Commande</h3>
                        <p><strong>Un email récapitulatif vous a été envoyé à :</strong> <?= htmlspecialchars($commande['email']) ?></p>
                        <p><strong>Vous serez informé par email :</strong></p>
                        <ul>
                            <li>✅ De la réception de votre paiement</li>
                            <li>📦 Du statut de préparation de votre commande</li>
                            <li>🚚 De la date exacte de livraison début décembre</li>
                            <li>📞 Des informations de récupération</li>
                        </ul>
                    </div>

                    <div class="section" style="text-align: center; background: #f8f9fa;">
                        <h3>❓ Une question ?</h3>
                        <p>Contactez-nous à <a href="mailto:vin-contact@ewenevin.fr" style="color: #5a0c24;">vin-contact@ewenevin.fr</a></p>
                        <p>Nous vous répondrons dans les plus brefs délais.</p>
                    </div>
                </div>
                
                <div class="footer">
                    <p>Merci pour votre confiance !</p>
                    <p><strong>🍷 L'équipe Ewen et Vins</strong></p>
                    <p>Email : <a href="mailto:vin-contact@ewenevin.fr" style="color: #fff;">vin-contact@ewenevin.fr</a></p>
                    <p style="margin-top: 15px; font-size: 12px; opacity: 0.8;">
                        Cet email a été envoyé automatiquement, merci de ne pas y répondre.
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    // Méthode pour stocker la commande dans la session
    private function stockerCommandeSession($commandeId, $donneesCommande) {
        $_SESSION['derniere_commande'] = [
            'id' => $commandeId,
            'nom_client' => $donneesCommande['nom'] . ' ' . $donneesCommande['prenom'],
            'email' => $donneesCommande['email'],
            'meme_personne' => $donneesCommande['meme_personne'] ?? 1,
            'nom_recup' => $donneesCommande['nom_recup'] ?? ''
        ];
    }
}
<?php
require_once __DIR__ . '/../config/database.php';

class AdminEmailController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    public function sendDeliveryEmail() {
        if (!isset($_SESSION['admin'])) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
            exit;
        }
        
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // Récupérer toutes les commandes payées
            $stmt = $this->db->prepare("
                SELECT DISTINCT c.* 
                FROM vin_commandes c
                WHERE c.statut = 'payee'
                ORDER BY c.id
            ");
            $stmt->execute();
            $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $resultats = [];
            $totalEnvoyes = 0;
            
            foreach ($commandes as $commande) {
                // Pour le moment, on envoie uniquement à l'email de test
                // COMMENTER la ligne ci-dessous et DÉCOMMENTER la suivante pour envoyer à tous les clients
                $emailDestinataire = 'ewenevin0@gmail.com'; // MODE TEST
                // $emailDestinataire = $commande['email']; // MODE PRODUCTION
                
                $envoye = $this->envoyerEmailLivraison([
                    'id' => $commande['id'],
                    'nom_client' => $commande['nom_client'],
                    'email' => $emailDestinataire,
                    'meme_personne' => $commande['meme_personne'],
                    'nom_recup' => $commande['nom_recup']
                ]);
                
                if ($envoye) {
                    $totalEnvoyes++;
                    $resultats[] = "Commande #{$commande['id']} - {$commande['nom_client']} : SUCCÈS";
                } else {
                    $resultats[] = "Commande #{$commande['id']} - {$commande['nom_client']} : ÉCHEC";
                }
                
                // Petite pause pour éviter de surcharger le serveur
                usleep(500000); // 0.5 seconde
            }
            
            $message = "Emails de livraison envoyés : $totalEnvoyes/" . count($commandes);
            
            echo json_encode([
                'success' => true, 
                'message' => $message,
                'details' => $resultats,
                'test_mode' => true // Indique qu'on est en mode test
            ]);
            
        } catch (Exception $e) {
            error_log("ERREUR AdminEmailController: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Erreur technique: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    private function envoyerEmailLivraison($commande) {
        $destinataire = $commande['email'];
        $sujet = "📦 Information Livraison - Votre commande #" . $commande['id'] . " - Ewen et Vins";
        $message = $this->construireEmailLivraison($commande);
        
        return $this->envoyerEmail($destinataire, $sujet, $message);
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
                error_log("EMAIL LIVRAISON ENVOYÉ - À: $destinataire, Sujet: $sujet");
            } else {
                error_log("ERREUR ENVOI EMAIL LIVRAISON - À: $destinataire, Sujet: $sujet");
            }
            
            return $envoye;
        } catch (Exception $e) {
            error_log("EXCEPTION ENVOI EMAIL LIVRAISON: " . $e->getMessage());
            return false;
        }
    }
    
    private function construireEmailLivraison($commande) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Information Livraison - Ewen et Vins</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background: white; }
                .header { background: #5a0c24; color: white; padding: 30px 20px; text-align: center; }
                .logo { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
                .content { padding: 30px 20px; background: #f9f9f9; }
                .section { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .highlight { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; border-radius: 4px; }
                .info-box { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0; border-radius: 4px; }
                .footer { background: #2c3e50; color: white; padding: 20px; text-align: center; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="logo">🍷 Ewen et Vins</div>
                    <h1 style="margin: 10px 0; font-size: 28px;">📦 Information de Livraison</h1>
                    <p style="font-size: 18px; margin: 10px 0; opacity: 0.9;">Commande #<?= $commande['id'] ?></p>
                </div>
                
                <div class="content">
                    <div class="section">
                        <h2>Cher(e) <?= htmlspecialchars($commande['nom_client']) ?>,</h2>
                        <p>Nous avons une information importante concernant la livraison de votre commande.</p>
                    </div>

                    <div class="highlight">
                        <h3 style="color: #856404; margin-top: 0;">🚚 INFORMATION DE LIVRAISON</h3>
                        <p style="font-size: 1.2em; font-weight: bold; color: #856404;">
                            "Bonsoir,<br>
                            La livraison du vin se fera le 8 décembre à 17h."
                        </p>
                    </div>

                    <div class="info-box">
                        <h3 style="color: #155724; margin-top: 0;">📋 Détails de la livraison</h3>
                        <ul style="margin-bottom: 0;">
                            <li><strong>Date :</strong> 8 décembre 2025</li>
                            <li><strong>Heure :</strong> 17h00</li>
                            <li><strong>Lieu :</strong> Vous serez contacté pour les détails du point de retrait</li>
                            <?php if ($commande['meme_personne'] == 1): ?>
                                <li><strong>Personne de récupération :</strong> <?= htmlspecialchars($commande['nom_client']) ?></li>
                            <?php else: ?>
                                <li><strong>Personne de récupération :</strong> <?= htmlspecialchars($commande['nom_recup']) ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="section">
                        <h3>📞 Contact</h3>
                        <p>Si vous avez des questions concernant cette livraison, n'hésitez pas à nous contacter :</p>
                        <p>📧 <a href="mailto:vin-contact@ewenevin.fr">vin-contact@ewenevin.fr</a></p>
                    </div>

                    <div class="section" style="text-align: center; background: #f8f9fa;">
                        <p><strong>Merci pour votre confiance et à très bientôt !</strong></p>
                    </div>
                </div>
                
                <div class="footer">
                    <p>🍷 L'équipe Ewen et Vins</p>
                    <p>Email : <a href="mailto:vin-contact@ewenevin.fr" style="color: #fff;">vin-contact@ewenevin.fr</a></p>
                    <p style="margin-top: 15px; font-size: 12px; opacity: 0.8;">
                        Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
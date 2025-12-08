<?php include "app/views/header.php"; ?>

<div class="confirmation-container">
    <div class="confirmation-content">
        <div class="confirmation-header">
            <h1>✅ Commande Confirmée !</h1>
            <h2>⏳ En attente de paiement</h2>
            <p class="confirmation-message">Merci pour votre commande, <?= htmlspecialchars($commande['nom_client']) ?> !</p>
            <p class="confirmation-numero">Numéro de commande : <strong>#<?= $commande['id'] ?></strong></p>
        </div>

        <!-- Notification email -->
        <div class="email-notification">
            <h2>📧 Suivi de Votre Commande</h2>
            <div class="email-details">
                <p><strong>Un email récapitulatif vous a été envoyé à :</strong> <?= htmlspecialchars($commande['email']) ?></p>
                <p><strong>Vous serez informé par email :</strong></p>
                <ul>
                    <li>✅ De la réception de votre paiement</li>
                    <li>📦 Du statut de préparation de votre commande</li>
                    <li>🚚 De la date exacte de livraison début décembre</li>
                    <li>📞 Des informations de récupération</li>
                </ul>
            </div>
        </div>

        <!-- Informations de récupération -->
        <div class="recup-info">
            <h2>📦 Informations de Récupération</h2>
            <div class="recup-details">
                <?php if ($commande['meme_personne'] == 1): ?>
                    <p><strong>🔄 La commande sera récupérée par :</strong> <?= htmlspecialchars($commande['nom_client']) ?> (même personne)</p>
                <?php else: ?>
                    <p><strong>👤 La commande sera récupérée par :</strong> <?= htmlspecialchars($commande['prenom_recup']) ?> <?= htmlspecialchars($commande['nom_recup']) ?></p>
                <?php endif; ?>
                <p><strong>📅 Livraison prévue :</strong> <span style="color: #d35400; font-weight: bold;">Début Décembre 2025</span></p>
            </div>
        </div>

        <div class="paiement-virement">
            <h2>💳 Paiement par Virement Instantané</h2>
            <div class="virement-info">
                <p><strong>Veuillez régler par virement instantané à :</strong></p>
                <div class="virement-details">
                    <p><strong>🏦 IBAN :</strong> FR76 1470 6000 0273 9957 4572 816</p>
                    <p><strong>👤 Bénéficiaire :</strong> Ewen Evin</p>
                    <p><strong>💰 Montant :</strong> <span style="color: #c0392b; font-weight: bold;"><?= number_format($total, 2) ?> €</span></p>
                    <p><strong>🏷️ Référence :</strong> "<?= htmlspecialchars($commande['nom_client']) ?> – Commande : #<?= $commande['id'] ?>"</p>
                </div>
            </div>
            
            <div class="instructions">
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
        </div>

        <!-- Récapitulatif de la commande - Version minimaliste -->
        <div class="recap-commande">
            <h2>📋 Récapitulatif de Votre Commande</h2>
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
                    
                    // Calculs selon le type de produit - MÊME LOGIQUE QUE DANS L'EMAIL
                    if ($est_coffret) {
                        // COFFRETS : Le prix unitaire est pour le coffret complet
                        $sous_total = $prix_unitaire * $prod['quantite'];
                        $conditionnement = "Coffret de " . $bouteilles_par_carton . " bouteille" . ($bouteilles_par_carton > 1 ? 's' : '');
                        $details_quantite = $prod['quantite'] . " coffret" . ($prod['quantite'] > 1 ? 's' : '');
                    }
                    elseif ($est_vin_biere) {
                        // VINS/BIERES : Le prix unitaire est par bouteille, on calcule le prix du carton
                        $prix_carton = $prix_unitaire * $bouteilles_par_carton;
                        $sous_total = $prix_carton * $prod['quantite'];
                        $conditionnement = $bouteilles_par_carton . " bouteille" . ($bouteilles_par_carton > 1 ? 's' : '');
                        $total_bouteilles = $prod['quantite'] * $bouteilles_par_carton;
                        $details_quantite = $prod['quantite'] . " carton" . ($prod['quantite'] > 1 ? 's' : '');
                    }
                    else {
                        // ACCESSOIRES : Le prix unitaire est pour l'unité (ou le lot)
                        $sous_total = $prix_unitaire * $prod['quantite'];
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
                            <strong><?= number_format($sous_total, 2) ?> €</strong>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <p class="total"><strong>💵 Total : <?= number_format($total, 2) ?> €</strong></p>
        </div>

        <div class="confirmation-actions">
            <a href="index.php?page=produits" class="btn btn-primary">🛍️ Retour au Catalogue</a>
            <button onclick="printConfirmation()" class="btn btn-secondary">🖨️ Imprimer cette confirmation</button>
        </div>
    </div>
</div>

<script>
function printConfirmation() {
    window.print();
}

document.addEventListener('DOMContentLoaded', function() {
    if (!sessionStorage.getItem('email_envoye_<?= $commande['id'] ?>')) {
        fetch('index.php?page=envoyer-email-confirmation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'commande_id=<?= $commande['id'] ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Email de confirmation envoyé avec succès');
                sessionStorage.setItem('email_envoye_<?= $commande['id'] ?>', 'true');
            } else {
                console.error('Erreur lors de l\'envoi de l\'email:', data.message);
            }
        })
        .catch(error => {
            console.error('Erreur réseau:', error);
        });
    }
});
</script>

<?php include "app/views/footer.php"; ?>
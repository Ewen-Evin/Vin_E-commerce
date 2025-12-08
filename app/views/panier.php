<?php include "app/views/header.php"; ?>

<h1>Votre Panier</h1>

<?php if (empty($produits)): ?>
    <p>Votre panier est vide.</p>
<?php else: ?>
    <div class="info-panier">
        <p>🛒 <strong>Attention :</strong> Les conditionnements varient selon les produits (cartons, coffrets ou pièces unitaires).</p>
    </div>

    <table class="panier-table">
        <tr>
            <th>Image</th>
        <th>Produit</th>
            <th>Prix unitaire</th>
            <th>Conditionnement</th>
            <th>Quantité</th>
            <th>Sous-total</th>
            <th>Action</th>
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
                // COFFRETS : Le prix unitaire est pour le coffret complet
                $sous_total = $prix_unitaire * $prod['quantite'];
                $conditionnement = "Coffret de " . $bouteilles_par_carton . " bouteille" . ($bouteilles_par_carton > 1 ? 's' : '');
                $info_prix = "par coffret";
                $details_quantite = $prod['quantite'] . " coffret" . ($prod['quantite'] > 1 ? 's' : '');
                $titre_quantite = "Nombre de coffrets";
            }
            elseif ($est_vin_biere) {
                // VINS/BIERES : Le prix unitaire est par bouteille, on calcule le prix du carton
                $prix_carton = $prix_unitaire * $bouteilles_par_carton;
                $sous_total = $prix_carton * $prod['quantite'];
                $conditionnement = $bouteilles_par_carton . " bouteille" . ($bouteilles_par_carton > 1 ? 's' : '');
                $info_prix = "par bouteille";
                $total_bouteilles = $prod['quantite'] * $bouteilles_par_carton;
                $details_quantite = $prod['quantite'] . " carton" . ($prod['quantite'] > 1 ? 's' : '') . " (" . $total_bouteilles . " bouteille" . ($total_bouteilles > 1 ? 's' : '') . ")";
                $titre_quantite = "Nombre de cartons";
            }
            else {
                // ACCESSOIRES : Le prix unitaire est pour l'unité (ou le lot)
                $sous_total = $prix_unitaire * $prod['quantite'];
                $conditionnement = $bouteilles_par_carton > 1 ? "Lot de " . $bouteilles_par_carton . " pièces" : "À l'unité";
                $info_prix = $bouteilles_par_carton > 1 ? "par lot" : "par pièce";
                $total_pieces = $prod['quantite'] * $bouteilles_par_carton;
                $details_quantite = $prod['quantite'] . ($bouteilles_par_carton > 1 ? " lot" . ($prod['quantite'] > 1 ? 's' : '') . " (" . $total_pieces . " pièce" . ($total_pieces > 1 ? 's' : '') . ")" : " pièce" . ($prod['quantite'] > 1 ? 's' : ''));
                $titre_quantite = $bouteilles_par_carton > 1 ? "Nombre de lots" : "Nombre de pièces";
            }
            ?>
            <tr data-id="<?= $prod['id'] ?>" 
                data-prix-unitaire="<?= $prix_unitaire ?>" 
                data-bouteilles-carton="<?= $bouteilles_par_carton ?>"
                data-categorie="<?= $categorie ?>"
                data-type="<?= $est_coffret ? 'coffret' : ($est_vin_biere ? 'vin_biere' : 'accessoire') ?>">
                <td>
                    <img src="public/images/produits/<?= htmlspecialchars($prod['image'] ?? 'default.png') ?>" 
                         alt="<?= htmlspecialchars($prod['nom']) ?>" 
                         class="panier-img">
                </td>
                <td>
                    <?= htmlspecialchars($prod['nom']) ?>
                    <br>
                    <small class="produit-info">
                        <?php if ($est_coffret): ?>
                            🎁 <?= $conditionnement ?>
                        <?php elseif ($est_vin_biere): ?>
                            📦 Carton de <?= $conditionnement ?>
                        <?php else: ?>
                            🔧 <?= $conditionnement ?>
                        <?php endif; ?>
                    </small>
                </td>
                <td class="prix-unitaire">
                    <?= number_format($prix_unitaire, 2) ?> €
                    <br>
                    <small class="prix-info"><?= $info_prix ?></small>
                </td>
                <td class="text-center conditionnement">
                    <?= $conditionnement ?>
                </td>
                <td>
                    <input type="number" 
                           class="update-qty" 
                           data-id="<?= $prod['id'] ?>" 
                           value="<?= $prod['quantite'] ?>" 
                           min="1"
                           title="<?= $titre_quantite ?>">
                    <br>
                    <small class="details-quantite">
                        <?= $details_quantite ?>
                    </small>
                </td>
                <td>
                    <strong class="sous-total"><?= number_format($sous_total, 2) ?> €</strong>
                </td>
                <td>
                    <button type="button" class="remove-item btn btn-danger" data-id="<?= $prod['id'] ?>">
                        🗑 Supprimer
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php
    // Calcul du montant total
    $total_general = 0;
    
    foreach ($produits as $prod) {
        $categorie = $prod['categorie'] ?? '';
        $bouteilles_par_carton = $prod['quantite_par_carton'] ?? 1;
        $prix_unitaire = $prod['prix_unitaire'];
        
        $est_coffret = in_array($categorie, ['Coffrets']);
        $est_vin_biere = in_array($categorie, ['Rouges', 'Blancs', 'Rosés', 'Bulles', 'Bière']) && !$est_coffret;
        
        if ($est_coffret) {
            // Coffrets : prix unitaire direct
            $total_general += $prix_unitaire * $prod['quantite'];
        }
        elseif ($est_vin_biere) {
            // Vins/Bières : prix unitaire × bouteilles par carton × quantité
            $prix_carton = $prix_unitaire * $bouteilles_par_carton;
            $total_general += $prix_carton * $prod['quantite'];
        }
        else {
            // Accessoires : prix unitaire direct
            $total_general += $prix_unitaire * $prod['quantite'];
        }
    }
    ?>

    <div class="panier-resume">
        <div class="resume-item total">
            <strong>Montant total : <span id="total-general"><?= number_format($total_general, 2) ?></span> €</strong>
        </div>
    </div>

    <div class="panier-actions">
        <a href="index.php?page=commande" class="btn btn-primary">Valider ma commande</a>
        <a href="#" id="vider-panier-link" class="btn btn-danger">Vider le panier</a>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mise à jour de la quantité avec gestion des différents types
    document.querySelectorAll('.update-qty').forEach(input => {
        input.addEventListener('change', function() {
            const produitId = this.getAttribute('data-id');
            const nouvelleQuantite = parseInt(this.value);
            
            if (nouvelleQuantite < 1) {
                this.value = 1;
                return;
            }
            
            // Récupérer les données du produit
            const ligneProduit = this.closest('tr');
            const prixUnitaire = parseFloat(ligneProduit.getAttribute('data-prix-unitaire'));
            const bouteillesParCarton = parseInt(ligneProduit.getAttribute('data-bouteilles-carton'));
            const typeProduit = ligneProduit.getAttribute('data-type');
            
            let nouveauSousTotal;
            let detailsQuantite;
            
            if (typeProduit === 'coffret') {
                // COFFRETS : prix unitaire direct
                nouveauSousTotal = prixUnitaire * nouvelleQuantite;
                detailsQuantite = nouvelleQuantite + " coffret" + (nouvelleQuantite > 1 ? 's' : '');
            }
            else if (typeProduit === 'vin_biere') {
                // VINS/BIERES : prix unitaire × bouteilles par carton × quantité
                const prixCarton = prixUnitaire * bouteillesParCarton;
                nouveauSousTotal = prixCarton * nouvelleQuantite;
                const totalBouteilles = bouteillesParCarton * nouvelleQuantite;
                detailsQuantite = nouvelleQuantite + " carton" + (nouvelleQuantite > 1 ? 's' : '') + " (" + totalBouteilles + " bouteille" + (totalBouteilles > 1 ? 's' : '') + ")";
            }
            else {
                // ACCESSOIRES : prix unitaire direct
                nouveauSousTotal = prixUnitaire * nouvelleQuantite;
                const totalPieces = nouvelleQuantite * bouteillesParCarton;
                if (bouteillesParCarton > 1) {
                    detailsQuantite = nouvelleQuantite + " lot" + (nouvelleQuantite > 1 ? 's' : '') + " (" + totalPieces + " pièce" + (totalPieces > 1 ? 's' : '') + ")";
                } else {
                    detailsQuantite = nouvelleQuantite + " pièce" + (nouvelleQuantite > 1 ? 's' : '');
                }
            }
            
            // Mettre à jour l'affichage
            const sousTotalElement = ligneProduit.querySelector('.sous-total');
            sousTotalElement.textContent = nouveauSousTotal.toFixed(2) + ' €';
            
            const detailsQuantiteElement = ligneProduit.querySelector('.details-quantite');
            detailsQuantiteElement.textContent = detailsQuantite;
            
            // Mettre à jour le total général
            mettreAJourTotalGeneral();
            
            // Mettre à jour le panier en session via AJAX
            fetch('index.php?page=update-panier', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + produitId + '&quantite=' + nouvelleQuantite
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Erreur lors de la mise à jour du panier');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        });
    });
    
    // Fonction pour mettre à jour le total général
    function mettreAJourTotalGeneral() {
        let total = 0;
        
        document.querySelectorAll('tr[data-id]').forEach(ligne => {
            const sousTotalText = ligne.querySelector('.sous-total').textContent;
            const sousTotal = parseFloat(sousTotalText.replace(' €', ''));
            total += sousTotal;
        });
        
        document.getElementById('total-general').textContent = total.toFixed(2);
    }

    // Gestion du bouton "Vider le panier"
    const viderPanierBtn = document.getElementById('vider-panier-link');
    if (viderPanierBtn) {
        viderPanierBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: "Vider le panier ?",
                text: "Cette action supprimera tous les produits de votre panier",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Oui, vider le panier",
                cancelButtonText: "Annuler",
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "index.php?page=vider-panier";
                }
            });
        });
    }
});
</script>

<?php include "app/views/footer.php"; ?>
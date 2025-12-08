<?php include "app/views/header.php"; ?>

<div class="commande-container">
    <h1>Finaliser ma Commande</h1>
    
    <div class="commande-content">
        <!-- Récapitulatif du panier -->
        <div class="recap-panier">
            <h2>Récapitulatif de votre commande</h2>
            
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
                        $total_bouteilles = $prod['quantite'] * $bouteilles_par_carton;
                        $details_quantite = $prod['quantite'] . " coffret" . ($prod['quantite'] > 1 ? 's' : '');
                    }
                    elseif ($est_vin_biere) {
                        // VINS/BIERES : Le prix unitaire est par bouteille, on calcule le prix du carton
                        $prix_carton = $prix_unitaire * $bouteilles_par_carton;
                        $sous_total = $prix_carton * $prod['quantite'];
                        $conditionnement = $bouteilles_par_carton . " bouteille" . ($bouteilles_par_carton > 1 ? 's' : '');
                        $info_prix = "par bouteille";
                        $total_bouteilles = $prod['quantite'] * $bouteilles_par_carton;
                        $details_quantite = $prod['quantite'] . " carton" . ($prod['quantite'] > 1 ? 's' : '');
                    }
                    else {
                        // ACCESSOIRES : Le prix unitaire est pour l'unité (ou le lot)
                        $sous_total = $prix_unitaire * $prod['quantite'];
                        $conditionnement = $bouteilles_par_carton > 1 ? "Lot de " . $bouteilles_par_carton . " pièces" : "À l'unité";
                        $info_prix = $bouteilles_par_carton > 1 ? "par lot" : "par pièce";
                        $total_pieces = $prod['quantite'] * $bouteilles_par_carton;
                        $details_quantite = $prod['quantite'] . ($bouteilles_par_carton > 1 ? " lot" . ($prod['quantite'] > 1 ? 's' : '') : " pièce" . ($prod['quantite'] > 1 ? 's' : ''));
                    }
                    ?>
                    <tr>
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
                            <?= $details_quantite ?>
                            <?php if ($est_vin_biere): ?>
                                <br>
                                <small class="details-quantite">
                                    (<?= $total_bouteilles ?> bouteille<?= $total_bouteilles > 1 ? 's' : '' ?>)
                                </small>
                            <?php elseif ($est_accessoire && $bouteilles_par_carton > 1): ?>
                                <br>
                                <small class="details-quantite">
                                    (<?= $total_pieces ?> pièce<?= $total_pieces > 1 ? 's' : '' ?>)
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= number_format($sous_total, 2) ?> €</strong>
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
                    <strong>Montant total : <?= number_format($total_general, 2) ?> €</strong>
                </div>
            </div>
            
            <!-- Bouton Retour au panier -->
            <a href="index.php?page=panier" class="btn-retour-panier">← Retour au panier</a>
        </div>

        <!-- Formulaire de coordonnées -->
        <div class="form-coordonnees">
            <h2>Vos coordonnées</h2>
            
            <form method="POST" action="index.php?page=traiter-commande">
                <div class="form-group">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($_SESSION['user_nom'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="prenom">Prénom *</label>
                    <input type="text" id="prenom" name="prenom" required value="<?= htmlspecialchars($_SESSION['user_prenom'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="telephone">Téléphone *</label>
                    <input type="tel" id="telephone" name="telephone" required>
                </div>
                
                <div class="form-group">
                    <label for="adresse">Adresse *</label>
                    <textarea id="adresse" name="adresse" required rows="3" placeholder="Votre adresse complète..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="message">Message (facultatif)</label>
                    <textarea id="message" name="message" rows="3" placeholder="Informations complémentaires..."></textarea>
                </div>
                
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="meme_personne" id="meme_personne" value="1" checked>
                        <span class="checkmark"></span>
                        La commande sera récupérée par la même personne
                    </label>
                </div>
                
                <div id="infos-recup" style="display: none;">
                    <h3>Personne qui récupérera la commande</h3>
                    <div class="form-group">
                        <label for="nom_recup">Nom *</label>
                        <input type="text" id="nom_recup" name="nom_recup">
                    </div>
                    
                    <div class="form-group">
                        <label for="prenom_recup">Prénom *</label>
                        <input type="text" id="prenom_recup" name="prenom_recup">
                    </div>
                </div>
                
                <button type="submit" class="btn-commande">Confirmer la commande</button>
            </form>
        </div>
    </div>
</div>

<script>
// Gestion de l'affichage des infos de récupération
document.getElementById('meme_personne').addEventListener('change', function() {
    const infosRecup = document.getElementById('infos-recup');
    const inputsRecup = infosRecup.querySelectorAll('input[type="text"]');
    
    if (this.checked) {
        infosRecup.style.display = 'none';
        // Rendre les champs non obligatoires
        inputsRecup.forEach(input => {
            input.removeAttribute('required');
        });
    } else {
        infosRecup.style.display = 'block';
        // Rendre les champs obligatoires
        inputsRecup.forEach(input => {
            input.setAttribute('required', 'required');
        });
    }
});

// Validation du formulaire
document.querySelector('form').addEventListener('submit', function(e) {
    const memePersonne = document.getElementById('meme_personne').checked;
    
    if (!memePersonne) {
        const nomRecup = document.getElementById('nom_recup').value.trim();
        const prenomRecup = document.getElementById('prenom_recup').value.trim();
        
        if (!nomRecup || !prenomRecup) {
            e.preventDefault();
            alert('Veuillez remplir les informations de la personne qui récupérera la commande.');
            return false;
        }
    }
    
    return true;
});
</script>

<?php include "app/views/footer.php"; ?>
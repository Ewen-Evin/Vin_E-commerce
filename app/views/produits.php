<?php include "app/views/header.php"; ?>

<h1>Catalogue des Produits</h1>
<div class="produits-container">
    <?php foreach ($produits as $p): ?>
        <?php
        // Déterminer le type de produit
        $categorie = $p['categorie'] ?? '';
        $est_accessoire = in_array($categorie, ['Accessoires']);
        $est_coffret = in_array($categorie, ['Coffrets']);
        $est_vin_biere = in_array($categorie, ['Rouges', 'Blancs', 'Rosés', 'Bulles', 'Bière']) && !$est_coffret;

        // Configuration selon le type
        if ($est_coffret) {
            $card_class = 'coffret';
            $prix_affichage = number_format($p['prix_unitaire'], 2) . ' €';
            $info_prix = 'Coffret complet';
            $conditionnement = 'Coffret de ' . $p['quantite_par_carton'] . ' bouteille' . ($p['quantite_par_carton'] > 1 ? 's' : '');
        } elseif ($est_accessoire) {
            $card_class = 'accessoire';
            $prix_affichage = number_format($p['prix_unitaire'], 2) . ' €';
            
            // CORRECTION ICI : Vérifier si c'est un lot ou une pièce unitaire
            if ($p['quantite_par_carton'] > 1) {
                $conditionnement = 'Lot de ' . $p['quantite_par_carton'] . ' pièces';
                $info_prix = 'par lot';
            } else {
                $conditionnement = 'À l\'unité';
                $info_prix = 'par pièce';
            }
        } else {
            $card_class = '';
            $prix_carton = $p['prix_unitaire'] * $p['quantite_par_carton'];
            $prix_affichage = number_format($prix_carton, 2) . ' €';
            $info_prix = '(' . number_format($p['prix_unitaire'], 2) . ' € l\'unité)';
            $conditionnement = 'Carton de ' . $p['quantite_par_carton'] . ' bouteille' . ($p['quantite_par_carton'] > 1 ? 's' : '');
        }
        ?>
        
        <div class="produit-card <?= $card_class ?>">
            <div class="image-container">
                <?php if (!empty($p['image'])): ?>
                    <img src="public/images/produits/<?= htmlspecialchars($p['image']) ?>" 
                         alt="<?= htmlspecialchars($p['nom']) ?>" 
                         class="produit-img">
                <?php else: ?>
                    <img src="public/images/produits/default.png" 
                         alt="Image non disponible" 
                         class="produit-img">
                <?php endif; ?>
            </div>

            <h2><?= htmlspecialchars($p['nom']) ?></h2>
            
            <?php if (!$est_coffret): ?>
                <?php 
                // Description avec "Voir plus" pour tous sauf coffrets
                $description = htmlspecialchars($p['description']);
                $isLong = strlen($p['description']) > 150;
                ?>
                
                <?php if ($isLong): ?>
                    <div class="product-description">
                        <div class="short-desc"><?= nl2br(htmlspecialchars(substr($p['description'], 0, 150))) ?>...</div>
                        <div class="full-desc" style="display:none;"><?= nl2br($description) ?></div>
                        <button type="button" class="toggle-desc" onclick="toggleDescription(this)">Voir plus</button>
                    </div>
                <?php else: ?>
                    <p class="product-desc"><?= nl2br($description) ?></p>
                <?php endif; ?>
            <?php else: ?>
                <!-- Pour les coffrets : bouton pour ouvrir la description dans SweetAlert -->
                <div class="coffret-description">
                    <button type="button" class="btn-coffret-desc" 
                            onclick="showCoffretDescription(
                                '<?= htmlspecialchars(addslashes($p['nom'])) ?>',
                                `<?= addslashes($p['description']) ?>`,
                                <?= $p['id'] ?>,
                                <?= $p['prix_unitaire'] ?>,
                                '<?= htmlspecialchars($p['image']) ?>'
                            )">
                        📖 Voir la description
                    </button>
                </div>
            <?php endif; ?>
            
            <!-- Affichage des prix selon le type de produit -->
            <div class="product-prices">
                <p class="carton-price"><strong><?= $prix_affichage ?></strong></p>
                
                <?php if ($est_accessoire): ?>
                    <p class="carton-info"><?= $conditionnement ?></p>
                    <p class="unit-price"><?= $info_prix ?></p>
                <?php elseif ($est_coffret): ?>
                    <p class="carton-info"><?= $conditionnement ?></p>
                <?php else: ?>
                    <p class="unit-price"><?= $info_prix ?></p>
                    <p class="carton-info"><?= $conditionnement ?></p>
                <?php endif; ?>
            </div>
            
            <?php if (!$est_coffret): ?>
                <!-- Formulaire pour produits normaux et accessoires -->
                <form class="add-to-cart-form" data-id="<?= $p['id'] ?>">
                    <input type="number" name="quantite" value="1" min="1" max="10" 
                        title="<?= $est_accessoire ? 'Nombre de ' . ($p['quantite_par_carton'] > 1 ? 'lots' : 'pièces') : 'Nombre de cartons' ?>">
                    <span class="quantity-label">
                        <?php if ($est_accessoire): ?>
                            <?= $p['quantite_par_carton'] > 1 ? 'lot(s)' : 'pièce(s)' ?>
                        <?php else: ?>
                            carton(s)
                        <?php endif; ?>
                    </span>
                    <button type="submit">Ajouter au panier</button>
                </form>
            <?php else: ?>
                <!-- Pour les coffrets, afficher un bouton qui ouvre la modal -->
                <div style="padding: 15px; text-align: center;">
                    <button type="button" class="btn-coffret-add" 
                            onclick="showCoffretDescription(
                                '<?= htmlspecialchars(addslashes($p['nom'])) ?>',
                                `<?= addslashes($p['description']) ?>`,
                                <?= $p['id'] ?>,
                                <?= $p['prix_unitaire'] ?>,
                                '<?= htmlspecialchars($p['image']) ?>'
                            )"
                            style="background: #8B0000; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; width: 100%; font-weight: 600;">
                        Ajouter au panier
                    </button>
                </div>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Variable globale pour suivre les requêtes en cours
let pendingRequests = new Set();

// Fonction pour ajouter au panier avec protection contre les doublons
function addToCart(productId, quantity) {
    // Créer un identifiant unique pour cette requête
    const requestId = `${productId}-${Date.now()}`;
    
    // Vérifier si une requête similaire est déjà en cours
    if (pendingRequests.has(productId)) {
        console.log('Requête déjà en cours pour ce produit:', productId);
        return Promise.reject('Requête déjà en cours');
    }
    
    pendingRequests.add(productId);
    
    console.log('Ajout au panier - Produit:', productId, 'Quantité:', quantity);
    
    return fetch('index.php?page=panier-ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id_produit=${productId}&quantite=${quantity}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau');
        }
        return response.json();
    })
    .then(data => {
        console.log('Réponse du serveur:', data);
        return data;
    })
    .finally(() => {
        // Retirer la requête de l'ensemble après un délai
        setTimeout(() => {
            pendingRequests.delete(productId);
        }, 1000);
    });
}

// Fonction pour afficher la confirmation
function showCartConfirmation() {
    Swal.fire({
        title: 'Produit ajouté au panier !',
        text: 'Que souhaitez-vous faire ?',
        icon: 'success',
        showCancelButton: true,
        confirmButtonText: '🛒 Voir le panier',
        cancelButtonText: '🛍️ Continuer mes achats',
        confirmButtonColor: '#8B0000',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'index.php?page=panier';
        }
    });
}

// Fonction pour les coffrets
function showCoffretDescription(nom, description, id, prix, image) {
    const formHtml = `
        <div class="coffret-modal">
            <div class="coffret-modal-image">
                <img src="public/images/produits/${image}" alt="${nom}" style="max-width: 200px; max-height: 300px; object-fit: contain; border-radius: 8px;">
            </div>
            <div class="coffret-modal-content">
                <h3 style="color: #5a0c24; margin-bottom: 15px;">${nom}</h3>
                <div class="coffret-description-text" style="line-height: 1.6; color: #333; margin-bottom: 15px;">
                    ${description.replace(/\n/g, '<br>')}
                </div>
                <div class="coffret-modal-price" style="font-size: 1.4em; color: #8B0000; font-weight: bold; text-align: center; margin: 15px 0;">
                    <strong>${parseFloat(prix).toFixed(2)} €</strong>
                </div>
                <div style="margin-top: 20px; text-align: center;">
                    <div style="display: inline-flex; gap: 10px; align-items: center; justify-content: center; margin-bottom: 15px;">
                        <input type="number" id="modal-quantity" value="1" min="1" max="10" 
                               style="width: 80px; padding: 8px; text-align: center; border: 1px solid #ddd; border-radius: 4px;">
                        <span style="color: #666;">coffret(s)</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    Swal.fire({
        title: 'Description du coffret',
        html: formHtml,
        width: 700,
        showCancelButton: true,
        confirmButtonText: 'Ajouter au panier',
        cancelButtonText: 'Fermer',
        confirmButtonColor: '#8B0000',
        cancelButtonColor: '#6c757d',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const quantity = document.getElementById('modal-quantity').value;
            return addToCart(id, parseInt(quantity))
                .then(data => {
                    if (!data.success) {
                        throw new Error('Erreur lors de l\'ajout au panier');
                    }
                    return data;
                })
                .catch(error => {
                    Swal.showValidationMessage(`Erreur: ${error.message}`);
                });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.close();
            showCartConfirmation();
            updateCartCount();
        }
    });
}

// Fonction pour basculer la description
function toggleDescription(button) {
    const container = button.parentElement;
    const shortDesc = container.querySelector('.short-desc');
    const fullDesc = container.querySelector('.full-desc');
    
    if (fullDesc.style.display === 'none') {
        shortDesc.style.display = 'none';
        fullDesc.style.display = 'block';
        button.textContent = 'Voir moins';
    } else {
        shortDesc.style.display = 'block';
        fullDesc.style.display = 'none';
        button.textContent = 'Voir plus';
    }
}

// Fonction pour mettre à jour le compteur du panier
function updateCartCount() {
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        const currentCount = parseInt(cartCountElement.textContent) || 0;
        cartCountElement.textContent = currentCount + 1;
    }
}

// Gestion des formulaires d'ajout au panier (uniquement pour produits normaux)
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.add-to-cart-form');
    
    forms.forEach(form => {
        // Supprimer tous les écouteurs existants
        form.replaceWith(form.cloneNode(true));
    });
    
    // Réattacher les écouteurs sur les nouveaux éléments
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation(); // Empêcher la propagation
            
            const productId = this.getAttribute('data-id');
            const quantityInput = this.querySelector('input[name="quantite"]');
            const quantity = parseInt(quantityInput.value);
            
            // Désactiver le formulaire
            this.style.opacity = '0.6';
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Ajout...';
            
            console.log('Soumission formulaire - Produit:', productId, 'Quantité:', quantity);
            
            addToCart(productId, quantity)
                .then(data => {
                    if (data.success) {
                        showCartConfirmation();
                        updateCartCount();
                        
                        // Réinitialiser la quantité
                        quantityInput.value = 1;
                    } else {
                        Swal.fire({
                            title: 'Erreur',
                            text: 'Erreur lors de l\'ajout au panier',
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    if (error !== 'Requête déjà en cours') {
                        Swal.fire({
                            title: 'Erreur',
                            text: 'Erreur de connexion',
                            icon: 'error'
                        });
                    }
                })
                .finally(() => {
                    // Réactiver le formulaire après un délai
                    setTimeout(() => {
                        this.style.opacity = '1';
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }, 1500);
                });
        });
    });
});

// Empêcher les doubles clics sur toute la page
document.addEventListener('click', function(e) {
    if (e.target.type === 'submit' || e.target.closest('button[type="submit"]')) {
        const button = e.target.type === 'submit' ? e.target : e.target.closest('button[type="submit"]');
        if (button.disabled) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }
}, true);
</script>

<?php include "app/views/footer.php"; ?>
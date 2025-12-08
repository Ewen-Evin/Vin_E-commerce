<?php include "app/views/header.php"; ?>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="admin-container">
    <h1>📋 Gestion des Commandes</h1>
    
    <div class="admin-welcome">
        <a href="index.php?page=admin" class="btn btn-secondary mb-3">
            <span class="material-icons">arrow_back</span> Retour à l'administration
        </a>
        
        <?php if (isset($_GET['email'])): ?>
            <a href="index.php?page=admin-commandes" class="btn btn-outline-secondary mb-3">
                <span class="material-icons">clear</span> Retirer le filtre
            </a>
            <p class="mb-3">Filtre actif : <strong><?= htmlspecialchars($_GET['email']) ?></strong></p>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message'] ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
    </div>

    <div class="admin-section">
        <h3>📊 Statistiques des Commandes</h3>
        <div class="row text-center">
            <div class="col-md-3">
                <div class="stat-card bg-primary text-white p-3 rounded">
                    <h4><?= $stats['total_commandes'] ?? 0 ?></h4>
                    <small>Commandes totales</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-success text-white p-3 rounded">
                    <h4><?= number_format($stats['chiffre_affaires'] ?? 0, 2, ',', ' ') ?> €</h4>
                    <small>Chiffre d'affaires</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-info text-white p-3 rounded">
                    <h4><?= number_format($stats['panier_moyen'] ?? 0, 2, ',', ' ') ?> €</h4>
                    <small>Panier moyen</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-warning text-white p-3 rounded">
                    <h4><?= $stats['clients_uniques'] ?? 0 ?></h4>
                    <small>Clients uniques</small>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-section">
        <?php if (empty($commandes)): ?>
            <div class="alert alert-info text-center">
                <span class="material-icons">info</span>
                Aucune commande trouvée dans la base de données.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Contact</th>
                            <th>Date</th>
                            <th>Produits Commandés</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commandes as $commande): 
                            $db = Database::getConnection();
                            $query = "SELECT p.nom, p.categorie, p.quantite_par_carton, cp.quantite, cp.prix_unitaire 
                                      FROM vin_commandes_produits cp 
                                      JOIN vin_produits p ON cp.id_produit = p.id 
                                      WHERE cp.id_commande = ?";
                            $stmt = $db->prepare($query);
                            $stmt->execute([$commande['id']]);
                            $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <tr>
                            <td>
                                <strong>#<?= $commande['id'] ?></strong>
                            </td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($commande['nom_client']) ?></strong>
                                    <?php if ($commande['nom_recup']): ?>
                                        <br>
                                        <small class="text-muted">
                                            Récupération: <?= htmlspecialchars($commande['nom_recup']) ?> 
                                            <?= $commande['prenom_recup'] ? htmlspecialchars($commande['prenom_recup']) : '' ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <a href="mailto:<?= htmlspecialchars($commande['email']) ?>" class="text-primary d-block">
                                        <?= htmlspecialchars($commande['email']) ?>
                                    </a>
                                    <?php if ($commande['telephone']): ?>
                                        <a href="tel:<?= htmlspecialchars($commande['telephone']) ?>" class="text-primary">
                                            <?= htmlspecialchars($commande['telephone']) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick='viewOrderProducts(<?= $commande['id'] ?>)'>
                                    Voir les produits (<?= count($produits) ?>)
                                </button>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        <?= $commande['total_articles'] ?> article(s) total
                                    </small>
                                </div>
                            </td>
                            <td>
                                <strong class="text-success"><?= number_format($commande['total'], 2, ',', ' ') ?> €</strong>
                            </td>
                            <td>
                                <form method="POST" action="index.php?page=admin-update-commande" class="d-inline">
                                    <input type="hidden" name="action" value="update_statut">
                                    <input type="hidden" name="id_commande" value="<?= $commande['id'] ?>">
                                    <select name="statut" class="form-select form-select-sm statut-select" 
                                            onchange="this.form.submit()" 
                                            data-commande-id="<?= $commande['id'] ?>">
                                        <option value="en_attente" <?= $commande['statut'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                        <option value="payee" <?= $commande['statut'] === 'payee' ? 'selected' : '' ?>>Payée</option>
                                        <option value="expediee" <?= $commande['statut'] === 'expediee' ? 'selected' : '' ?>>Expédiée</option>
                                        <option value="annulee" <?= $commande['statut'] === 'annulee' ? 'selected' : '' ?>>Annulée</option>
                                    </select>
                                </form>
                                <div class="statut-badge mt-1">
                                    <?php
                                    $statutColors = [
                                        'en_attente' => 'bg-warning',
                                        'payee' => 'bg-info',
                                        'expediee' => 'bg-success',
                                        'annulee' => 'bg-danger'
                                    ];
                                    $statutTexts = [
                                        'en_attente' => 'En attente',
                                        'payee' => 'Payée',
                                        'expediee' => 'Expédiée',
                                        'annulee' => 'Annulée'
                                    ];
                                    ?>
                                    <span class="badge <?= $statutColors[$commande['statut']] ?>">
                                        <?= $statutTexts[$commande['statut']] ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="index.php?page=admin-clients" 
                                       class="btn btn-info"
                                       title="Voir le client">
                                        <span class="material-icons">person</span>
                                    </a>
                                    
                                    <a href="mailto:<?= htmlspecialchars($commande['email']) ?>" 
                                       class="btn btn-success"
                                       title="Envoyer un email">
                                        <span class="material-icons">email</span>
                                    </a>
                                    
                                    <button type="button" class="btn btn-warning" 
                                            onclick='printOrder(<?= $commande['id'] ?>)'
                                            title="Imprimer">
                                        <span class="material-icons">print</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="admin-footer">
        <a href="index.php?page=admin" class="btn btn-secondary">
            <span class="material-icons">arrow_back</span> Retour à l'administration
        </a>
        <a href="index.php?page=logout" class="btn btn-secondary">
            <span class="material-icons">logout</span> Se déconnecter
        </a>
    </div>
</div>

<script>
function viewOrderProducts(orderId) {
    fetch(`index.php?page=ajax-commande-produits&id=${orderId}`)
        .then(response => response.json())
        .then(produits => {
            let html = `<div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Catégorie</th>
                            <th>Conditionnement</th>
                            <th>Quantité</th>
                            <th>Prix unitaire</th>
                            <th>Sous-total</th>
                        </tr>
                    </thead>
                    <tbody>`;
            
            let total = 0;
            produits.forEach(produit => {
                const prixUnitaire = parseFloat(produit.prix_unitaire);
                const quantite = parseInt(produit.quantite);
                const bouteillesParCarton = parseInt(produit.quantite_par_carton);
                const categorie = produit.categorie;
                
                let sousTotal = 0;
                let conditionnement = '';
                
                const estAccessoire = categorie === 'Accessoires';
                const estCoffret = categorie === 'Coffrets';
                const estVinBiere = ['Rouges', 'Blancs', 'Rosés', 'Bulles', 'Bière'].includes(categorie) && !estCoffret;
                
                if (estCoffret) {
                    sousTotal = prixUnitaire * quantite;
                    conditionnement = "Coffret de " + bouteillesParCarton + " bouteille" + (bouteillesParCarton > 1 ? 's' : '');
                }
                else if (estVinBiere) {
                    const prixCarton = prixUnitaire * bouteillesParCarton;
                    sousTotal = prixCarton * quantite;
                    conditionnement = "Carton de " + bouteillesParCarton + " bouteille" + (bouteillesParCarton > 1 ? 's' : '');
                }
                else {
                    sousTotal = prixUnitaire * quantite;
                    conditionnement = bouteillesParCarton > 1 ? "Lot de " + bouteillesParCarton + " pièces" : "À l'unité";
                }
                
                total += sousTotal;
                
                html += `<tr>
                    <td>${produit.nom}</td>
                    <td>${categorie}</td>
                    <td>${conditionnement}</td>
                    <td>${quantite}</td>
                    <td>${prixUnitaire.toFixed(2).replace('.', ',')} €</td>
                    <td><strong>${sousTotal.toFixed(2).replace('.', ',')} €</strong></td>
                </tr>`;
            });
            
            html += `</tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">Total commande:</th>
                            <th><strong>${total.toFixed(2).replace('.', ',')} €</strong></th>
                        </tr>
                    </tfoot>
                </table>
            </div>`;
            
            Swal.fire({
                title: `Produits de la commande #${orderId}`,
                html: html,
                width: '1500px',
                showConfirmButton: false,
                showCloseButton: true
            });
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Erreur', 'Impossible de charger les produits de la commande', 'error');
        });
}

function printOrder(orderId) {
    const printWindow = window.open(`index.php?page=commande-print&id=${orderId}`, '_blank');
    printWindow.onafterprint = function() {
        printWindow.close();
    };
}

function confirmDeleteOrder(orderId, clientName) {
    const div = document.createElement('div');
    div.textContent = clientName;
    const escapedName = div.innerHTML;
    
    Swal.fire({
        title: 'Confirmer la suppression',
        html: `Êtes-vous sûr de vouloir supprimer la commande #${orderId} de <strong>"${escapedName}"</strong> ?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'index.php?page=admin-commandes';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_order';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = orderId;
            
            form.appendChild(actionInput);
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const statutSelects = document.querySelectorAll('.statut-select');
    statutSelects.forEach(select => {
        select.addEventListener('change', function() {
            const commandeId = this.getAttribute('data-commande-id');
            const newStatut = this.value;
            
            const badge = this.closest('td').querySelector('.statut-badge .badge');
            const statutColors = {
                'en_attente': 'bg-warning',
                'payee': 'bg-info',
                'expediee': 'bg-success',
                'annulee': 'bg-danger'
            };
            const statutTexts = {
                'en_attente': 'En attente',
                'payee': 'Payée',
                'expediee': 'Expédiée',
                'annulee': 'Annulée'
            };
            
            badge.className = `badge ${statutColors[newStatut]}`;
            badge.textContent = statutTexts[newStatut];
        });
    });
});
</script>

<style>
.stat-card h4 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: bold;
}

.badge {
    padding: 6px 10px;
    font-size: 0.85em;
}

.btn-group-sm .btn {
    padding: 6px 10px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.material-icons {
    vertical-align: middle;
    font-size: 18px;
}

.table th {
    background: #5a0c24;
    color: white;
    border: none;
    padding: 12px 8px;
    font-weight: 600;
    text-align: center;
}

.table td {
    padding: 12px 8px;
    vertical-align: middle;
}

.statut-select {
    min-width: 120px;
    cursor: pointer;
}

.form-select-sm {
    padding: 4px 8px;
    font-size: 0.85rem;
}

.btn-outline-primary {
    border: 1px solid #5a0c24;
    color: #5a0c24;
}

.btn-outline-primary:hover {
    background: #5a0c24;
    color: white;
}
</style>

<?php include "app/views/footer.php"; ?>
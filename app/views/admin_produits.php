<?php
if (!isset($_SESSION['admin'])) {
    header("Location: index.php?page=login");
    exit;
}
?>

<?php include "app/views/header.php"; ?>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="admin-container">
    <h1>📦 Gestion des Produits</h1>
    
    <div class="admin-welcome">
        <a href="index.php?page=admin" class="btn btn-secondary mb-3">
            <span class="material-icons">arrow_back</span> Retour à l'administration
        </a>
    </div>

    <!-- Tableau de gestion des produits -->
    <div class="admin-section">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Référence</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Catégorie</th>
                        <th>Prix Unitaire</th>
                        <th>Qté/Carton</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Ligne pour ajouter un nouveau produit -->
                    <tr class="add-product-form">
                        <form method="post" enctype="multipart/form-data" id="addProductForm">
                            <input type="hidden" name="action" value="add_product">
                            <td>
                                <input type="file" name="image" accept="image/*" class="form-control form-control-sm" style="min-width: 120px;">
                            </td>
                            <td>
                                <input type="text" name="reference" class="form-control form-control-sm" placeholder="Référence" required style="min-width: 100px;">
                            </td>
                            <td>
                                <input type="text" name="nom" class="form-control form-control-sm" placeholder="Nom" required style="min-width: 120px;">
                            </td>
                            <td>
                                <textarea name="description" class="form-control form-control-sm description-input" placeholder="Description" rows="3"></textarea>
                            </td>
                            <td>
                                <select name="categorie" class="form-control form-control-sm" style="min-width: 120px;">
                                    <option value="Rouges">Rouges</option>
                                    <option value="Blancs">Blancs</option>
                                    <option value="Rosés">Rosés</option>
                                    <option value="Bulles">Bulles</option>
                                    <option value="Bière">Bière</option>
                                    <option value="Coffrets">Coffrets</option>
                                    <option value="Accessoires">Accessoires</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="prix_unitaire" step="0.01" min="0" class="form-control form-control-sm" placeholder="Prix" required style="min-width: 100px;">
                            </td>
                            <td>
                                <input type="number" name="quantite_par_carton" min="1" class="form-control form-control-sm" value="1" required style="min-width: 80px;">
                            </td>
                            <td>
                                <button type="submit" class="btn btn-success btn-sm">
                                    <span class="material-icons">add</span> Ajouter
                                </button>
                            </td>
                        </form>
                    </tr>

                    <!-- Liste des produits existants -->
                    <?php foreach ($produits as $produit): ?>
                    <tr>
                        <td>
                            <?php if ($produit['image']): ?>
                                <img src="public/images/produits/<?= $produit['image'] ?>" alt="<?= $produit['nom'] ?>" class="product-image" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                                <div class="image-placeholder">
                                    <span class="material-icons">image</span>
                                </div>
                            <?php endif; ?>
                        </td>
                        
                        <td><?= htmlspecialchars($produit['reference']) ?></td>
                        
                        <td><?= htmlspecialchars($produit['nom']) ?></td>
                        
                        <td>
                            <?php 
                                $desc = htmlspecialchars($produit['description']);
                                if (strlen($desc) > 50) {
                                    echo '<span title="' . $desc . '">' . substr($desc, 0, 50) . '...</span>';
                                } else {
                                    echo $desc;
                                }
                            ?>
                        </td>
                        
                        <td><?= htmlspecialchars($produit['categorie']) ?></td>
                        
                        <td><?= number_format($produit['prix_unitaire'], 2, ',', ' ') ?> €</td>
                        
                        <td><?= $produit['quantite_par_carton'] ?></td>
                        
                        <td>
                            <div class="btn-group btn-group-sm">
                                <!-- Bouton Modifier -->
                                <button type="button" class="btn btn-warning" 
                                        onclick='editProduct(<?= json_encode([
                                            "id" => $produit["id"],
                                            "reference" => $produit["reference"],
                                            "nom" => $produit["nom"],
                                            "description" => $produit["description"],
                                            "categorie" => $produit["categorie"],
                                            "prix_unitaire" => $produit["prix_unitaire"],
                                            "quantite_par_carton" => $produit["quantite_par_carton"],
                                            "image" => $produit["image"]
                                        ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                    <span class="material-icons" style="font-size: 16px;">edit</span>
                                </button>
                                
                                <!-- Bouton Supprimer -->
                                <button type="button" class="btn btn-danger" 
                                        onclick='confirmDelete(<?= $produit["id"] ?>, <?= json_encode($produit["nom"], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                    <span class="material-icons" style="font-size: 16px;">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
function editProduct(data) {
    console.log('editProduct called with:', data);
    
    const escapeHtml = (text) => {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    };
    
    const formHtml = `
        <div id="editProductHeader" style="display: flex; justify-content: flex-end; position: absolute; top: 10px; right: 10px; z-index: 10;">
            <button type="button" id="closeEditProduct" style="background: none; border: none; font-size: 2rem; cursor: pointer; line-height: 1;" aria-label="Fermer">&times;</button>
        </div>
        <form method="POST" action="" enctype="multipart/form-data" style="margin-top: 30px;">
            <input type="hidden" name="action" value="update_product">
            <input type="hidden" name="id" value="${escapeHtml(data.id)}">
            
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="form-label">Référence</label>
                    <input type="text" name="reference" class="form-control" value="${escapeHtml(data.reference)}" required>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" value="${escapeHtml(data.nom)}" required>
                </div>
            </div>
            
            <div class="mb-2">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">${escapeHtml(data.description)}</textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="form-label">Catégorie</label>
                    <select name="categorie" class="form-control">
                        <option value="Rouges" ${data.categorie === 'Rouges' ? 'selected' : ''}>Rouges</option>
                        <option value="Blancs" ${data.categorie === 'Blancs' ? 'selected' : ''}>Blancs</option>
                        <option value="Rosés" ${data.categorie === 'Rosés' ? 'selected' : ''}>Rosés</option>
                        <option value="Bulles" ${data.categorie === 'Bulles' ? 'selected' : ''}>Bulles</option>
                        <option value="Bière" ${data.categorie === 'Bière' ? 'selected' : ''}>Bière</option>
                        <option value="Coffrets" ${data.categorie === 'Coffrets' ? 'selected' : ''}>Coffrets</option>
                        <option value="Accessoires" ${data.categorie === 'Accessoires' ? 'selected' : ''}>Accessoires</option>
                    </select>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Image actuelle</label>
                    ${data.image ? `<div>
                        <img src="public/images/produits/${escapeHtml(data.image)}" alt="${escapeHtml(data.nom)}" style="width: 50px; height: 50px; object-fit: cover;">
                        <small class="text-muted d-block">${escapeHtml(data.image)}</small>
                    </div>` : '<div class="text-muted">Aucune image</div>'}
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="form-label">Nouvelle image</label>
                    <input type="file" name="image" accept="image/*" class="form-control">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Prix Unitaire (€)</label>
                    <input type="number" name="prix_unitaire" step="0.01" min="0" value="${data.prix_unitaire}" class="form-control" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Qté/Carton</label>
                    <input type="number" name="quantite_par_carton" min="1" value="${data.quantite_par_carton}" class="form-control" required>
                </div>
            </div>
            
            <div class="text-end mt-3">
                <button type="button" class="btn btn-secondary me-2" onclick="Swal.close()">Annuler</button>
                <button type="submit" class="btn btn-success">Enregistrer</button>
            </div>
        </form>
    `;
    
    Swal.fire({
        title: '<div style="position:relative;">Modifier le produit</div>',
        html: formHtml,
        width: '600px',
        showConfirmButton: false,
        didOpen: () => {
            document.getElementById('closeEditProduct').onclick = () => Swal.close();
        }
    });
}

function confirmDelete(id, nom) {
    const div = document.createElement('div');
    div.textContent = nom;
    const escapedNom = div.innerHTML;
    
    Swal.fire({
        title: 'Confirmer la suppression',
        html: 'Êtes-vous sûr de vouloir supprimer le produit <strong>"' + escapedNom + '"</strong> ?',
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
            form.action = '';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_product';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            
            form.appendChild(actionInput);
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<?php include "app/views/footer.php"; ?>
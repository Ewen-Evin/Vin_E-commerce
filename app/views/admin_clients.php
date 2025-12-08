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
    <h1>👥 Gestion des Clients</h1>
    
    <div class="admin-welcome">
        <a href="index.php?page=admin" class="btn btn-secondary mb-3">
            <span class="material-icons">arrow_back</span> Retour à l'administration
        </a>
        <p>Liste de tous les clients ayant passé commande sur le site.</p>
    </div>

    <!-- Tableau des clients -->
    <div class="admin-section">
        <?php if (empty($clients)): ?>
            <div class="alert alert-info text-center">
                <span class="material-icons">info</span>
                Aucun client trouvé dans la base de données.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Nombre de commandes</th>
                            <th>Dernière commande</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($client['nom']) ?></strong>
                            </td>
                            <td>
                                <a href="mailto:<?= htmlspecialchars($client['email']) ?>" class="text-primary">
                                    <?= htmlspecialchars($client['email']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($client['telephone']): ?>
                                    <a href="tel:<?= htmlspecialchars($client['telephone']) ?>" class="text-primary">
                                        <?= htmlspecialchars($client['telephone']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Non renseigné</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-primary" style="font-size: 1em;">
                                    <?= $client['nb_commandes'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($client['derniere_commande']): ?>
                                    <?= date('d/m/Y H:i', strtotime($client['derniere_commande'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <!-- Bouton Voir les commandes -->
                                    <button type="button" class="btn btn-info" 
                                            onclick='viewClientOrders("<?= addslashes($client['email']) ?>")'
                                            title="Voir les commandes">
                                        <span class="material-icons">receipt</span>
                                    </button>
                                    
                                    <!-- Bouton Contacter -->
                                    <a href="mailto:<?= htmlspecialchars($client['email']) ?>" 
                                       class="btn btn-success"
                                       title="Envoyer un email">
                                        <span class="material-icons">email</span>
                                    </a>
                                    
                                    <?php if ($client['telephone']): ?>
                                    <!-- Bouton Appeler -->
                                    <a href="tel:<?= htmlspecialchars($client['telephone']) ?>" 
                                       class="btn btn-warning"
                                       title="Appeler">
                                        <span class="material-icons">phone</span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Résumé -->
            <div class="mt-4 p-3 bg-light rounded">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h4><?= count($clients) ?></h4>
                        <small class="text-muted">Clients total</small>
                    </div>
                    <div class="col-md-3">
                        <h4><?= array_sum(array_column($clients, 'nb_commandes')) ?></h4>
                        <small class="text-muted">Commandes totales</small>
                    </div>
                </div>
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
function viewClientOrders(email) {
    // Rediriger vers la page des commandes avec filtre par email
    window.location.href = 'index.php?page=admin-commandes&email=' + encodeURIComponent(email);
}

// Fonction de recherche en temps réel
function searchClients() {
    const input = document.getElementById('searchClient');
    const filter = input.value.toLowerCase();
    const table = document.querySelector('.table tbody');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length - 1; j++) { // -1 pour exclure la colonne actions
            if (cells[j]) {
                const text = cells[j].textContent || cells[j].innerText;
                if (text.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}

// Ajouter une barre de recherche (optionnel)
document.addEventListener('DOMContentLoaded', function() {
    const welcomeDiv = document.querySelector('.admin-welcome');
    const searchHtml = `
        <div class="mb-3">
            <div class="input-group" style="max-width: 400px;">
                <span class="input-group-text">
                    <span class="material-icons">search</span>
                </span>
                <input type="text" id="searchClient" class="form-control" placeholder="Rechercher un client..." onkeyup="searchClients()">
            </div>
        </div>
    `;
    welcomeDiv.insertAdjacentHTML('beforeend', searchHtml);
});
</script>

<style>
.badge {
    padding: 8px 12px;
    border-radius: 20px;
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
    text-align: center;
}

.input-group-text {
    background: #5a0c24;
    color: white;
    border: 1px solid #5a0c24;
}
</style>

<?php include "app/views/footer.php"; ?>

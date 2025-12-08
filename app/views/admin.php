<?php
if (!isset($_SESSION['admin'])) {
    header("Location: index.php?page=login");
    exit;
}
?>

<?php include "app/views/header.php"; ?>

<div class="admin-container">
    <h1>🛠️ Espace Administration</h1>
    
    <div class="admin-welcome">
        <p>Bienvenue <strong><?= htmlspecialchars($_SESSION['admin']) ?></strong> !</p>
        <p>Vous avez accès à toutes les fonctionnalités d'administration.</p>
    </div>

    <div class="admin-actions">
        <div class="action-card">
            <h3>📦 Gestion des Produits</h3>
            <p>Gérer le catalogue de vins et accessoires</p>
            <a href="index.php?page=admin-produits" class="btn btn-primary">Voir les produits</a>
        </div>
        
        <div class="action-card">
            <h3>👥 Gestion des Clients</h3>
            <p>Gérer la liste des clients</p>
            <a href="index.php?page=admin-clients" class="btn btn-primary">Voir les clients</a>
        </div>
        
        <div class="action-card">
            <h3>📋 Gestion des Commandes</h3>
            <p>Suivre et gérer les commandes</p>
            <a href="index.php?page=admin-commandes" class="btn btn-primary">Voir les commandes</a>
        </div>
        
        <!-- NOUVEAU : Bouton pour envoyer l'email de livraison -->
        <div class="action-card">
            <h3>📧 Notification Livraison</h3>
            <p>Envoyer l'info de livraison du weekend</p>
            <button type="button" class="btn btn-warning" onclick="sendDeliveryEmail()">
                Envoyer l'email
            </button>
        </div>
    </div>

    <div class="admin-footer">
        <a href="index.php?page=logout" class="btn btn-secondary">Se déconnecter</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function sendDeliveryEmail() {
    Swal.fire({
        title: 'Envoyer la notification de livraison ?',
        html: `
            <div class="text-start">
                <p><strong>Information de livraison :</strong></p>
                <p>"Bonsoir,<br>
                Le vin est arrivé chez moi ce soir ! 🎉<br>
                La livraison se fera d'ici ce weekend."</p>
                <p><strong>Mode test activé :</strong> L'email sera envoyé uniquement à <strong>ewenevin0@gmail.com</strong></p>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Oui, envoyer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#ffc107',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return fetch('index.php?page=admin-send-delivery-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'test_mode=1'
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Erreur lors de l\'envoi');
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(`Erreur: ${error.message}`);
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Email envoyé !',
                text: 'La notification de livraison a été envoyée avec succès (mode test).',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        }
    });
}
</script>

<?php include "app/views/footer.php"; ?>
<?php 
if (isset($_SESSION['admin'])) {
    header("Location: index.php?page=admin");
    exit;
}
?>

<?php include "app/views/header.php"; ?>

<div class="login-container">
    <h1>Connexion Admin</h1>
    
    <?php if (isset($_SESSION['admin'])): ?>
        <div class="already-connected">
            <p>✅ Vous êtes déjà connecté en tant que <strong><?= htmlspecialchars($_SESSION['admin']) ?></strong></p>
            <div class="button-group">
                <a href="index.php?page=admin" class="btn btn-primary">Accéder à l'administration</a>
                <a href="index.php?page=logout" class="btn btn-secondary">Se déconnecter</a>
            </div>
        </div>
    <?php else: ?>
        <form method="post" action="index.php?page=login-check" class="login-form">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>

        <?php if (!empty($error)): ?>
            <p class="error-message"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include "app/views/footer.php"; ?>
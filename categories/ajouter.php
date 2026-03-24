<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../connexion_db.php';

if (!isset($_SESSION['utilisateur']) || !in_array($_SESSION['utilisateur']['role'], ['editeur', 'administrateur'])) {
    header('Location: ' . $base_url . 'connexion/connexion.php');
    exit;
}

$erreurs = [];
$succes  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');

    // Validation serveur
    if (empty($nom)) {
        $erreurs[] = "Le nom de la catégorie est obligatoire.";
    } elseif (strlen($nom) > 100) {
        $erreurs[] = "Le nom ne doit pas dépasser 100 caractères.";
    } else {
        // Vérifier l'unicité
        $check = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE nom = :nom");
        $check->bindValue(':nom', $nom);
        $check->execute();
        if ($check->fetchColumn() > 0) {
            $erreurs[] = "Cette catégorie existe déjà.";
        }
    }

    if (empty($erreurs)) {
        $stmt = $pdo->prepare("INSERT INTO categories (nom) VALUES (:nom)");
        $stmt->bindValue(':nom', $nom);
        $stmt->execute();
        $succes = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une catégorie — ESPACTU</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php require '../menu.php'; ?>

<div class="main">
    <div class="form-card">
        <h2>Ajouter une catégorie</h2>

        <?php if ($succes): ?>
            <div class="alert alert-succes">
                Catégorie ajoutée avec succès. <a href="liste.php">Voir la liste</a>
            </div>
        <?php endif; ?>

        <?php if (!empty($erreurs)): ?>
            <div class="alert alert-erreur">
                <ul>
                    <?php foreach ($erreurs as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="form-categorie" method="POST" action="ajouter.php" novalidate>

            <div class="form-group">
                <label for="nom">Nom de la catégorie *</label>
                <input type="text" id="nom" name="nom"
                       value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                       maxlength="100"
                       placeholder="Ex : Technologie">
                <span class="form-error" id="err-nom"></span>
            </div>

            <div class="form-actions">
                <a href="liste.php" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>

        </form>
    </div>
</div>

<script>
document.getElementById('form-categorie').addEventListener('submit', function(e) {
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
    const nom = document.getElementById('nom').value.trim();
    let valide = true;

    if (!nom) {
        document.getElementById('err-nom').textContent = 'Le nom est obligatoire.';
        valide = false;
    } else if (nom.length > 100) {
        document.getElementById('err-nom').textContent = 'Le nom ne doit pas dépasser 100 caractères.';
        valide = false;
    }

    if (!valide) e.preventDefault();
});
</script>

</body>
</html>
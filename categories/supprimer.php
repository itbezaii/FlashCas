<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../connexion_db.php';

if (!isset($_SESSION['utilisateur']) || !in_array($_SESSION['utilisateur']['role'], ['editeur', 'administrateur'])) {
    header('Location: ' . $base_url . 'connexion/connexion.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: liste.php');
    exit;
}

$id = (int)$_GET['id'];

// Vérifier que la catégorie existe
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$categorie = $stmt->fetch();

if (!$categorie) {
    header('Location: liste.php');
    exit;
}

// Vérifier qu'aucun article n'utilise cette catégorie
$check = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE categorie_id = :id");
$check->bindValue(':id', $id, PDO::PARAM_INT);
$check->execute();
$nb_articles = (int)$check->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer'])) {
    if ($nb_articles > 0) {
        header('Location: liste.php');
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: liste.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer une catégorie — ESPACTU</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php require '../menu.php'; ?>

<div class="main">
    <div class="form-card confirm-card">

        <?php if ($nb_articles > 0): ?>
            <div class="confirm-icon">🚫</div>
            <h2>Suppression impossible</h2>
            <p>La catégorie <strong><?= htmlspecialchars($categorie['nom']) ?></strong>
               contient <strong><?= $nb_articles ?> article(s)</strong>.</p>
            <p class="confirm-warning">Supprimez ou déplacez ces articles avant de supprimer la catégorie.</p>
            <div class="form-actions">
                <a href="liste.php" class="btn btn-secondary">Retour</a>
            </div>

        <?php else: ?>
            <div class="confirm-icon">⚠️</div>
            <h2>Confirmer la suppression</h2>
            <p>Vous êtes sur le point de supprimer la catégorie :</p>
            <p class="confirm-titre">"<?= htmlspecialchars($categorie['nom']) ?>"</p>
            <p class="confirm-warning">Cette action est irréversible.</p>

            <form method="POST" action="supprimer.php?id=<?= $id ?>">
                <div class="form-actions">
                    <a href="liste.php" class="btn btn-secondary">Annuler</a>
                    <button type="submit" name="confirmer" value="1" class="btn btn-danger">
                        Supprimer définitivement
                    </button>
                </div>
            </form>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
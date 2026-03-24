<?php
session_start();
require '../connexion_db.php';

// Accès réservé aux éditeurs et administrateurs
if (!isset($_SESSION['utilisateur']) || !in_array($_SESSION['utilisateur']['role'], ['editeur', 'administrateur'])) {
    header('Location: ../connexion.php');
    exit;
}

// Vérification de l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../accueil.php');
    exit;
}

$id = (int)$_GET['id'];

// Vérifier que l'article existe
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$article = $stmt->fetch();

if (!$article) {
    header('Location: ../accueil.php');
    exit;
}

$erreur = '';

// Confirmation de suppression via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer'])) {
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: ../accueil.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un article — ESPACTU</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php require '../menu.php'; ?>

<div class="main">
    <div class="form-card confirm-card">

        <div class="confirm-icon">⚠️</div>
        <h2>Confirmer la suppression</h2>

        <p>Vous êtes sur le point de supprimer définitivement l'article :</p>
        <p class="confirm-titre">"<?= htmlspecialchars($article['titre']) ?>"</p>
        <p class="confirm-warning">Cette action est irréversible.</p>

        <form method="POST" action="supprimer.php?id=<?= $id ?>">
            <div class="form-actions">
                <a href="detail.php?id=<?= $id ?>" class="btn btn-secondary">Annuler</a>
                <button type="submit" name="confirmer" value="1"
                        class="btn btn-danger">Supprimer définitivement</button>
            </div>
        </form>

    </div>
</div>

</body>
</html>
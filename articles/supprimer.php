<?php
session_start();
require '../connexion_db.php';

<<<<<<< HEAD
// Accès réservé aux éditeurs et administrateurs
if (!isset($_SESSION['utilisateur']) || !in_array($_SESSION['utilisateur']['role'], ['editeur', 'administrateur'])) {
    header('Location: ../connexion.php');
    exit;
}

// Vérification de l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../accueil.php');
=======
if (!isset($_SESSION['role']) || 
    !in_array($_SESSION['role'], ['editeur', 'admin'])) {
    header('Location: ../connexion.php');
    exit;
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: liste.php');
>>>>>>> a74d60d794e932a4794d5b2e1cb71a2e73f483d3
    exit;
}

$id = (int)$_GET['id'];

<<<<<<< HEAD
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
=======
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = :id");
$stmt->execute([':id' => $id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: liste.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header('Location: liste.php?succes=supprime');
>>>>>>> a74d60d794e932a4794d5b2e1cb71a2e73f483d3
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
<<<<<<< HEAD
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un article — ESPACTU</title>
    <link rel="stylesheet" href="../css/style.css">
=======
    <title>Supprimer un article</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="form.css">
>>>>>>> a74d60d794e932a4794d5b2e1cb71a2e73f483d3
</head>
<body>

<?php require '../menu.php'; ?>

<<<<<<< HEAD
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
=======
<div class="form-container">
    <div class="form-header">
        <h1>Supprimer un article</h1>
        <a href="liste.php" class="btn-retour">← Retour</a>
    </div>

    <div class="alert alert-erreur">
        Êtes-vous sûr de vouloir supprimer cet article ?
    </div>

    <div class="article-apercu">
        <p class="apercu-titre"><?= htmlspecialchars($article['titre']) ?></p>
        <p class="apercu-desc"><?= htmlspecialchars($article['description']) ?></p>
        <p class="apercu-date">Publié le <?= date('d M Y', strtotime($article['date_publication'])) ?></p>
    </div>

    <form method="POST" action="">
        <div class="form-actions">
            <button type="submit" class="btn-danger">Oui, supprimer</button>
            <a href="liste.php" class="btn-annuler">Annuler</a>
        </div>
    </form>
>>>>>>> a74d60d794e932a4794d5b2e1cb71a2e73f483d3
</div>

</body>
</html>
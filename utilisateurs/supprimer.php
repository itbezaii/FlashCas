<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../connexion_db.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'administrateur') {
    header('Location: ' . $base_url . 'connexion/connexion.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: liste.php');
    exit;
}

$id = (int)$_GET['id'];

// Empêcher un admin de se supprimer lui-même
if ($id === (int)$_SESSION['utilisateur']['id']) {
    header('Location: liste.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id, nom, prenom, login, role FROM utilisateurs WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$utilisateur = $stmt->fetch();

if (!$utilisateur) {
    header('Location: liste.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer'])) {
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = :id");
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
    <title>Supprimer un utilisateur — ESPACTU</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php require '../menu.php'; ?>

<div class="main">
    <div class="form-card confirm-card">
        <div class="confirm-icon">⚠️</div>
        <h2>Confirmer la suppression</h2>

        <p>Vous êtes sur le point de supprimer l'utilisateur :</p>
        <p class="confirm-titre">
            "<?= htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']) ?>"
            <span class="badge badge-role badge-<?= $utilisateur['role'] ?>"><?= htmlspecialchars($utilisateur['role']) ?></span>
        </p>
        <p class="confirm-warning">Cette action est irréversible.</p>

        <form method="POST" action="supprimer.php?id=<?= $id ?>">
            <div class="form-actions">
                <a href="liste.php" class="btn btn-secondary">Annuler</a>
                <button type="submit" name="confirmer" value="1" class="btn btn-danger">
                    Supprimer définitivement
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
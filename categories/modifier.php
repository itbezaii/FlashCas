<?php
session_start();
require '../connexion_db.php';

if (!isset($_SESSION['utilisateur']) || !in_array($_SESSION['utilisateur']['role'], ['editeur', 'administrateur'])) {
    header('Location: ../connexion.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: liste.php');
    exit;
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$categorie = $stmt->fetch();

if (!$categorie) {
    header('Location: liste.php');
    exit;
}

$erreurs = [];
$succes  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');

    if (empty($nom)) {
        $erreurs[] = "Le nom de la catégorie est obligatoire.";
    } elseif (strlen($nom) > 100) {
        $erreurs[] = "Le nom ne doit pas dépasser 100 caractères.";
    } else {
        // Vérifier l'unicité (en excluant la catégorie courante)
        $check = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE nom = :nom AND id != :id");
        $check->bindValue(':nom', $nom);
        $check->bindValue(':id', $id, PDO::PARAM_INT);
        $check->execute();
        if ($check->fetchColumn() > 0) {
            $erreurs[] = "Cette catégorie existe déjà.";
        }
    }

    if (empty($erreurs)) {
        $stmt = $pdo->prepare("UPDATE categories SET nom = :nom WHERE id = :id");
        $stmt->bindValue(':nom', $nom);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $categorie['nom'] = $nom;
        $succes = true;
    }
}

$val_nom = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['nom'] ?? '') : $categorie['nom'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une catégorie — ESPACTU</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php require '../menu.php'; ?>

<div class="main">
    <div class="form-card">
        <h2>Modifier la catégorie</h2>

        <?php if ($succes): ?>
            <div class="alert alert-succes">
                Catégorie modifiée avec succès. <a href="liste.php">Voir la liste</a>
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

        <form id="form-categorie" method="POST" action="modifier.php?id=<?= $id ?>" novalidate>

            <div class="form-group">
                <label for="nom">Nom de la catégorie *</label>
                <input type="text" id="nom" name="nom"
                       value="<?= htmlspecialchars($val_nom) ?>"
                       maxlength="100">
                <span class="form-error" id="err-nom"></span>
            </div>

            <div class="form-actions">
                <a href="liste.php" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
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
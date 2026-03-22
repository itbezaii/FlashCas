<?php
session_start();
require '../connexion_db.php';

if (!isset($_SESSION['role']) || 
    !in_array($_SESSION['role'], ['editeur', 'admin'])) {
    header('Location: ../connexion.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: liste.php');
    exit;
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = :id");
$stmt->execute([':id' => $id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: liste.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();
$erreurs = [];
$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titre        = trim($_POST['titre'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $contenu      = trim($_POST['contenu'] ?? '');
    $categorie_id = (int)($_POST['categorie_id'] ?? 0);

    if (empty($titre))        $erreurs[] = "Le titre est obligatoire.";
    if (strlen($titre) > 255) $erreurs[] = "Le titre ne doit pas dépasser 255 caractères.";
    if (empty($description))  $erreurs[] = "La description est obligatoire.";
    if (empty($contenu))      $erreurs[] = "Le contenu est obligatoire.";
    if ($categorie_id === 0)  $erreurs[] = "Veuillez choisir une catégorie.";

    if (empty($erreurs)) {
        $stmt = $pdo->prepare("
            UPDATE articles 
            SET titre = :titre,
                description = :description,
                contenu = :contenu,
                categorie_id = :categorie_id
            WHERE id = :id
        ");
        $stmt->execute([
            ':titre'        => $titre,
            ':description'  => $description,
            ':contenu'      => $contenu,
            ':categorie_id' => $categorie_id,
            ':id'           => $id
        ]);
        $succes = true;

        $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $article = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un article</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="form.css">
</head>
<body>

<?php require '../menu.php'; ?>

<div class="form-container">
    <div class="form-header">
        <h1>Modifier un article</h1>
        <a href="liste.php" class="btn-retour">← Retour</a>
    </div>

    <?php if ($succes): ?>
        <div class="alert alert-succes">
            Article modifié avec succès !
            <a href="../accueil.php">Voir l'accueil</a>
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

    <form id="formModifier" method="POST" action="">

        <div class="form-group">
            <label for="titre">Titre *</label>
            <input
                type="text"
                id="titre"
                name="titre"
                value="<?= htmlspecialchars($_POST['titre'] ?? $article['titre']) ?>"
                maxlength="255"
            >
            <span class="erreur-js" id="err-titre"></span>
        </div>

        <div class="form-group">
            <label for="description">Description *</label>
            <input
                type="text"
                id="description"
                name="description"
                value="<?= htmlspecialchars($_POST['description'] ?? $article['description']) ?>"
            >
            <span class="erreur-js" id="err-description"></span>
        </div>

        <div class="form-group">
            <label for="categorie_id">Catégorie *</label>
            <select id="categorie_id" name="categorie_id">
                <option value="0">-- Choisir une catégorie --</option>
                <?php foreach ($categories as $cat): ?>
                    <option
                        value="<?= $cat['id'] ?>"
                        <?= (($_POST['categorie_id'] ?? $article['categorie_id']) == $cat['id']) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($cat['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="erreur-js" id="err-categorie"></span>
        </div>

        <div class="form-group">
            <label for="contenu">Contenu *</label>
            <textarea
                id="contenu"
                name="contenu"
                rows="10"
            ><?= htmlspecialchars($_POST['contenu'] ?? $article['contenu']) ?></textarea>
            <span class="erreur-js" id="err-contenu"></span>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">Enregistrer les modifications</button>
            <a href="liste.php" class="btn-annuler">Annuler</a>
        </div>

    </form>
</div>
<script>
document.getElementById('formModifier').addEventListener('submit', function(e) {
    let valide = true;

    document.querySelectorAll('.erreur-js').forEach(el => el.textContent = '');

    const titre       = document.getElementById('titre').value.trim();
    const description = document.getElementById('description').value.trim();
    const categorie   = document.getElementById('categorie_id').value;
    const contenu     = document.getElementById('contenu').value.trim();

    if (titre === '') {
        document.getElementById('err-titre').textContent = 'Le titre est obligatoire.';
        valide = false;
    } else if (titre.length > 255) {
        document.getElementById('err-titre').textContent = 'Le titre est trop long.';
        valide = false;
    }

    if (description === '') {
        document.getElementById('err-description').textContent = 'La description est obligatoire.';
        valide = false;
    }

    if (categorie === '0') {
        document.getElementById('err-categorie').textContent = 'Veuillez choisir une catégorie.';
        valide = false;
    }

    if (contenu === '') {
        document.getElementById('err-contenu').textContent = 'Le contenu est obligatoire.';
        valide = false;
    }

    if (!valide) e.preventDefault();
});
</script>

</body>
</html>
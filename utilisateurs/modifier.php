<?php
session_start();
require '../connexion_db.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'administrateur') {
    header('Location: ../connexion.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: liste.php');
    exit;
}

$id    = (int)$_GET['id'];
$roles = ['editeur', 'administrateur'];

$stmt = $pdo->prepare("SELECT id, nom, prenom, login, role FROM utilisateurs WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$utilisateur = $stmt->fetch();

if (!$utilisateur) {
    header('Location: liste.php');
    exit;
}

$erreurs = [];
$succes  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $login  = trim($_POST['login'] ?? '');
    $mdp    = $_POST['mot_de_passe'] ?? '';
    $mdp2   = $_POST['mot_de_passe2'] ?? '';
    $role   = $_POST['role'] ?? '';

    if (empty($nom))    $erreurs[] = "Le nom est obligatoire.";
    if (empty($prenom)) $erreurs[] = "Le prénom est obligatoire.";

    if (empty($login)) {
        $erreurs[] = "Le login est obligatoire.";
    } else {
        $check = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE login = :login AND id != :id");
        $check->bindValue(':login', $login);
        $check->bindValue(':id', $id, PDO::PARAM_INT);
        $check->execute();
        if ($check->fetchColumn() > 0) {
            $erreurs[] = "Ce login est déjà utilisé.";
        }
    }

    // Mot de passe facultatif à la modification
    if (!empty($mdp)) {
        if (strlen($mdp) < 6) {
            $erreurs[] = "Le mot de passe doit contenir au moins 6 caractères.";
        } elseif ($mdp !== $mdp2) {
            $erreurs[] = "Les mots de passe ne correspondent pas.";
        }
    }

    if (!in_array($role, $roles)) {
        $erreurs[] = "Le rôle sélectionné est invalide.";
    }

    if (empty($erreurs)) {
        if (!empty($mdp)) {
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                UPDATE utilisateurs
                SET nom = :nom, prenom = :prenom, login = :login,
                    mot_de_passe = :mot_de_passe, role = :role
                WHERE id = :id
            ");
            $stmt->bindValue(':mot_de_passe', $hash);
        } else {
            $stmt = $pdo->prepare("
                UPDATE utilisateurs
                SET nom = :nom, prenom = :prenom, login = :login, role = :role
                WHERE id = :id
            ");
        }
        $stmt->bindValue(':nom', $nom);
        $stmt->bindValue(':prenom', $prenom);
        $stmt->bindValue(':login', $login);
        $stmt->bindValue(':role', $role);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $utilisateur = ['id' => $id, 'nom' => $nom, 'prenom' => $prenom, 'login' => $login, 'role' => $role];
        $succes = true;
    }
}

$val_nom    = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['nom'] ?? '')    : $utilisateur['nom'];
$val_prenom = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['prenom'] ?? '') : $utilisateur['prenom'];
$val_login  = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['login'] ?? '')  : $utilisateur['login'];
$val_role   = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['role'] ?? '')   : $utilisateur['role'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un utilisateur — ESPACTU</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php require '../menu.php'; ?>

<div class="main">
    <div class="form-card">
        <h2>Modifier l'utilisateur</h2>

        <?php if ($succes): ?>
            <div class="alert alert-succes">
                Utilisateur modifié avec succès. <a href="liste.php">Voir la liste</a>
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

        <form id="form-utilisateur" method="POST" action="modifier.php?id=<?= $id ?>" novalidate>

            <div class="form-group">
                <label for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($val_nom) ?>">
                <span class="form-error" id="err-nom"></span>
            </div>

            <div class="form-group">
                <label for="prenom">Prénom *</label>
                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($val_prenom) ?>">
                <span class="form-error" id="err-prenom"></span>
            </div>

            <div class="form-group">
                <label for="login">Login *</label>
                <input type="text" id="login" name="login"
                       value="<?= htmlspecialchars($val_login) ?>" autocomplete="off">
                <span class="form-error" id="err-login"></span>
            </div>

            <div class="form-group">
                <label for="mot_de_passe">Nouveau mot de passe <small>(laisser vide pour ne pas changer)</small></label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" autocomplete="new-password">
                <span class="form-error" id="err-mdp"></span>
            </div>

            <div class="form-group">
                <label for="mot_de_passe2">Confirmer le mot de passe</label>
                <input type="password" id="mot_de_passe2" name="mot_de_passe2" autocomplete="new-password">
                <span class="form-error" id="err-mdp2"></span>
            </div>

            <div class="form-group">
                <label for="role">Rôle *</label>
                <select id="role" name="role">
                    <option value="editeur"        <?= $val_role === 'editeur' ? 'selected' : '' ?>>Éditeur</option>
                    <option value="administrateur" <?= $val_role === 'administrateur' ? 'selected' : '' ?>>Administrateur</option>
                </select>
                <span class="form-error" id="err-role"></span>
            </div>

            <div class="form-actions">
                <a href="liste.php" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>

        </form>
    </div>
</div>

<script>
document.getElementById('form-utilisateur').addEventListener('submit', function(e) {
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
    let valide = true;

    const nom    = document.getElementById('nom').value.trim();
    const prenom = document.getElementById('prenom').value.trim();
    const login  = document.getElementById('login').value.trim();
    const mdp    = document.getElementById('mot_de_passe').value;
    const mdp2   = document.getElementById('mot_de_passe2').value;
    const role   = document.getElementById('role').value;

    if (!nom)    { document.getElementById('err-nom').textContent = 'Le nom est obligatoire.'; valide = false; }
    if (!prenom) { document.getElementById('err-prenom').textContent = 'Le prénom est obligatoire.'; valide = false; }
    if (!login)  { document.getElementById('err-login').textContent = 'Le login est obligatoire.'; valide = false; }

    if (mdp) {
        if (mdp.length < 6) {
            document.getElementById('err-mdp').textContent = 'Le mot de passe doit contenir au moins 6 caractères.';
            valide = false;
        } else if (mdp !== mdp2) {
            document.getElementById('err-mdp2').textContent = 'Les mots de passe ne correspondent pas.';
            valide = false;
        }
    }

    if (!role) { document.getElementById('err-role').textContent = 'Le rôle est obligatoire.'; valide = false; }

    if (!valide) e.preventDefault();
});
</script>

</body>
</html>
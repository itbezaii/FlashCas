<?php
session_start();
require '../connexion_db.php';

// Accès réservé aux administrateurs uniquement
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'administrateur') {
    header('Location: ../connexion.php');
    exit;
}

$utilisateurs = $pdo->query("
    SELECT id, nom, prenom, login, role
    FROM utilisateurs
    ORDER BY nom ASC, prenom ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilisateurs — ESPACTU</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php require '../menu.php'; ?>

<div class="main">
    <div class="list-header">
        <h2>Gestion des utilisateurs</h2>
        <a href="ajouter.php" class="btn btn-primary">+ Nouvel utilisateur</a>
    </div>

    <?php if (empty($utilisateurs)): ?>
        <p class="no-result">Aucun utilisateur enregistré.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Login</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $i => $u): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($u['nom']) ?></td>
                        <td><?= htmlspecialchars($u['prenom']) ?></td>
                        <td><?= htmlspecialchars($u['login']) ?></td>
                        <td><span class="badge badge-role badge-<?= $u['role'] ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                        <td class="actions">
                            <a href="modifier.php?id=<?= (int)$u['id'] ?>" class="btn btn-secondary btn-sm">Modifier</a>
                            <?php if ((int)$u['id'] !== (int)$_SESSION['utilisateur']['id']): ?>
                                <a href="supprimer.php?id=<?= (int)$u['id'] ?>" class="btn btn-danger btn-sm">Supprimer</a>
                            <?php else: ?>
                                <span class="btn btn-disabled btn-sm" title="Vous ne pouvez pas supprimer votre propre compte">Supprimer</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
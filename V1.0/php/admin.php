<?php
session_start();

// Durée du cookie (en secondes) : ici 7 jours
const COOKIE_DURATION = 7 * 24 * 60 * 60;

// Identifiants administrateur (exemple codé en dur)
const ADMIN_USER = 'test';
const ADMIN_PASS = 'password';

// 1) Si le cookie existe mais pas la session, on recrée la session
if (empty($_SESSION['is_admin']) && isset($_COOKIE['admin_logged_in']) && $_COOKIE['admin_logged_in'] === '1') {
    $_SESSION['is_admin'] = true;
}

// 2) Déconnexion si on passe ?action=logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Supprimer la session
    session_unset();
    session_destroy();
    // Expirer le cookie
    setcookie('admin_logged_in', '', time() - 3600, '/');
    header('Location: admin.php');
    exit;
}

$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === ADMIN_USER && $password === ADMIN_PASS) {
        // Authentification réussie : créer la session
        $_SESSION['is_admin'] = true;
        // Créer un cookie valide 7 jours
        setcookie('admin_logged_in', '1', time() + COOKIE_DURATION, '/');
        header('Location: admin.php');
        exit;
    } else {
        $errorMessage = 'Nom d’utilisateur ou mot de passe invalide.';
    }
}

$isLoggedIn = !empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin – SAÉ 23</title>
  <!-- Lien vers votre CSS principal -->
  <link rel="stylesheet" href="../style/style1.css" />
  <!-- Ajustements très ciblés pour login + tableau Admin -->
  <style>
    /* Conteneur du formulaire de connexion */
    .login-container {
      max-width: 360px;
      margin: 4rem auto;
      background: #ffffff;
      border-radius: 12px;
      padding: 2rem 1.5rem;
      box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }
    .login-container h2 {
      text-align: center;
      font-size: 1.5rem;
      font-weight: 600;
      color: #1d1d1f;
      margin-bottom: 1.5rem;
    }
    .login-container label {
      display: block;
      margin-bottom: 0.25rem;
      font-size: 0.95rem;
      color: #1d1d1f;
    }
    .login-container input {
      width: 100%;
      padding: 0.6rem 0.8rem;
      font-size: 1rem;
      border: 1px solid #d2d2d7;
      border-radius: 8px;
      background: #f5f5f7;
      margin-bottom: 1rem;
      color: #1d1d1f;
    }
    .login-container input:focus {
      outline: none;
      border-color: #0071e3;
      box-shadow: 0 0 0 2px rgba(0,113,227,0.2);
    }
    .error-message {
      color: #c0392b;
      text-align: center;
      font-size: 0.9rem;
      margin-bottom: 1rem;
    }

    /* Conteneur général Admin */
    .admin-container {
      max-width: 90%;
      margin: 2rem auto;
    }
    .admin-container h1 {
      text-align: center;
      font-size: 2rem;
      font-weight: 600;
      color: #1d1d1f;
      margin-bottom: 1rem;
    }
    .admin-logout {
      text-align: right;
      margin-bottom: 1rem;
    }
    .admin-logout a {
      color: #c0392b;
      font-weight: 600;
      text-decoration: none;
      transition: color 0.3s ease;
    }
    .admin-logout a:hover {
      color: #a93226;
    }

    /* Tableau Admin */
    .admin-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }
    .admin-table th,
    .admin-table td {
      padding: 0.75rem 1rem;
      border: 1px solid #d2d2d7;
      text-align: left;
    }
    .admin-table th {
      background: #f5f5f7;
      font-weight: 600;
      color: #1d1d1f;
    }
    .admin-table tr:nth-child(even) td {
      background: #fafafa;
    }
  </style>
</head>
<body>
  <!-- SEUL bloc HEADER / NAV -->
  <header class="header">
    <nav class="nav container">
      <a href="index.html" class="logo">Data Processing</a>
      <ul class="nav-links">
        <li><a href="../index.html">Accueil</a></li>
        <li><a href="../presentation.html">Présentation</a></li>
        <li><a href="../projet.html">Projet</a></li>
        <li><a href="admin.php" class="active"> Admin</a></li>
      </ul>
      <button id="menu-toggle" class="menu-toggle" aria-label="Menu">&#9776;</button>
    </nav>
  </header>

  <?php if (!$isLoggedIn): ?>
    <!-- FORMULAIRE DE CONNEXION -->
    <div class="login-container">
      <h2>Connexion Admin</h2>
      <?php if ($errorMessage): ?>
        <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>
      <?php endif; ?>
      <form method="post" action="admin.php">
        <label for="username">Nom d’utilisateur</label>
        <input type="text" name="username" id="username" required autofocus />

        <label for="password">Mot de passe</label>
        <input type="password" name="password" id="password" required />

        <button type="submit" class="btn">Se connecter</button>
      </form>
    </div>
  <?php else: ?>
    <!-- ESPACE ADMIN APRÈS CONNEXION -->
    <div class="admin-container">
      <h1>Espace Admin</h1>
      <div class="admin-logout">
        <a href="admin.php?action=logout">Déconnexion</a>
      </div>

      <?php
      // Exemple de données factices (remplacez par votre requête PDO)
      $rows = [
        ['id'=>1, 'nom_utilisateur'=>'alice', 'email'=>'alice@example.com'],
        ['id'=>2, 'nom_utilisateur'=>'bob',   'email'=>'bob@example.com'],
      ];
      ?>

      <?php if (count($rows) > 0): ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Utilisateur</th>
              <th>Email</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['id']) ?></td>
                <td><?= htmlspecialchars($r['nom_utilisateur']) ?></td>
                <td><?= htmlspecialchars($r['email']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="projet-paragraphe">Aucun enregistrement à afficher.</p>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- FOOTER unique -->
  <footer class="footer">
    <div class="footer-container">
      <p class="footer-text">© 2025 SAÉ 23 Groupe B – Tous droits réservés.</p>
      <p class="footer-legal">
        Mentions légales : ce site et son contenu sont la propriété de l’équipe SAÉ 23 du BUT R&T, IUT de Blagnac.
      </p>
      <p class="footer-github">
        Retrouvez-nous sur 
        <a href="https://github.com/hugo-diniz/SAE23Projet/tree/main " target="_blank" rel="noopener">GitHub</a>
      </p>
    </div>
  </footer>
</body>
</html>

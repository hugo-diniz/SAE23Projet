<?php
session_start();

// Durée du cookie « default_table » : 30 jours
const TABLE_COOKIE_DURATION = 30 * 24 * 60 * 60;

// Identifiants administrateur (codés en dur)
const ADMIN_USER = 'admin';
const ADMIN_PASS = 'password';

// Paramètres de connexion à la base de données
$servername  = "localhost";
$db_username = "adminB";
$db_password = "passroot";
$dbname      = "sae23";

// 1) Processus de login (session + cookie de connexion)
$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']  ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === ADMIN_USER && $password === ADMIN_PASS) {
        $_SESSION['is_admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $errorMessage = 'Nom ou mot de passe invalide.';
    }
}

// 2) Logout éventuel
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: admin.php');
    exit;
}

// 3) Si on n’est pas connecté, on affiche uniquement le formulaire de login
if (empty($_SESSION['is_admin'])) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
      <title>Admin – Connexion</title>
      <link rel="stylesheet" href="style/style1.css" />
      <style>
        .login-container {
          max-width: 360px;
          margin: 6rem auto;
          background: #fff;
          border-radius: 12px;
          padding: 2rem 1.5rem;
          box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }
        .login-container h2 {
          text-align: center;
          font-size: 1.5rem;
          font-weight: 600;
          margin-bottom: 1.5rem;
          color: #1d1d1f;
        }
        .login-container label,
        .login-container input {
          display: block;
          width: 100%;
        }
        .login-container label {
          margin-bottom: 0.25rem;
          font-size: 0.95rem;
          color: #1d1d1f;
        }
        .login-container input {
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
      </style>
    </head>
    <body>
      <header class="header">
        <nav class="nav container">
          <a href="index.html" class="logo">Data Processing</a>
          <ul class="nav-links">
            <li><a href="index.html">Accueil</a></li>
            <li><a href="presentation.html">Présentation</a></li>
            <li><a href="projet.html">Projet</a></li>
            <li><a href="admin.php" class="active">Admin</a></li>
            <li><a href="consultation.php">Données</a></li>
          </ul>
          <button id="menu-toggle" class="menu-toggle" aria-label="Menu">&#9776;</button>
        </nav>
      </header>

      <div class="login-container">
        <h2>Connexion Admin</h2>
        <?php if ($errorMessage): ?>
          <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>
        <form method="post" action="admin.php">
          <input type="hidden" name="login" value="1" />
          <label for="username">Nom d'utilisateur</label>
          <input type="text" name="username" id="username" required autofocus />

          <label for="password">Mot de passe</label>
          <input type="password" name="password" id="password" required />

          <button type="submit" class="btn">Se connecter</button>
        </form>
      </div>

      <footer class="footer">
        <div class="footer-container">
          <p>© 2025 SAÉ 23 Groupe B – Tous droits réservés.</p>
          <p>Mentions légales : site et contenu © équipe SAE 23, BUT R&T, IUT Blagnac.</p>
          <p>GitHub : 
            <a href="https://github.com/hugo-diniz/SAE23Projet/tree/main" target="_blank" rel="noopener">
              github.com/hugo-diniz/SAE23Projet
            </a>
          </p>
        </div>
      </footer>
    </body>
    </html>
    <?php
    exit;
}

// 4) À ce stade, l’admin est connecté. On va proposer le choix de la “table par défaut”.

// a) Connexion temporaire pour lister les tables
$conn = mysqli_connect($servername, $db_username, $db_password, $dbname);
$tables = [];
if ($conn) {
    $res = mysqli_query($conn, "SHOW TABLES");
    while ($row = mysqli_fetch_array($res)) {
        $tables[] = $row[0];
    }
    mysqli_close($conn);
}

// b) Si l’admin a soumis une table, on enregistre dans un cookie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['default_table'])) {
    $chosen = $_POST['default_table'];
    if (in_array($chosen, $tables, true)) {
        setcookie('default_table', $chosen, time() + TABLE_COOKIE_DURATION, '/');
    }
    header('Location: admin.php');
    exit;
}

// Récupérer la table par défaut (si existante)
$defaultTable = $_COOKIE['default_table'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin – Choix de la table</title>
  <link rel="stylesheet" href="style/style1.css" />
  <style>
    .admin-container {
      max-width: 480px;
      margin: 3rem auto;
      background: #fff;
      border-radius: 12px;
      padding: 2rem 1.5rem;
      box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }
    .admin-container h1 {
      text-align: center;
      font-size: 1.75rem;
      margin-bottom: 1rem;
      color: #1d1d1f;
    }
    .admin-container label {
      display: block;
      margin: 0.75rem 0 0.25rem;
      font-size: 1rem;
      color: #1d1d1f;
    }
    .admin-container select {
      width: 100%;
      padding: 0.6rem 0.8rem;
      font-size: 1rem;
      border: 1px solid #d2d2d7;
      border-radius: 8px;
      background: #f5f5f7;
      margin-bottom: 1.5rem;
      color: #1d1d1f;
    }
    .admin-container button {
      width: 100%;
      background: #0071e3;
      color: #fff;
      padding: 0.7rem;
      font-size: 1rem;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .admin-container button:hover {
      background: #005bb5;
    }
    .table-current {
      margin-top: 1rem;
      font-size: 0.95rem;
      color: #6e6e73;
      text-align: center;
    }
    .admin-logout {
      text-align: right;
      margin-bottom: 1rem;
    }
    .admin-logout a {
      color: #c0392b;
      font-weight: 600;
      text-decoration: none;
    }
    .admin-logout a:hover {
      color: #a93226;
    }
  </style>
</head>
<body>
  <header class="header">
    <nav class="nav container">
      <a href="index.html" class="logo">Data Processing</a>
      <ul class="nav-links">
        <li><a href="index.html">Accueil</a></li>
        <li><a href="presentation.html">Présentation</a></li>
        <li><a href="projet.html">Projet</a></li>
        <li><a href="admin.php" class="active">Admin</a></li>
        <li><a href="consultation.php">Données</a></li>
      </ul>
      <button id="menu-toggle" class="menu-toggle" aria-label="Menu">&#9776;</button>
    </nav>
  </header>

  <div class="admin-container">
    <h1>Choix de la « table par défaut »</h1>
    <div class="admin-logout">
      <a href="admin.php?action=logout">Déconnexion</a>
    </div>

    <?php if (empty($tables)): ?>
      <p>Aucune table trouvée dans la base de données.</p>
    <?php else: ?>
      <form method="post" action="admin.php">
        <label for="default_table">Sélectionnez une table :</label>
        <select name="default_table" id="default_table" required>
          <option value="">-- Choisir --</option>
          <?php foreach ($tables as $t): ?>
            <option value="<?= htmlspecialchars($t) ?>"
              <?= ($defaultTable === $t) ? 'selected' : '' ?>>
              <?= htmlspecialchars($t) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit">Enregistrer</button>
      </form>
      <?php if ($defaultTable): ?>
        <p class="table-current">
          La table par défaut est : <strong><?= htmlspecialchars($defaultTable) ?></strong>
        </p>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <footer class="footer">
    <div class="footer-container">
      <p>© 2025 SAÉ 23 Groupe B – Tous droits réservés.</p>
      <p>Mentions légales : site et contenu © équipe SAE 23, BUT R&T, IUT Blagnac.</p>
      <p>GitHub :
        <a href="https://github.com/hugo-diniz/SAE23Projet/tree/main" target="_blank" rel="noopener">
          github.com/hugo-diniz/SAE23Projet
        </a>
      </p>
    </div>
  </footer>
</body>
</html>

<?php
session_start();

// Durée du cookie (en secondes) : ici 15 minutes
const COOKIE_DURATION = 00 * 00 * 15 * 60;

// Identifiants administrateur (exemple codé en dur)
const ADMIN_USER = 'adminA';
const ADMIN_PASS = 'passroot';

// Paramètres de base de données
$servername = "localhost";
$db_username = "adminA";
$db_password = "passroot";
$dbname = "sae23";

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
        $errorMessage = 'Nom ou mot de passe invalide.';
    }
}

$isLoggedIn = !empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// Connexion à la base de données (seulement si connecté)
$conn = null;
$tables = [];
$selectedTable = $_GET['table'] ?? '';
$tableData = [];
$errorDb = '';

if ($isLoggedIn) {
    try {
        $conn = mysqli_connect($servername, $db_username, $db_password, $dbname);
        if (!$conn) {
            throw new Exception("Connexion échouée: " . mysqli_connect_error());
        }
        
        // Récupérer la liste des tables
        $result = mysqli_query($conn, "SHOW TABLES");
        if ($result) {
            while ($row = mysqli_fetch_array($result)) {
                $tables[] = $row[0];
            }
        }
        
        // Si une table est sélectionnée, récupérer ses données
        if ($selectedTable && in_array($selectedTable, $tables)) {
            $query = "SELECT * FROM `" . mysqli_real_escape_string($conn, $selectedTable) . "` LIMIT 100";
            $result = mysqli_query($conn, $query);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $tableData[] = $row;
                }
            }
        }
        
    } catch (Exception $e) {
        $errorDb = $e->getMessage();
    }
}
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
      max-width: 95%;
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

    /* Navigation des tables */
    .table-nav {
      margin: 1.5rem 0;
      padding: 1rem;
      background: #f5f5f7;
      border-radius: 8px;
    }
    .table-nav h3 {
      margin: 0 0 1rem 0;
      color: #1d1d1f;
    }
    .table-links {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
    }
    .table-links a {
      padding: 0.5rem 1rem;
      background: #ffffff;
      color: #0071e3;
      text-decoration: none;
      border-radius: 6px;
      border: 1px solid #d2d2d7;
      transition: all 0.3s ease;
    }
    .table-links a:hover,
    .table-links a.active {
      background: #0071e3;
      color: #ffffff;
    }

    /* Tableau Admin */
    .admin-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
      font-size: 0.9rem;
    }
    .admin-table th,
    .admin-table td {
      padding: 0.75rem 1rem;
      border: 1px solid #d2d2d7;
      text-align: left;
      word-wrap: break-word;
      max-width: 200px;
    }
    .admin-table th {
      background: #f5f5f7;
      font-weight: 600;
      color: #1d1d1f;
      position: sticky;
      top: 0;
    }
    .admin-table tr:nth-child(even) td {
      background: #fafafa;
    }
    .admin-table td {
      vertical-align: top;
    }

    /* Infos de la base */
    .db-info {
      background: #e8f5e8;
      padding: 1rem;
      border-radius: 8px;
      border-left: 4px solid #27ae60;
      margin-bottom: 1rem;
    }
    .db-error {
      background: #fdeaea;
      padding: 1rem;
      border-radius: 8px;
      border-left: 4px solid #c0392b;
      color: #c0392b;
      margin-bottom: 1rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .table-links {
        flex-direction: column;
      }
      .admin-table {
        font-size: 0.8rem;
      }
      .admin-table th,
      .admin-table td {
        padding: 0.5rem;
        max-width: 150px;
      }
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
        <li><a href="consultation.php">Données</a></li>
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
        <label for="username">Nom d'utilisateur</label>
        <input type="text" name="username" id="username" required autofocus />

        <label for="password">Mot de passe</label>
        <input type="password" name="password" id="password" required />

        <button type="submit" class="btn">Se connecter</button>
      </form>
    </div>
  <?php else: ?>
    <!-- ESPACE ADMIN APRÈS CONNEXION -->
    <div class="admin-container">
      <h1>Espace Admin - Base de données SAÉ23</h1>
      <div class="admin-logout">
        <a href="admin.php?action=logout">Déconnexion</a>
      </div>

      <?php if ($errorDb): ?>
        <div class="db-error">
          <strong>Erreur de connexion à la base de données :</strong><br>
          <?= htmlspecialchars($errorDb) ?>
        </div>
      <?php elseif ($conn): ?>
        <div class="db-info">
          <strong>✓ Connecté à la base de données :</strong> <?= htmlspecialchars($dbname) ?><br>
          <strong>Serveur :</strong> <?= htmlspecialchars($servername) ?> | 
          <strong>Tables trouvées :</strong> <?= count($tables) ?>
        </div>

        <?php if (count($tables) > 0): ?>
          <div class="table-nav">
            <h3>Sélectionner une table :</h3>
            <div class="table-links">
              <?php foreach ($tables as $table): ?>
                <a href="admin.php?table=<?= urlencode($table) ?>" 
                   class="<?= $selectedTable === $table ? 'active' : '' ?>">
                  <?= htmlspecialchars($table) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>

          <?php if ($selectedTable && count($tableData) > 0): ?>
            <h3>Données de la table : <?= htmlspecialchars($selectedTable) ?></h3>
            <p><em>Affichage des 100 premiers enregistrements</em></p>
            
            <div style="overflow-x: auto;">
              <table class="admin-table">
                <thead>
                  <tr>
                    <?php foreach (array_keys($tableData[0]) as $column): ?>
                      <th><?= htmlspecialchars($column) ?></th>
                    <?php endforeach; ?>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($tableData as $row): ?>
                    <tr>
                      <?php foreach ($row as $value): ?>
                        <td><?= htmlspecialchars($value ?? 'NULL') ?></td>
                      <?php endforeach; ?>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            
          <?php elseif ($selectedTable && count($tableData) === 0): ?>
            <p class="projet-paragraphe">La table "<?= htmlspecialchars($selectedTable) ?>" est vide ou n'existe pas.</p>
            
          <?php elseif (!$selectedTable): ?>
            <p class="projet-paragraphe">Sélectionnez une table ci-dessus pour voir son contenu.</p>
          <?php endif; ?>
          
        <?php else: ?>
          <p class="projet-paragraphe">Aucune table trouvée dans la base de données.</p>
        <?php endif; ?>
        
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- FOOTER unique -->
  <footer class="footer">
    <div class="footer-container">
      <p class="footer-text">© 2025 SAÉ 23 Groupe B – Tous droits réservés.</p>
      <p class="footer-legal">
        Mentions légales : ce site et son contenu sont la propriété de l'équipe SAÉ 23 du BUT R&T, IUT de Blagnac.
      </p>
      <p class="footer-github">
        Retrouvez-nous sur 
        <a href="https://github.com/hugo-diniz/SAE23Projet/tree/main " target="_blank" rel="noopener">GitHub</a>
      </p>
    </div>
  </footer>

  <?php 
  // Fermer la connexion
  if ($conn) {
      mysqli_close($conn);
  }
  ?>
</body>
</html>

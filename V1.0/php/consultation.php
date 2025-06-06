<?php
// Pas de session/login ici : on se connecte toujours automatiquement
// à la base pour lire la « table par défaut » que l’admin a choisie.

$servername   = "localhost";
$db_username  = "adminA";
$db_password  = "passroot";
$dbname       = "sae23";


$conn          = null;
$errorDb       = '';
$tables        = [];
$selectedTable = $_COOKIE['default_table'] ?? '';
$tableData     = [];

$conn = mysqli_connect($servername, $db_username, $db_password, $dbname);
if (!$conn) {
    $errorDb = "Connexion échouée : " . mysqli_connect_error();
} else {
    // Récupérer la liste des tables (pour affichage d’infos)
    $res = mysqli_query($conn, "SHOW TABLES");
    if ($res) {
        while ($row = mysqli_fetch_array($res)) {
            $tables[] = $row[0];
        }
    }
    // Si on a une “table par défaut” valide, on en récupère les 100 premiers enregistrements
    if ($selectedTable && in_array($selectedTable, $tables, true)) {
        $safeTable = mysqli_real_escape_string($conn, $selectedTable);
        $query     = "SELECT * FROM `{$safeTable}` LIMIT 100";
        $res2      = mysqli_query($conn, $query);
        if ($res2) {
            while ($row = mysqli_fetch_assoc($res2)) {
                $tableData[] = $row;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Consultation – SAE 23</title>
  <link rel="stylesheet" href="style/style1.css" />
  <style>
    .admin-container {
      max-width: 95%;
      margin: 2rem auto;
    }
    .table-info {
      margin-bottom: 1rem;
      font-size: 1rem;
      color: #1d1d1f;
    }
    .db-error {
      background: #fdeaea;
      padding: 1rem;
      border-radius: 8px;
      border-left: 4px solid #c0392b;
      color: #c0392b;
      margin-bottom: 1rem;
    }
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
    @media (max-width: 768px) {
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
  <header class="header">
    <nav class="nav container">
      <a href="index.html" class="logo">Data Processing</a>
      <ul class="nav-links">
        <li><a href="index.html">Accueil</a></li>
        <li><a href="presentation.html">Présentation</a></li>
        <li><a href="projet.html">Projet</a></li>
        <li><a href="admin.php">Admin</a></li>
        <li><a href="consultation.php" class="active">Données</a></li>
      </ul>
      <button id="menu-toggle" class="menu-toggle" aria-label="Menu">&#9776;</button>
    </nav>
  </header>

  <div class="admin-container">
    <h1>Consultation des données</h1>

    <?php if ($errorDb): ?>
      <div class="db-error">
        <strong>Erreur de connexion : </strong><br>
        <?= htmlspecialchars($errorDb) ?>
      </div>
    <?php else: ?>
      <?php if (empty($selectedTable)): ?>
        <p class="table-info">
          Aucune « table par défaut » définie.  
          Veuillez <a href="admin.php">aller dans Admin</a> pour choisir la table à afficher.
        </p>
      <?php elseif (!in_array($selectedTable, $tables, true)): ?>
        <p class="table-info">
          La table « <?= htmlspecialchars($selectedTable) ?> » n’existe pas.  
          Veuillez <a href="admin.php">vérifier le choix dans Admin</a>.
        </p>
      <?php else: ?>
        <div class="table-info">
          Affichage de la table : <strong><?= htmlspecialchars($selectedTable) ?></strong>  
          (enregistrements limités à 100).  
          <br>
          <em>Total de tables dans la base : <?= count($tables) ?></em>
        </div>

        <?php if (count($tableData) === 0): ?>
          <p class="table-info">La table « <?= htmlspecialchars($selectedTable) ?> » est vide.</p>
        <?php else: ?>
          <div style="overflow-x:auto;">
            <table class="admin-table">
              <thead>
                <tr>
                  <?php foreach (array_keys($tableData[0]) as $col): ?>
                    <th><?= htmlspecialchars($col) ?></th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($tableData as $row): ?>
                  <tr>
                    <?php foreach ($row as $val): ?>
                      <td><?= htmlspecialchars($val ?? 'NULL') ?></td>
                    <?php endforeach; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
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

  <?php if ($conn) { mysqli_close($conn); } ?>
</body>
</html>

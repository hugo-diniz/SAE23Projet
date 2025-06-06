<?php
session_start();

// Paramètres de base de données
$servername = "localhost";
$db_username = "adminB";
$db_password = "passroot";
$dbname = "sae23";

// Variables pour l'affichage
$selectedTable = $_GET['table'] ?? '';
$tables = [];
$tableData = [];
$errorDb = '';
$conn = null;

// Connexion à la base de données
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Consultation des Données – SAÉ 23</title>
  <!-- Lien vers votre CSS principal -->
  <link rel="stylesheet" href="../style/style1.css" />
  <style>
    /* Conteneur principal */
    .data-container {
      max-width: 95%;
      margin: 2rem auto;
      padding: 0 1rem;
    }
    
    .data-container h1 {
      text-align: center;
      font-size: 2.2rem;
      font-weight: 600;
      color: #1d1d1f;
      margin-bottom: 1rem;
    }
    
    .data-subtitle {
      text-align: center;
      color: #666;
      font-size: 1.1rem;
      margin-bottom: 2rem;
    }

    /* Statut de connexion */
    .connection-status {
      background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
      padding: 1.2rem;
      border-radius: 12px;
      border-left: 5px solid #27ae60;
      margin-bottom: 2rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .connection-error {
      background: linear-gradient(135deg, #fdeaea 0%, #f8d7da 100%);
      padding: 1.2rem;
      border-radius: 12px;
      border-left: 5px solid #c0392b;
      color: #721c24;
      margin-bottom: 2rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    /* Navigation des tables */
    .table-selector {
      background: #ffffff;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.08);
      margin-bottom: 2rem;
    }
    
    .table-selector h2 {
      margin: 0 0 1.2rem 0;
      color: #1d1d1f;
      font-size: 1.4rem;
      font-weight: 600;
    }
    
    .table-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 1rem;
    }
    
    .table-card {
      padding: 1rem 1.2rem;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      color: #0071e3;
      text-decoration: none;
      border-radius: 10px;
      border: 2px solid transparent;
      transition: all 0.3s ease;
      text-align: center;
      font-weight: 500;
      position: relative;
      overflow: hidden;
    }
    
    .table-card:before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
      transition: left 0.5s;
    }
    
    .table-card:hover:before {
      left: 100%;
    }
    
    .table-card:hover,
    .table-card.active {
      background: linear-gradient(135deg, #0071e3 0%, #005bb5 100%);
      color: #ffffff;
      border-color: #0071e3;
      transform: translateY(-2px);
      box-shadow: 0 4px 16px rgba(0,113,227,0.3);
    }

    /* Table de données */
    .data-section {
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.08);
      overflow: hidden;
    }
    
    .data-header {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      padding: 1.5rem;
      border-bottom: 1px solid #dee2e6;
    }
    
    .data-header h3 {
      margin: 0 0 0.5rem 0;
      color: #1d1d1f;
      font-size: 1.3rem;
      font-weight: 600;
    }
    
    .data-info {
      color: #666;
      font-size: 0.9rem;
    }
    
    .data-table-container {
      overflow-x: auto;
      max-height: 70vh;
    }
    
    .data-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9rem;
    }
    
    .data-table th {
      background: linear-gradient(135deg, #1d1d1f 0%, #2c2c2e 100%);
      color: #ffffff;
      padding: 1rem;
      text-align: left;
      font-weight: 600;
      position: sticky;
      top: 0;
      z-index: 10;
      border-bottom: 2px solid #0071e3;
    }
    
    .data-table td {
      padding: 0.8rem 1rem;
      border-bottom: 1px solid #dee2e6;
      vertical-align: top;
      max-width: 250px;
      word-wrap: break-word;
    }
    
    .data-table tr:nth-child(even) td {
      background: #f8f9fa;
    }
    
    .data-table tr:hover td {
      background: #e3f2fd;
    }

    /* États spéciaux */
    .no-data {
      text-align: center;
      padding: 3rem;
      color: #666;
    }
    
    .no-data-icon {
      font-size: 3rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .data-container {
        margin: 1rem auto;
        padding: 0 0.5rem;
      }
      
      .table-grid {
        grid-template-columns: 1fr;
      }
      
      .data-table {
        font-size: 0.8rem;
      }
      
      .data-table th,
      .data-table td {
        padding: 0.6rem 0.8rem;
        max-width: 150px;
      }
      
      .data-container h1 {
        font-size: 1.8rem;
      }
    }

    /* Animation de chargement */
    .loading {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid #f3f3f3;
      border-radius: 50%;
      border-top-color: #0071e3;
      animation: spin 1s ease-in-out infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
  </style>
</head>
<body>
  <!-- Navigation -->
  <header class="header">
    <nav class="nav container">
      <a href="../index.html" class="logo">Data Processing</a>
      <ul class="nav-links">
        <li><a href="../index.html">Accueil</a></li>
        <li><a href="../presentation.html">Présentation</a></li>
        <li><a href="../projet.html">Projet</a></li>
        <li><a href="admin.php">Admin</a></li>
        <li><a href="consultation.php" class="active">Données</a></li>
      </ul>
      <button id="menu-toggle" class="menu-toggle" aria-label="Menu">&#9776;</button>
    </nav>
  </header>

  <div class="data-container">
    <h1>📊 Consultation des Données</h1>
    <p class="data-subtitle">Explorez les données de votre base SAÉ23</p>

    <!-- Statut de connexion -->
    <?php if ($errorDb): ?>
      <div class="connection-error">
        <strong>❌ Erreur de connexion à la base de données</strong><br>
        <?= htmlspecialchars($errorDb) ?>
      </div>
    <?php elseif ($conn): ?>
      <div class="connection-status">
        <strong>✅ Connexion établie</strong><br>
        <strong>Base :</strong> <?= htmlspecialchars($dbname) ?> | 
        <strong>Serveur :</strong> <?= htmlspecialchars($servername) ?> | 
        <strong>Tables :</strong> <?= count($tables) ?>
      </div>
    <?php endif; ?>

    <?php if ($conn && count($tables) > 0): ?>
      <!-- Sélecteur de tables -->
      <div class="table-selector">
        <h2>🗂️ Sélectionnez une table</h2>
        <div class="table-grid">
          <?php foreach ($tables as $table): ?>
            <a href="database.php?table=<?= urlencode($table) ?>" 
               class="table-card <?= $selectedTable === $table ? 'active' : '' ?>">
              📋 <?= htmlspecialchars($table) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Affichage des données -->
      <?php if ($selectedTable): ?>
        <div class="data-section">
          <div class="data-header">
            <h3>📊 Table : <?= htmlspecialchars($selectedTable) ?></h3>
            <div class="data-info">
              <?php if (count($tableData) > 0): ?>
                Affichage de <?= count($tableData) ?> enregistrement(s) 
                <?= count($tableData) === 100 ? '(limité à 100)' : '' ?>
              <?php else: ?>
                Aucune donnée trouvée
              <?php endif; ?>
            </div>
          </div>

          <?php if (count($tableData) > 0): ?>
            <div class="data-table-container">
              <table class="data-table">
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
          <?php else: ?>
            <div class="no-data">
              <div class="no-data-icon">📭</div>
              <h3>Table vide</h3>
              <p>La table "<?= htmlspecialchars($selectedTable) ?>" ne contient aucune donnée.</p>
            </div>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="no-data">
          <div class="no-data-icon">👆</div>
          <h3>Sélectionnez une table</h3>
          <p>Choisissez une table ci-dessus pour afficher son contenu.</p>
        </div>
      <?php endif; ?>

    <?php elseif ($conn): ?>
      <div class="no-data">
        <div class="no-data-icon">🗃️</div>
        <h3>Aucune table trouvée</h3>
        <p>La base de données ne contient aucune table accessible.</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- Footer -->
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

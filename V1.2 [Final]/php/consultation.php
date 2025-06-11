<?php
// === Configuration de la base de données ===
$servername = "localhost";
$db_username = "adminA";
$db_password = "passroot";
$dbname = "sae23";

// === Variables d'initialisation ===
$conn = mysqli_connect($servername, $db_username, $db_password, $dbname);
$visibleTables = [];
$selectedTable = $_GET['table'] ?? '';
$tableData = [];
$error = '';

// === Vérification de la connexion ===
if (!$conn) {
    $error = "Connexion à la base de données échouée : " . mysqli_connect_error();
} else {
    // Récupération des tables visibles
    $result = mysqli_query($conn, "SELECT table_name FROM visible_tables");
    while ($row = mysqli_fetch_assoc($result)) {
        $visibleTables[] = $row['table_name'];
    }

    // Sélection et affichage d'une table
    if ($selectedTable && in_array($selectedTable, $visibleTables)) {
        $safeTable = mysqli_real_escape_string($conn, $selectedTable);
        $res = mysqli_query($conn, "SELECT * FROM `$safeTable` LIMIT 100");

        if ($res && mysqli_num_rows($res) > 0) {
            while ($row = mysqli_fetch_assoc($res)) {
                $tableData[] = $row;
            }
        }
    }
}

// === Configuration PDO pour les statistiques ===
$host = 'localhost';
$db = 'sae23';
$user = 'adminA';
$pass = 'passroot';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$capteurs = [];
$filtreCapteur = $_GET['capteur'] ?? null;
$resultat = null;

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Récupérer la liste des capteurs pour le formulaire
    $capteurs = $pdo->query("SELECT DISTINCT NomCap FROM Mesure ORDER BY NomCap")->fetchAll();

    if ($filtreCapteur) {
        // Requête avec filtre sur NomCap
        $sql = "
            SELECT NomCap,
                   MIN(Valeur) AS MinValeur,
                   MAX(Valeur) AS MaxValeur,
                   AVG(Valeur) AS MoyenneValeur
            FROM Mesure
            WHERE NomCap = ?
            GROUP BY NomCap
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$filtreCapteur]);
        $resultat = $stmt->fetch();
    }
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Consultation Données</title>
    <meta http-equiv="refresh" content="120">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="../style/style1.css" />
    <style>
        .table-links a {
            margin-right: 0.8rem;
            margin-bottom: 0.6rem;
            display: inline-block;
            background-color: #eee;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
        }

        .table-links a.active,
        .table-links a:hover {
            background-color: #0071e3;
            color: #fff;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            font-size: 0.95rem;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #ccc;
            padding: 0.4rem 0.6rem;
            text-align: left;
        }

        .data-table th {
            background-color: #f5f5f5;
        }

        #mes2 {
            width: 300px;
            margin: 0 auto;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .stats-table th,
        .stats-table td {
            border: 1px solid #ccc;
            padding: 0.5rem;
            text-align: center;
        }

        .stats-table th {
            background-color: #f5f5f5;
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <header class="header">
        <nav class="nav container">
            <a href="../index.html" class="logo">SAÉ 23</a>
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

    <div class="container">
        <h1>Consultation des données</h1>
        <br>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php elseif (empty($visibleTables)): ?>
            <p>Aucune table visible n'a été définie par l'administrateur.</p>
        <?php else: ?>
            <!-- Section des liens vers les tables -->
            <div class="table-links">
                <?php foreach ($visibleTables as $table): ?>
                    <a href="consultation.php?table=<?= urlencode($table) ?>"
                       class="<?= $selectedTable === $table ? 'active' : '' ?>">
                        <?= htmlspecialchars($table) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Affichage des données de la table sélectionnée -->
            <?php if ($selectedTable): ?>
                <h2>Données de la table : <?= htmlspecialchars($selectedTable) ?></h2>
                <?php if (!empty($tableData)): ?>
                    <div style="overflow-x: auto;">
                        <table class="data-table">
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
                        <p><em>Affichage des 100 premiers enregistrements</em></p>
                    </div>
                <?php else: ?>
                    <p>La table est vide ou les données ne sont pas accessibles.</p>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
        <br>

        <!-- Section des statistiques des capteurs -->
        <div id="mes2">
            <h2>Choisir un Capteur</h2>
            <form method="GET">
                <!-- Conserver la table sélectionnée si elle existe -->
                <?php if ($selectedTable): ?>
                    <input type="hidden" name="table" value="<?= htmlspecialchars($selectedTable) ?>">
                <?php endif; ?>
                
                <label for="capteur">Capteur :</label>
                <select name="capteur" id="capteur" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($capteurs as $c): ?>
                        <option value="<?= htmlspecialchars($c['NomCap']) ?>" 
                                <?= ($filtreCapteur == $c['NomCap']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['NomCap']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Afficher</button>
            </form>
            <br>

            <?php if ($filtreCapteur && $resultat): ?>
                <h3>Statistiques pour le capteur : <?= htmlspecialchars($filtreCapteur) ?></h3>
                <table class="stats-table">
                    <tr>
                        <th>Valeur Min</th>
                        <th>Valeur Max</th>
                        <th>Moyenne</th>
                    </tr>
                    <tr>
                        <td><?= number_format($resultat['MinValeur'], 2) ?></td>
                        <td><?= number_format($resultat['MaxValeur'], 2) ?></td>
                        <td><?= number_format($resultat['MoyenneValeur'], 2) ?></td>
                    </tr>
                </table>
            <?php elseif ($filtreCapteur): ?>
                <p>Aucune donnée trouvée pour ce capteur.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-container">
            <p class="footer-text">© 2025 SAÉ 23 Groupe B – Tous droits réservés.</p>
            <p class="footer-legal">
                Mentions légales : ce site et son contenu sont la propriété de l'équipe SAÉ 23 du BUT R&T, IUT de Blagnac.
            </p>
            <p class="footer-github">
                Retrouvez-nous sur 
                <a href="https://github.com/hugo-diniz/SAE23Projet/tree/main" target="_blank" rel="noopener">GitHub</a>
            </p>
        </div>
    </footer>

    <?php if ($conn) mysqli_close($conn); ?>
</body>
</html>

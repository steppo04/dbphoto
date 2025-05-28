<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dbphoto";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connessione fallita: " . $conn->connect_error); }

// 1. Top 5 prodotti più venduti
$topProdotti = [];
$sql = "SELECT p.NOME, SUM(i.QUANTITA) AS tot FROM INCLUDE i JOIN PRODOTTO p ON i.ID_PRODOTTO = p.ID_PRODOTTO GROUP BY p.ID_PRODOTTO, p.NOME ORDER BY tot DESC LIMIT 5";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) { while ($row = $res->fetch_assoc()) { $topProdotti[] = $row; } }

// 2. Top 5 servizi più richiesti (da rullini)
$topServizi = [];
$sql = "SELECT s.NOME_SERVIZIO, COUNT(*) AS richieste FROM RULLINO r JOIN SERVIZIO s ON r.ID_SERVIZIO = s.ID_SERVIZIO GROUP BY s.ID_SERVIZIO, s.NOME_SERVIZIO ORDER BY richieste DESC LIMIT 5";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) { while ($row = $res->fetch_assoc()) { $topServizi[] = $row; } }

// 3. Numero rullini elaborati
$numRullini = 0;
$res = $conn->query("SELECT COUNT(*) AS tot FROM RULLINO");
if ($res && $row = $res->fetch_assoc()) { $numRullini = $row['tot']; }

// 4. Tempi medi per stato ordine
$tempiMedi = [];
$sql = "SELECT s.STATO, AVG(DATEDIFF(g2.DATA_INIZIO_STATO, g1.DATA_INIZIO_STATO)) AS giorni_medi
FROM GENERAZIONE_STATO g1
JOIN GENERAZIONE_STATO g2 ON g1.ID_ORDINE = g2.ID_ORDINE AND g2.CODICE_STATO_ORDINE = g1.CODICE_STATO_ORDINE + 1
JOIN STATO_ORDINE s ON g2.CODICE_STATO_ORDINE = s.CODICE_STATO_ORDINE
GROUP BY s.STATO";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) { while ($row = $res->fetch_assoc()) { $tempiMedi[] = $row; } }

// 5. Prodotto più venduto per categoria
$topProdottoPerCategoria = [];
$sql = "SELECT categoria, prodotto, tot
FROM (
    SELECT
        cat.NOME AS categoria,
        prod.NOME AS prodotto,
        SUM(i.QUANTITA) AS tot,
        ROW_NUMBER() OVER (PARTITION BY cat.ID_CATEGORIA ORDER BY SUM(i.QUANTITA) DESC, prod.ID_PRODOTTO ASC) as rn
    FROM CATEGORIA cat
    JOIN PRODOTTO prod ON cat.ID_CATEGORIA = prod.ID_CATEGORIA
    JOIN INCLUDE i ON prod.ID_PRODOTTO = i.ID_PRODOTTO
    GROUP BY cat.ID_CATEGORIA, cat.NOME, prod.ID_PRODOTTO, prod.NOME
) t
WHERE rn = 1
ORDER BY categoria, prodotto";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $topProdottoPerCategoria[] = $row;
    }
}

$adminId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Statistiche</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f1f1f1; margin: 0; }
        .admin-container { display: flex; min-height: 100vh; }
        .sidebar {
            width: 220px;
            background: #007bff;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 30px 0 0 0;
            min-height: 100vh;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 16px 30px;
            font-size: 18px;
            transition: background 0.2s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #0056b3;
        }
        .main {
            flex: 1;
            padding: 40px 50px;
            background: #f9f9f9;
        }
        h1 { color: #007bff; margin-bottom: 20px; }
        .stats-boxes {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        .stat-box {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 22px 30px;
            min-width: 180px;
            text-align: center;
        }
        .stat-title { color: #555; font-size: 15px; margin-bottom: 8px; }
        .stat-value { color: #007bff; font-size: 2em; font-weight: bold; }
        .table-section { margin-bottom: 32px; }
        .stat-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 10px;
        }
        .stat-table th, .stat-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .stat-table thead {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <a href="admin.php?id=<?= $adminId ?>">Home</a>
        <a href="admin_prodotti.php?id=<?= $adminId ?>">Prodotti</a>
        <a href="admin_ordini.php?id=<?= $adminId ?>">Ordini</a>
        <a href="admin_utenti.php?id=<?= $adminId ?>">Utenti</a>
        <a href="admin_recensioni.php?id=<?= $adminId ?>">Recensioni</a>
        <a href="admin_statistiche.php?id=<?= $adminId ?>" class="active">Statistiche</a>
    </div>
    <div class="main">
        <h1>Statistiche</h1>
        <div class="stats-boxes">
            <div class="stat-box">
                <div class="stat-title">Rullini elaborati</div>
                <div class="stat-value"><?= $numRullini ?></div>
            </div>
        </div>
        <div class="table-section">
            <h2>Top 5 prodotti più venduti</h2>
            <table class="stat-table">
                <thead><tr><th>Prodotto</th><th>Quantità venduta</th></tr></thead>
                <tbody>
                <?php foreach ($topProdotti as $prod): ?>
                    <tr><td><?= htmlspecialchars($prod['NOME']) ?></td><td><?= $prod['tot'] ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="table-section">
            <h2>Top 5 servizi più richiesti</h2>
            <table class="stat-table">
                <thead><tr><th>Servizio</th><th>Richieste</th></tr></thead>
                <tbody>
                <?php foreach ($topServizi as $serv): ?>
                    <tr><td><?= htmlspecialchars($serv['NOME_SERVIZIO']) ?></td><td><?= $serv['richieste'] ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="table-section">
            <h2>Tempi medi per stato ordine</h2>
            <table class="stat-table">
                <thead><tr><th>Stato</th><th>Giorni medi</th></tr></thead>
                <tbody>
                <?php foreach ($tempiMedi as $tm): ?>
                    <tr><td><?= htmlspecialchars($tm['STATO']) ?></td><td><?= $tm['giorni_medi'] !== null ? number_format($tm['giorni_medi'], 2, ',', '.') : '-' ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="table-section">
            <h2>Prodotto più venduto per categoria</h2>
            <table class="stat-table">
                <thead><tr><th>Categoria</th><th>Prodotto</th><th>Quantità venduta</th></tr></thead>
                <tbody>
                <?php if (!empty($topProdottoPerCategoria)): ?>
                    <?php foreach ($topProdottoPerCategoria as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['categoria']) ?></td>
                            <td><?= htmlspecialchars($row['prodotto']) ?></td>
                            <td><?= htmlspecialchars($row['tot']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3">Nessun dato disponibile</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html> 
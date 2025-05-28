<?php
session_start();
// Qui puoi aggiungere un controllo di login admin se necessario

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dbphoto";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connessione fallita: " . $conn->connect_error); }

// Recupero id admin da GET o sessione
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $adminId = intval($_GET['id']);
    $_SESSION['admin_id'] = $adminId;
} elseif (isset($_SESSION['admin_id'])) {
    $adminId = intval($_SESSION['admin_id']);
} else {
    header('Location: login.php');
    exit();
}

// Statistiche principali
// Rullini elaborati
$numRullini = 0;
$res = $conn->query("SELECT COUNT(*) AS tot FROM RULLINO");
if ($res && $row = $res->fetch_assoc()) { $numRullini = $row['tot']; }
// Top prodotto
$topProdotto = null;
$res = $conn->query("SELECT p.NOME, SUM(i.QUANTITA) AS tot FROM INCLUDE i JOIN PRODOTTO p ON i.ID_PRODOTTO = p.ID_PRODOTTO GROUP BY p.ID_PRODOTTO, p.NOME ORDER BY tot DESC LIMIT 1");
if ($res && $row = $res->fetch_assoc()) { $topProdotto = $row['NOME'] . ' (' . $row['tot'] . ')'; }
// Top servizio
$topServizio = null;
$res = $conn->query("SELECT s.NOME_SERVIZIO, COUNT(*) AS richieste FROM RULLINO r JOIN SERVIZIO s ON r.ID_SERVIZIO = s.ID_SERVIZIO GROUP BY s.ID_SERVIZIO, s.NOME_SERVIZIO ORDER BY richieste DESC LIMIT 1");
if ($res && $row = $res->fetch_assoc()) { $topServizio = $row['NOME_SERVIZIO'] . ' (' . $row['richieste'] . ')'; }
// Voto medio di tutte le recensioni
$mediaGlobale = '-';
$res = $conn->query("SELECT AVG(VOTO) as media FROM VALUTAZIONE_CRITERIO");
if (
    $res &&
    ($row = $res->fetch_assoc()) &&
    isset($row['media']) &&
    is_numeric($row['media'])
) {
    $mediaGlobale = number_format($row['media'], 2, ',', '.');
}
// Recupero criterio con voto medio più basso e più alto
$criterioMin = $criterioMax = null;
$minVoto = $maxVoto = null;
$res = $conn->query("SELECT c.NOME, AVG(v.VOTO) as media FROM VALUTAZIONE_CRITERIO v JOIN CRITERIO_DA_VALUTARE c ON v.ID_CRITERIO = c.ID_CRITERIO GROUP BY c.ID_CRITERIO, c.NOME ORDER BY media ASC");
if ($res && $res->num_rows > 0) {
    $rows = [];
    while ($row = $res->fetch_assoc()) { $rows[] = $row; }
    if (count($rows) > 0) {
        $criterioMin = $rows[0]['NOME'];
        $minVoto = number_format($rows[0]['media'], 2, ',', '.');
        $criterioMax = $rows[count($rows)-1]['NOME'];
        $maxVoto = number_format($rows[count($rows)-1]['media'], 2, ',', '.');
    }
}
// Ultime 3 recensioni
$ultimeRecensioni = [];
$sql = "SELECT r.ID_RECENSIONE, r.ID_ORDINE, r.DATA, r.COMMENTO, o.ID_UTENTE, u.NOME, u.COGNOME
        FROM RECENSIONE r
        JOIN ORDINE o ON r.ID_ORDINE = o.ID_ORDINE
        JOIN UTENTE u ON o.ID_UTENTE = u.ID_UTENTE
        ORDER BY r.DATA DESC LIMIT 3";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) { $ultimeRecensioni[] = $row; }
}
// Recupera voti criteri per queste recensioni
$recIds = array_column($ultimeRecensioni, 'ID_RECENSIONE');
$mediaRec = [];
if ($recIds) {
    $in = implode(',', array_map('intval', $recIds));
    $sql = "SELECT ID_RECENSIONE, AVG(VOTO) as media FROM VALUTAZIONE_CRITERIO WHERE ID_RECENSIONE IN ($in) GROUP BY ID_RECENSIONE";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) { $mediaRec[$row['ID_RECENSIONE']] = $row['media']; }
    }
}
// Ultimi 3 ordini
$ultimiOrdini = [];
$sql = "SELECT o.ID_ORDINE, o.DATA_ORDINE, o.TIPO_ORDINE, u.NOME, u.COGNOME, s.STATO
        FROM ORDINE o
        JOIN UTENTE u ON o.ID_UTENTE = u.ID_UTENTE
        JOIN (
            SELECT ID_ORDINE, MAX(CODICE_STATO_ORDINE) AS max_stato
            FROM GENERAZIONE_STATO
            GROUP BY ID_ORDINE
        ) gs ON o.ID_ORDINE = gs.ID_ORDINE
        JOIN STATO_ORDINE s ON gs.max_stato = s.CODICE_STATO_ORDINE
        ORDER BY o.DATA_ORDINE DESC LIMIT 3";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) { $ultimiOrdini[] = $row; }
}
// Recupero dati admin da id passato via GET
$res = $conn->query("SELECT NOME, COGNOME, EMAIL FROM UTENTE WHERE ID_UTENTE = $adminId AND RUOLO = 'admin'");
if (!$res || $res->num_rows === 0) {
    die('Amministratore non trovato.');
}
$adminRow = $res->fetch_assoc();
$adminNome = $adminRow['NOME'] . ' ' . $adminRow['COGNOME'];
$adminEmail = $adminRow['EMAIL'];
$adminUltimoAccesso = date('Y-m-d H:i'); // Puoi sostituire con un campo reale se disponibile
$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
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
            padding: 40px 5vw 40px 5vw;
            background: #f9f9f9;
        }
        h1 { color: #007bff; margin-bottom: 32px; font-size: 2.2em; letter-spacing: -1px; }
        .stats {
            display: flex;
            gap: 32px;
            margin-bottom: 38px;
            flex-wrap: wrap;
        }
        .stat-box {
            background: linear-gradient(135deg, #e3f0ff 0%, #f8fbff 100%);
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(0,123,255,0.08);
            padding: 32px 38px;
            min-width: 220px;
            text-align: center;
            flex: 1 1 220px;
            transition: box-shadow 0.2s;
        }
        .stat-box:hover {
            box-shadow: 0 8px 32px rgba(0,123,255,0.13);
        }
        .stat-title { color: #555; font-size: 1.1em; margin-bottom: 10px; letter-spacing: 0.5px; }
        .stat-value { color: #007bff; font-size: 2.3em; font-weight: bold; margin-bottom: 2px; }
        .stat-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 10px;
        }
        .stat-table th, .stat-table td {
            padding: 10px 8px;
            border: 1px solid #e3e3e3;
            text-align: center;
        }
        .stat-table thead {
            background-color: #007bff;
            color: white;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 36px;
            margin-bottom: 38px;
        }
        .dashboard-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.07);
            padding: 28px 24px 22px 24px;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .dashboard-card h2 {
            color: #007bff;
            font-size: 1.18em;
            margin-bottom: 16px;
            letter-spacing: 0.2px;
        }
        .dashboard-card table {
            font-size: 1em;
        }
        .suggestions {
            margin-top: 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 24px 30px;
        }
        .suggestions h2 { color: #007bff; font-size: 1.2em; margin-bottom: 12px; }
        .suggestions ul { margin: 0; padding-left: 18px; }
        .suggestions li { margin-bottom: 8px; color: #444; }
        @media (max-width: 900px) {
            .main { padding: 30px 2vw 30px 2vw; }
            .stats { flex-direction: column; gap: 18px; }
            .dashboard-grid { grid-template-columns: 1fr; gap: 22px; }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <a href="admin.php?id=<?= $adminId ?>" class="active">Home</a>
        <a href="admin_prodotti.php?id=<?= $adminId ?>">Prodotti</a>
        <a href="admin_ordini.php?id=<?= $adminId ?>">Ordini</a>
        <a href="admin_utenti.php?id=<?= $adminId ?>">Utenti</a>
        <a href="admin_recensioni.php?id=<?= $adminId ?>">Recensioni</a>
        <a href="admin_statistiche.php?id=<?= $adminId ?>">Statistiche</a>
    </div>
    <div class="main">
        <div class="dashboard-card" style="max-width:440px; margin:0 auto 32px auto; box-shadow:0 6px 24px rgba(0,123,255,0.10); background:linear-gradient(120deg,#e3f0ff 0%,#f8fbff 100%); display:flex; align-items:center; gap:24px;">
            <div style="flex-shrink:0; width:70px; height:70px; border-radius:50%; background:#007bff; display:flex; align-items:center; justify-content:center; color:white; font-size:2.2em; font-weight:bold;">
                <?= strtoupper(substr($adminNome,0,1)) ?>
            </div>
            <div style="flex:1;">
                <div style="font-size:1.35em; color:#007bff; font-weight:600; margin-bottom:2px;">Benvenuto, <?= htmlspecialchars($adminNome) ?></div>
                <div style="color:#444; font-size:1.05em; margin-bottom:2px;"><b>Email:</b> <?= htmlspecialchars($adminEmail) ?></div>
                <div style="color:#888; font-size:0.98em; margin-bottom:8px;"><b>Ultimo accesso:</b> <?= htmlspecialchars($adminUltimoAccesso) ?></div>
                <a href="login.php" style="display:inline-block; padding:7px 18px; background:#dc3545; color:white; border-radius:6px; text-decoration:none; font-size:1em; font-weight:500; margin-top:2px;">Logout</a>
            </div>
        </div>
        <h1>Dashboard Amministratore</h1>
        <div class="stats">
            <div class="stat-box">
                <div class="stat-title">Rullini elaborati</div>
                <div class="stat-value"><?= $numRullini ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-title">Prodotto più venduto</div>
                <div class="stat-value"><?= $topProdotto ? htmlspecialchars($topProdotto) : '--' ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-title">Servizio più richiesto</div>
                <div class="stat-value"><?= $topServizio ? htmlspecialchars($topServizio) : '--' ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-title">Voto medio recensioni</div>
                <div class="stat-value"><?= $mediaGlobale ?></div>
                <?php if ($criterioMin && $criterioMax): ?>
                    <div style="margin-top:10px; font-size:0.98em; color:#444;">
                        <span style="color:#dc3545;">Peggiore:</span> <b><?= htmlspecialchars($criterioMin) ?></b> (<?= $minVoto ?>)<br>
                        <span style="color:#28a745;">Migliore:</span> <b><?= htmlspecialchars($criterioMax) ?></b> (<?= $maxVoto ?>)
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>Ultimi 3 ordini</h2>
                <table class="stat-table">
                    <thead><tr><th>ID</th><th>Utente</th><th>Data</th><th>Stato</th><th>Tipo</th></tr></thead>
                    <tbody>
                    <?php foreach ($ultimiOrdini as $ord): ?>
                        <tr>
                            <td><?= $ord['ID_ORDINE'] ?></td>
                            <td><?= htmlspecialchars($ord['NOME'] . ' ' . $ord['COGNOME']) ?></td>
                            <td><?= htmlspecialchars($ord['DATA_ORDINE']) ?></td>
                            <td><?= htmlspecialchars($ord['STATO']) ?></td>
                            <td><?= htmlspecialchars($ord['TIPO_ORDINE']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="dashboard-card">
                <h2>Ultime 3 recensioni</h2>
                <table class="stat-table">
                    <thead><tr><th>Utente</th><th>Ordine</th><th>Data</th><th>Media</th><th>Commento</th></tr></thead>
                    <tbody>
                    <?php foreach ($ultimeRecensioni as $rec): ?>
                        <tr>
                            <td><?= htmlspecialchars($rec['NOME'] . ' ' . $rec['COGNOME']) ?></td>
                            <td><?= $rec['ID_ORDINE'] ?></td>
                            <td><?= htmlspecialchars($rec['DATA']) ?></td>
                            <td><?= isset($mediaRec[$rec['ID_RECENSIONE']]) ? number_format($mediaRec[$rec['ID_RECENSIONE']], 2, ',', '.') : '-' ?></td>
                            <td><?= htmlspecialchars($rec['COMMENTO']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>

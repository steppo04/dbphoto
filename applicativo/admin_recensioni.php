<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dbphoto";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connessione fallita: " . $conn->connect_error); }

// Gestione elimina recensione
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['azione']) && $_POST['azione'] === 'elimina' &&
    isset($_POST['id_recensione'])
) {
    $idRec = intval($_POST['id_recensione']);
    $conn->query("DELETE FROM VALUTAZIONE_CRITERIO WHERE ID_RECENSIONE = $idRec");
    $conn->query("DELETE FROM RECENSIONE WHERE ID_RECENSIONE = $idRec");
    $adminId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    header("Location: admin_recensioni.php?id=$adminId");
    exit();
}

// Recupera criteri
$criteri = [];
$res = $conn->query("SELECT ID_CRITERIO, NOME FROM CRITERIO_DA_VALUTARE ORDER BY ID_CRITERIO");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) { $criteri[$row['ID_CRITERIO']] = $row['NOME']; }
}

// Filtri
$where = [];
$params = [];
$types = "";
if (!empty($_GET['utente'])) {
    $where[] = "(u.NOME LIKE ? OR u.COGNOME LIKE ?)";
    $params[] = "%" . $_GET['utente'] . "%";
    $params[] = "%" . $_GET['utente'] . "%";
    $types .= "ss";
}
if (!empty($_GET['ordine'])) {
    $where[] = "r.ID_ORDINE = ?";
    $params[] = intval($_GET['ordine']);
    $types .= "i";
}
if (!empty($_GET['voto'])) {
    $where[] = "(
        SELECT AVG(VOTO) FROM VALUTAZIONE_CRITERIO vc WHERE vc.ID_RECENSIONE = r.ID_RECENSIONE
    ) >= ?";
    $params[] = floatval($_GET['voto']);
    $types .= "d";
}
if (!empty($_GET['data'])) {
    $where[] = "DATE(r.DATA) = ?";
    $params[] = $_GET['data'];
    $types .= "s";
}
$whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

$sql = "SELECT r.ID_RECENSIONE, r.ID_ORDINE, r.DATA, r.COMMENTO, u.NOME, u.COGNOME FROM RECENSIONE r JOIN ORDINE o ON r.ID_ORDINE = o.ID_ORDINE JOIN UTENTE u ON o.ID_UTENTE = u.ID_UTENTE $whereSql ORDER BY r.DATA DESC";
$stmt = $conn->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$res = $stmt->get_result();
$recensioni = [];
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) { $recensioni[] = $row; }
}
$stmt->close();

// Recupera voti criteri per tutte le recensioni mostrate
$recIds = array_column($recensioni, 'ID_RECENSIONE');
$criterioVoti = [];
if ($recIds) {
    $in = implode(',', array_map('intval', $recIds));
    $sql = "SELECT ID_RECENSIONE, ID_CRITERIO, VOTO FROM VALUTAZIONE_CRITERIO WHERE ID_RECENSIONE IN ($in)";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $criterioVoti[$row['ID_RECENSIONE']][$row['ID_CRITERIO']] = $row['VOTO'];
        }
    }
}
$conn->close();

$adminId = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Recensioni</title>
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
        .rec-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-bottom: 30px;
        }
        .rec-table th, .rec-table td {
            padding: 12px 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .rec-table thead {
            background-color: #007bff;
            color: white;
        }
        .criteri-voti {
            font-size: 1em;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .criteri-voti ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .criteri-voti li {
            margin-bottom: 6px;
            padding: 4px 0 4px 0;
            border-bottom: 1px solid #f1f1f1;
        }
        .criteri-voti li:last-child {
            border-bottom: none;
        }
        .criterio-nome { font-weight: 500; color: #007bff; }
        .criterio-voto { font-weight: bold; color: #222; margin-left: 8px; }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <a href="admin.php?id=<?= $adminId ?>">Home</a>
        <a href="admin_prodotti.php?id=<?= $adminId ?>">Prodotti</a>
        <a href="admin_ordini.php?id=<?= $adminId ?>">Ordini</a>
        <a href="admin_utenti.php?id=<?= $adminId ?>">Utenti</a>
        <a href="admin_recensioni.php?id=<?= $adminId ?>" class="active">Recensioni</a>
        <a href="admin_statistiche.php?id=<?= $adminId ?>">Statistiche</a>
    </div>
    <div class="main">
        <h1>Gestione Recensioni</h1>
        <table class="rec-table">
            <thead>
                <tr><th>ID</th><th>Utente</th><th>Ordine</th><th>Data</th><th>Voti criteri</th><th>Voto medio</th><th>Commento</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recensioni as $rec): ?>
                <tr>
                    <td><?= $rec['ID_RECENSIONE'] ?></td>
                    <td><?= htmlspecialchars($rec['NOME'] . ' ' . $rec['COGNOME']) ?></td>
                    <td><?= $rec['ID_ORDINE'] ?></td>
                    <td><?= htmlspecialchars($rec['DATA']) ?></td>
                    <td class="criteri-voti">
                        <ul>
                        <?php
                        $voti = [];
                        if (isset($criterioVoti[$rec['ID_RECENSIONE']])) {
                            foreach ($criteri as $idc => $nomec) {
                                if (isset($criterioVoti[$rec['ID_RECENSIONE']][$idc])) {
                                    $v = $criterioVoti[$rec['ID_RECENSIONE']][$idc];
                                    $voti[] = $v;
                                    echo '<li><span class="criterio-nome">' . htmlspecialchars($nomec) . '</span><span class="criterio-voto">' . $v . '</span></li>';
                                }
                            }
                        }
                        ?>
                        </ul>
                    </td>
                    <td>
                        <?php
                        if ($voti) {
                            echo number_format(array_sum($voti)/count($voti), 2, ',', '.');
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td><?= htmlspecialchars($rec['COMMENTO']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html> 
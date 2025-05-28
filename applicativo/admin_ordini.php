<?php
// Qui puoi aggiungere un controllo di login admin se necessario

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dbphoto";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connessione fallita: " . $conn->connect_error); }

// Aggiorna stato ordine
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['azione']) && $_POST['azione'] === 'aggiorna_stato' &&
    isset($_POST['id_ordine']) && isset($_POST['codice_stato'])
) {
    $idOrdine = intval($_POST['id_ordine']);
    $codiceStato = intval($_POST['codice_stato']);
    $stmt = $conn->prepare("INSERT INTO GENERAZIONE_STATO (CODICE_STATO_ORDINE, ID_ORDINE, DATA_INIZIO_STATO) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $codiceStato, $idOrdine);
    $stmt->execute();
    $stmt->close();
    $adminId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    header("Location: admin_ordini.php?id=$adminId");
    exit();
}

// Recupera stati disponibili
$stati = [
    1 => 'In lavorazione',
    2 => 'Pronto per la spedizione',
    3 => 'In transito',
    4 => 'Completato'
];

// Filtri
$where = [];
$params = [];
$types = "";
if (!empty($_GET['codice'])) {
    $where[] = "o.ID_ORDINE = ?";
    $params[] = intval($_GET['codice']);
    $types .= "i";
}
if (!empty($_GET['utente'])) {
    $where[] = "(u.NOME LIKE ? OR u.COGNOME LIKE ?)";
    $params[] = "%" . $_GET['utente'] . "%";
    $params[] = "%" . $_GET['utente'] . "%";
    $types .= "ss";
}
if (!empty($_GET['data'])) {
    $where[] = "DATE(o.DATA_ORDINE) = ?";
    $params[] = $_GET['data'];
    $types .= "s";
}
if (isset($_GET['stato']) && $_GET['stato'] !== "") {
    $where[] = "gs.max_stato = ?";
    $params[] = intval($_GET['stato']);
    $types .= "i";
}
$whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

// Query ordini
$sql = "
    SELECT o.ID_ORDINE, gs.DATA_INIZIO_STATO, o.TIPO_ORDINE, u.NOME, u.COGNOME, s.STATO, gs.max_stato
    FROM ORDINE o
    JOIN UTENTE u ON o.ID_UTENTE = u.ID_UTENTE
    JOIN (
        SELECT g1.ID_ORDINE, g1.CODICE_STATO_ORDINE as max_stato, g1.DATA_INIZIO_STATO
        FROM GENERAZIONE_STATO g1
        INNER JOIN (
            SELECT ID_ORDINE, MAX(DATA_INIZIO_STATO) as max_data
            FROM GENERAZIONE_STATO
            GROUP BY ID_ORDINE
        ) g2 ON g1.ID_ORDINE = g2.ID_ORDINE AND g1.DATA_INIZIO_STATO = g2.max_data
    ) gs ON o.ID_ORDINE = gs.ID_ORDINE
    JOIN STATO_ORDINE s ON gs.max_stato = s.CODICE_STATO_ORDINE
    $whereSql
    ORDER BY gs.DATA_INIZIO_STATO DESC
";
$stmt = $conn->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$res = $stmt->get_result();
$ordini = [];
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) { $ordini[] = $row; }
}
$stmt->close();
$conn->close();

$adminId = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Ordini</title>
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
        .filter-row { display: flex; gap: 12px; margin-bottom: 18px; }
        .filter-row input, .filter-row select { padding: 7px; border-radius: 5px; border: 1px solid #ccc; }
        .ord-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-bottom: 30px;
        }
        .ord-table th, .ord-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .ord-table thead {
            background-color: #007bff;
            color: white;
        }
        .btn {
            padding: 7px 16px;
            border-radius: 5px;
            border: none;
            font-size: 15px;
            margin: 0 3px;
            cursor: pointer;
        }
        .btn-edit { background: #ffc107; color: #333; }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <a href="admin.php?id=<?= $adminId ?>">Home</a>
        <a href="admin_prodotti.php?id=<?= $adminId ?>">Prodotti</a>
        <a href="admin_ordini.php?id=<?= $adminId ?>" class="active">Ordini</a>
        <a href="admin_utenti.php?id=<?= $adminId ?>">Utenti</a>
        <a href="admin_recensioni.php?id=<?= $adminId ?>">Recensioni</a>
        <a href="admin_statistiche.php?id=<?= $adminId ?>">Statistiche</a>
    </div>
    <div class="main">
        <h1>Gestione Ordini</h1>
        <form class="filter-row" method="get">
            <input type="text" name="codice" placeholder="Codice ordine">
            <input type="text" name="utente" placeholder="Nome utente">
            <input type="date" name="data">
            <select name="stato">
                <option value="">Tutti gli stati</option>
                <option value="1">In lavorazione</option>
                <option value="2">Pronto per la spedizione</option>
                <option value="3">In transito</option>
				<option value="4">Completato</option>
                <!-- Aggiungi altri stati se necessario -->
            </select>
            <button class="btn btn-edit" type="submit">Filtra</button>
        </form>
        <table class="ord-table">
            <thead>
                <tr><th>ID</th><th>Utente</th><th>Data</th><th>Tipo</th><th>Stato</th><th>Azioni</th></tr>
            </thead>
            <tbody>
                <?php foreach ($ordini as $ordine): ?>
                <tr>
                    <td><?= $ordine['ID_ORDINE'] ?></td>
                    <td><?= htmlspecialchars($ordine['NOME'] . ' ' . $ordine['COGNOME']) ?></td>
                    <td><?= htmlspecialchars($ordine['DATA_INIZIO_STATO']) ?></td>
                    <td><?= htmlspecialchars($ordine['TIPO_ORDINE']) ?></td>
                    <td><?= htmlspecialchars($ordine['STATO']) ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="azione" value="aggiorna_stato">
                            <input type="hidden" name="id_ordine" value="<?= $ordine['ID_ORDINE'] ?>">
                            <select name="codice_stato" required>
                                <?php foreach ([1,2,3,4] as $codice): ?>
                                    <option value="<?= $codice ?>" <?= $codice == $ordine['max_stato'] ? 'selected' : '' ?>><?= htmlspecialchars($stati[$codice]) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-edit" type="submit">Aggiorna</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html> 
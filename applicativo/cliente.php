<?php
// pagina: cliente.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dbphoto";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

if (!isset($_GET["id"])) {
    die("ID utente non specificato.");
}
$userId = intval($_GET["id"]);

// Recupera dati utente per visualizzazione
$sql = "SELECT * FROM UTENTE WHERE ID_UTENTE = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Utente non trovato.");
}
$utente = $result->fetch_assoc();
$stmt->close();

// Conteggio ordini
$sql_count = "SELECT COUNT(*) AS totale FROM ORDINE WHERE ID_UTENTE = ?";
$stmt = $conn->prepare($sql_count);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$num_ordini = $result->fetch_assoc()["totale"];
$stmt->close();

// Ordini utente
$sql_ordini = "
    SELECT o.ID_ORDINE, o.DATA_ORDINE, o.IMPORTO, s.STATO
    FROM ORDINE o
    JOIN (
        SELECT ID_ORDINE, MAX(CODICE_STATO_ORDINE) AS max_stato
        FROM GENERAZIONE_STATO
        GROUP BY ID_ORDINE
    ) gs ON o.ID_ORDINE = gs.ID_ORDINE
    JOIN STATO_ORDINE s ON gs.max_stato = s.CODICE_STATO_ORDINE
    WHERE o.ID_UTENTE = ?
    ORDER BY o.DATA_ORDINE DESC
";
$stmt = $conn->prepare($sql_ordini);
$stmt->bind_param("i", $userId);
$stmt->execute();
$ordini = $stmt->get_result();
$stmt->close();

// Gestione aggiunta indirizzo
$indirizzoAggiunto = false;
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['aggiungi_indirizzo']) &&
    isset($_POST['via']) &&
    isset($_POST['citta']) &&
    isset($_POST['cap']) &&
    isset($_POST['provincia']) &&
    isset($_POST['regione'])
) {
    $via = $conn->real_escape_string($_POST['via']);
    $citta = $conn->real_escape_string($_POST['citta']);
    $cap = $conn->real_escape_string($_POST['cap']);
    $provincia = $conn->real_escape_string($_POST['provincia']);
    $regione = $conn->real_escape_string($_POST['regione']);
    // Recupera nuovo ID_INDIRIZZO
    $res = $conn->query("SELECT MAX(ID_INDIRIZZO) AS max_id FROM INDIRIZZI_DI_SPEDIZIONE");
    $row = $res->fetch_assoc();
    $newAddressId = ($row["max_id"] !== null) ? $row["max_id"] + 1 : 1;
    $stmt = $conn->prepare("INSERT INTO INDIRIZZI_DI_SPEDIZIONE (ID_INDIRIZZO, VIA, CITTA, CAP, PROVINCIA, REGIONE, ID_UTENTE) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssi", $newAddressId, $via, $citta, $cap, $provincia, $regione, $userId);
    if ($stmt->execute()) {
        $indirizzoAggiunto = true;
        // Redirect per evitare doppio submit
        header("Location: cliente.php?id=$userId&indirizzo=ok");
        exit();
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Profilo Cliente</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f1f1;
            padding: 30px;
        }
        .container {
            background: white;
            max-width: 1000px;
            margin: 0 auto;
            padding: 25px 30px 30px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0;
        }
        .user-info {
            flex: 1;
            margin-bottom: 0;
        }
        .user-info label {
            font-weight: bold;
            color: #444;
        }
        .user-info span {
            color: #333;
            margin-left: 8px;
        }
        .sidebar {
            min-width: 220px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-right: 40px;
        }
        .order-btn {
            display: block;
            width: 100%;
            padding: 12px 0;
            background-color: #28a745;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            border: none;
            margin: 0;
        }
        .rec-btn {
            background-color: #ffc107;
            color: #333;
        }
        .add-btn {
            background-color: #007bff;
            color: white;
        }
        .main-content {
            flex: 3;
        }
        h2 {
            color: #007bff;
            margin-bottom: 10px;
            text-align: left;
        }
        .user-table {
            margin-bottom: 18px;
            width: 100%;
            border-collapse: collapse;
        }
        .user-table td {
            padding: 6px 12px;
            border: none;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }
        .orders-table th, .orders-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .orders-table thead {
            background-color: #007bff;
            color: white;
        }
        .msg-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
            text-align: center;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
<div class="container">
    <div style="text-align:right; margin-bottom:10px;">
        <a href="login.php" style="display:inline-block; padding:7px 18px; background:#dc3545; color:white; border-radius:6px; text-decoration:none; font-size:1em; font-weight:500;">Logout</a>
    </div>
    <div class="top-row">
        <div class="sidebar">
            <a href="nuovo_ordine.php?id=<?= $userId ?>" class="order-btn">Esegui un nuovo ordine</a>
            <a href="recensione.php?id=<?= $userId ?>" class="order-btn rec-btn">Scrivi una recensione</a>
            <a href="aggiungi_indirizzo.php?id=<?= $userId ?>" class="order-btn add-btn">Aggiungi indirizzo</a>
        </div>
        <div class="user-info">
            <h2>Profilo Cliente</h2>
            <table class="user-table">
                <tr><td><label>Nome:</label></td><td><span><?= htmlspecialchars($utente["NOME"]) ?></span></td></tr>
                <tr><td><label>Cognome:</label></td><td><span><?= htmlspecialchars($utente["COGNOME"]) ?></span></td></tr>
                <tr><td><label>Email:</label></td><td><span><?= htmlspecialchars($utente["EMAIL"]) ?></span></td></tr>
                <tr><td><label>Telefono:</label></td><td><span><?= htmlspecialchars($utente["TELEFONO"]) ?></span></td></tr>
                <tr><td><label>Numero ordini:</label></td><td><span><?= $num_ordini ?></span></td></tr>
                <tr><td><label>Account attivo:</label></td><td><span><?= $utente["ATTIVO"] === "Y" ? "Sì" : "No" ?></span></td></tr>
            </table>
            <?php if (isset($_GET['indirizzo']) && $_GET['indirizzo'] === 'ok'): ?>
                <div class="msg-success">Indirizzo aggiunto con successo!</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="main-content">
        <h2>I tuoi ordini</h2>
        <?php if ($ordini->num_rows > 0): ?>
            <table class="orders-table">
                <thead>
                <tr><th>ID Ordine</th><th>Data Ordine</th><th>Importo</th><th>Stato Attuale</th></tr>
                </thead>
                <tbody>
                <?php while ($ordine = $ordini->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($ordine["ID_ORDINE"]) ?></td>
                        <td><?= htmlspecialchars($ordine["DATA_ORDINE"]) ?></td>
                        <td>€ <?= number_format($ordine["IMPORTO"], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($ordine["STATO"]) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nessun ordine effettuato.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

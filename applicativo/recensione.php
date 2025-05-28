<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dbphoto";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connessione fallita: " . $conn->connect_error); }
if (!isset($_GET["id"])) { die("ID utente non specificato."); }
$userId = intval($_GET["id"]);

// Recupera ordini dell'utente NON ancora recensiti
$sqlOrd = "SELECT o.ID_ORDINE FROM ORDINE o LEFT JOIN RECENSIONE r ON o.ID_ORDINE = r.ID_ORDINE WHERE o.ID_UTENTE = $userId AND r.ID_ORDINE IS NULL ORDER BY o.ID_ORDINE DESC";
$resultOrd = $conn->query($sqlOrd);
$ordini = [];
if ($resultOrd->num_rows > 0) { while ($row = $resultOrd->fetch_assoc()) { $ordini[] = $row; } }

// Recupera criteri di valutazione
$sqlCrit = "SELECT ID_CRITERIO, NOME FROM CRITERIO_DA_VALUTARE";
$resultCrit = $conn->query($sqlCrit);
$criteri = [];
if ($resultCrit->num_rows > 0) { while ($row = $resultCrit->fetch_assoc()) { $criteri[] = $row; } }

$recensioneConfermata = false;
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['id_ordine']) &&
    isset($_POST['commento']) &&
    isset($_POST['voti']) &&
    is_array($_POST['voti'])
) {
    $idOrdine = intval($_POST['id_ordine']);
    $commento = $conn->real_escape_string($_POST['commento']);
    $voti = $_POST['voti'];

    // Recupera nuovo ID_RECENSIONE
    $res = $conn->query("SELECT MAX(ID_RECENSIONE) AS max_id FROM RECENSIONE");
    $row = $res->fetch_assoc();
    $idRecensione = ($row["max_id"] !== null) ? $row["max_id"] + 1 : 1;

    $conn->begin_transaction();
    try {
        // Inserisci recensione
        $stmt = $conn->prepare("INSERT INTO RECENSIONE (ID_RECENSIONE, ID_ORDINE, DATA, COMMENTO) VALUES (?, ?, NOW(), ?)");
        $stmt->bind_param("iis", $idRecensione, $idOrdine, $commento);
        $stmt->execute();
        $stmt->close();

        // Inserisci voti per ogni criterio
        $stmt = $conn->prepare("INSERT INTO VALUTAZIONE_CRITERIO (ID_RECENSIONE, ID_CRITERIO, VOTO) VALUES (?, ?, ?)");
        foreach ($voti as $idCriterio => $voto) {
            $idCriterio = intval($idCriterio);
            $voto = intval($voto);
            $stmt->bind_param("iii", $idRecensione, $idCriterio, $voto);
            $stmt->execute();
        }
        $stmt->close();

        $conn->commit();
        $recensioneConfermata = true;
    } catch (Exception $e) {
        $conn->rollback();
        die("Errore durante l'inserimento recensione: " . $e->getMessage());
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8" />
<title>Scrivi una recensione</title>
<style>
    body { font-family: 'Segoe UI', sans-serif; padding: 20px; background: #f9f9f9;}
    .container { max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);}
    label { font-weight: bold; margin-top: 10px; display: block; }
    textarea { width: 100%; min-height: 80px; border-radius: 6px; border: 1px solid #ccc; padding: 8px; font-size: 15px; }
    select, input[type=number] { padding: 8px; border-radius: 6px; border: 1px solid #ccc; font-size: 15px; margin-bottom: 10px; }
    .btn { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; margin-top: 15px; }
    .btn:hover { background: #218838; }
    .msg-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 20px; border-radius: 8px; margin-bottom: 30px; text-align: center; font-size: 1.2em; }
    .stars { color: #ffc107; font-size: 1.3em; }
</style>
<?php if ($recensioneConfermata): ?>
<meta http-equiv="refresh" content="3;url=cliente.php?id=<?= $userId ?>">
<?php endif; ?>
</head>
<body>
<div class="container">
<?php if ($recensioneConfermata): ?>
    <div class="msg-success">
        Recensione inviata con successo!<br>
        Grazie per il tuo feedback.
    </div>
<?php elseif (empty($ordini)): ?>
    <div class="msg-success">
        Non hai ordini da recensire.
    </div>
<?php else: ?>
    <h2>Scrivi una recensione</h2>
    <form method="post">
        <label for="id_ordine">Ordine da recensire:</label>
        <select name="id_ordine" required>
            <option value="">-- Seleziona ordine --</option>
            <?php foreach ($ordini as $ordine): ?>
                <option value="<?= $ordine['ID_ORDINE'] ?>">Ordine #<?= $ordine['ID_ORDINE'] ?></option>
            <?php endforeach; ?>
        </select>
        <label for="commento">Commento:</label>
        <textarea name="commento" id="commento" maxlength="255" required></textarea>
        <?php foreach ($criteri as $criterio): ?>
            <label><?= htmlspecialchars($criterio['NOME']) ?>:</label>
            <select name="voti[<?= $criterio['ID_CRITERIO'] ?>]" required>
                <option value="">-- Vota --</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>"><?= str_repeat('★', $i) ?> <?= $i ?></option>
                <?php endfor; ?>
            </select>
        <?php endforeach; ?>
        <button type="submit" class="btn">Invia recensione</button>
    </form>
<?php endif; ?>
</div>
</body>
</html> 
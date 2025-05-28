<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dbphoto";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connessione fallita: " . $conn->connect_error); }
if (!isset($_GET["id"])) { die("ID utente non specificato."); }
$userId = intval($_GET["id"]);

// Servizi disponibili
$sqlServ = "SELECT ID_SERVIZIO, NOME_SERVIZIO, PREZZO FROM SERVIZIO";
$resultServ = $conn->query($sqlServ);
$servizi = [];
if ($resultServ->num_rows > 0) { while ($row = $resultServ->fetch_assoc()) { $servizi[] = $row; } }

// Laboratori disponibili
$sqlLab = "SELECT ID_LABORATORIO, NOME, CITTA FROM LABORATORIO";
$resultLab = $conn->query($sqlLab);
$laboratori = [];
if ($resultLab->num_rows > 0) { while ($row = $resultLab->fetch_assoc()) { $laboratori[] = $row; } }

// Indirizzi utente
$sqlAddr = "SELECT ID_INDIRIZZO, VIA, CITTA, CAP FROM INDIRIZZI_DI_SPEDIZIONE WHERE ID_UTENTE = $userId";
$resultAddr = $conn->query($sqlAddr);
$indirizzi = [];
if ($resultAddr->num_rows > 0) { while ($row = $resultAddr->fetch_assoc()) { $indirizzi[] = $row; } }

// GESTIONE INSERIMENTO ORDINE
$ordineConfermato = false;
$idOrdine = null;
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['id_utente']) &&
    isset($_POST['dati_rullini']) &&
    isset($_POST['id_laboratorio']) &&
    isset($_POST['id_indirizzo']) &&
    isset($_POST['metodo_pagamento']) &&
    isset($_POST['valuta'])
) {
    $idUtente = intval($_POST['id_utente']);
    $idLaboratorio = intval($_POST['id_laboratorio']);
    $idIndirizzo = intval($_POST['id_indirizzo']);
    $metodoPagamento = $conn->real_escape_string($_POST['metodo_pagamento']);
    $valuta = $conn->real_escape_string($_POST['valuta']);
    $datiRullini = json_decode($_POST['dati_rullini'], true);

    if ($datiRullini && is_array($datiRullini) && count($datiRullini) > 0) {
        // Calcolo importo totale
        $importo = 0;
        foreach ($datiRullini as $item) {
            $importo += floatval($item['prezzo']);
        }

        // Recupera nuovo ID_ORDINE, ID_PAGAMENTO, ID_RULLINO
        $res = $conn->query("SELECT MAX(ID_ORDINE) AS max_id FROM ORDINE");
        $row = $res->fetch_assoc();
        $idOrdine = ($row["max_id"] !== null) ? $row["max_id"] + 1 : 1;

        $res = $conn->query("SELECT MAX(ID_PAGAMENTO) AS max_id FROM PAGAMENTO");
        $row = $res->fetch_assoc();
        $idPagamento = ($row["max_id"] !== null) ? $row["max_id"] + 1 : 1;

        $res = $conn->query("SELECT MAX(ID_RULLINO) AS max_id FROM RULLINO");
        $row = $res->fetch_assoc();
        $idRullino = ($row["max_id"] !== null) ? $row["max_id"] + 1 : 1;

        $conn->begin_transaction();
        try {
            // 1. Inserisci PAGAMENTO
            $stmt = $conn->prepare("INSERT INTO PAGAMENTO (ID_PAGAMENTO, METODO_DI_PAGAMENTO, VALUTA) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $idPagamento, $metodoPagamento, $valuta);
            $stmt->execute();
            $stmt->close();

            // 2. Inserisci ORDINE con ID_PAGAMENTO valorizzato
            $stmt = $conn->prepare("INSERT INTO ORDINE (ID_ORDINE, ID_PAGAMENTO, DATA_ORDINE, TIPO_ORDINE, ID_LABORATORIO, ID_INDIRIZZO, ID_UTENTE, IMPORTO) VALUES (?, ?, NOW(), 'SVILUPPO', ?, ?, ?, ?)");
            $stmt->bind_param("iiiiid", $idOrdine, $idPagamento, $idLaboratorio, $idIndirizzo, $idUtente, $importo);
            $stmt->execute();
            $stmt->close();

            // 3. Inserisci rullini
            $stmt = $conn->prepare("INSERT INTO RULLINO (ID_RULLINO, NUMERO_SCATTI, RISOLUZIONE, ID_SERVIZIO, ID_ORDINE) VALUES (?, ?, ?, ?, ?)");
            foreach ($datiRullini as $item) {
                $numScatti = intval($item['numero_scattti']);
                $risoluzione = $item['risoluzione'];
                $idServizio = intval($item['id_servizio']);
                $stmt->bind_param("issii", $idRullino, $numScatti, $risoluzione, $idServizio, $idOrdine);
                $stmt->execute();
                $idRullino++;
            }
            $stmt->close();

            // 4. Stato iniziale ordine
            $stmt = $conn->prepare("INSERT INTO GENERAZIONE_STATO (CODICE_STATO_ORDINE, ID_ORDINE, DATA_INIZIO_STATO) VALUES (1, ?, NOW())");
            $stmt->bind_param("i", $idOrdine);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $ordineConfermato = true;
        } catch (Exception $e) {
            $conn->rollback();
            die("Errore durante l'inserimento ordine: " . $e->getMessage());
        }
    } else {
        die("Nessun rullino inserito o dati non validi.");
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8" />
<title>Ordine Sviluppo Rullino</title>
<style>
    body { font-family: 'Segoe UI', sans-serif; padding: 20px; background: #f9f9f9;}
    .container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);}
    table { width: 100%; border-collapse: collapse; margin-top: 20px;}
    th, td { padding: 10px; border: 1px solid #ddd; text-align: center;}
    th { background: #007bff; color: white; }
    .btn { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; }
    .btn:hover { background: #218838; }
    .total { font-weight: bold; font-size: 1.2em; margin-top: 20px; }
    input[type=number], select, input[type=text] { margin-top: 5px; margin-bottom: 15px; }
    .msg-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 20px; border-radius: 8px; margin-bottom: 30px; text-align: center; font-size: 1.2em; }
</style>
<?php if ($ordineConfermato && $idOrdine): ?>
<meta http-equiv="refresh" content="3;url=cliente.php?id=<?= $userId ?>">
<?php endif; ?>
<script>
let carrelloRullini = [];
function aggiungiRullino() {
    const numScatti = document.getElementById('numero_scattti').value;
    const risoluzioneSel = document.getElementById('risoluzione');
    const risoluzione = risoluzioneSel.options[risoluzioneSel.selectedIndex].text;
    const selectServizio = document.getElementById('id_servizio');
    const idServizio = selectServizio.value;
    const nomeServizio = selectServizio.options[selectServizio.selectedIndex].text;
    const prezzo = parseFloat(selectServizio.selectedOptions[0].getAttribute('data-prezzo'));
    if (!numScatti || !risoluzione || !idServizio) {
        alert("Compila tutti i campi del rullino.");
        return;
    }
    carrelloRullini.push({numero_scattti: numScatti, risoluzione: risoluzione, id_servizio: idServizio, nome_servizio: nomeServizio, prezzo: prezzo});
    aggiornaCarrelloRullini();
}
function aggiornaCarrelloRullini() {
    const tbody = document.getElementById('carrello-rullini-body');
    tbody.innerHTML = '';
    let totale = 0;
    carrelloRullini.forEach((item, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.numero_scattti}</td>
            <td>${item.risoluzione}</td>
            <td>${item.nome_servizio}</td>
            <td>€ ${item.prezzo.toFixed(2)}</td>
            <td><button type="button" onclick="rimuoviRullino(${index})">Rimuovi</button></td>
        `;
        tbody.appendChild(row);
        totale += item.prezzo;
    });
    document.getElementById('totale').innerText = `Totale: € ${totale.toFixed(2)}`;
    document.getElementById('dati_rullini').value = JSON.stringify(carrelloRullini);
}
function rimuoviRullino(index) {
    carrelloRullini.splice(index, 1);
    aggiornaCarrelloRullini();
}
function beforeSubmitRullini() {
    aggiornaCarrelloRullini();
    if (carrelloRullini.length === 0) {
        alert("Devi aggiungere almeno un rullino all'ordine.");
        return false;
    }
    return true;
}
</script>
</head>
<body>
<div class="container">
<?php if ($ordineConfermato && $idOrdine): ?>
    <div class="msg-success">
        Ordine confermato!<br>
        Il tuo codice ordine è <strong><?= $idOrdine ?></strong>.<br>
        Verrai reindirizzato alla tua area personale tra pochi secondi...
    </div>
<?php else: ?>
    <h2>Ordine Sviluppo Rullino</h2>
    <div style="margin-bottom: 20px;">
        <label for="numero_scattti">Numero scatti:</label>
        <select id="numero_scattti">
            <option value="">-- Seleziona --</option>
            <option value="24">24</option>
            <option value="36">36</option>
        </select>
        <label for="risoluzione">Risoluzione:</label>
        <select id="risoluzione">
            <option value="">-- Seleziona --</option>
            <option value="basic">basic (10mb)</option>
            <option value="fine">fine (20mb)</option>
            <option value="ultra">ultra (40mb)</option>
        </select>
        <label for="id_servizio">Servizio:</label>
        <select id="id_servizio">
            <option value="">-- Seleziona servizio --</option>
            <?php foreach ($servizi as $servizio): ?>
                <option value="<?= $servizio['ID_SERVIZIO'] ?>" data-prezzo="<?= $servizio['PREZZO'] ?>">
                    <?= htmlspecialchars($servizio['NOME_SERVIZIO']) ?> (<?= number_format($servizio['PREZZO'], 2, ',', '.') ?> €)
                </option>
            <?php endforeach; ?>
        </select>
        <button class="btn" type="button" onclick="aggiungiRullino()">Aggiungi rullino</button>
    </div>
    <form method="post" onsubmit="return beforeSubmitRullini();">
        <input type="hidden" name="id_utente" value="<?= $userId ?>" />
        <input type="hidden" name="dati_rullini" id="dati_rullini" value="" />
        <label for="id_laboratorio">Laboratorio:</label>
        <select name="id_laboratorio" required>
            <option value="">-- Seleziona laboratorio --</option>
            <?php foreach ($laboratori as $lab): ?>
                <option value="<?= $lab['ID_LABORATORIO'] ?>">
                    <?= htmlspecialchars($lab['NOME']) ?> (<?= htmlspecialchars($lab['CITTA']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <label for="id_indirizzo">Indirizzo di spedizione:</label>
        <select name="id_indirizzo" required>
            <option value="">-- Seleziona indirizzo --</option>
            <?php foreach ($indirizzi as $indirizzo): ?>
                <option value="<?= $indirizzo['ID_INDIRIZZO'] ?>">
                    <?= htmlspecialchars($indirizzo['VIA']) ?>, <?= htmlspecialchars($indirizzo['CITTA']) ?> (<?= htmlspecialchars($indirizzo['CAP']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <label for="metodo_pagamento">Metodo di pagamento:</label>
        <select name="metodo_pagamento" required>
            <option value="">-- Seleziona metodo --</option>
            <option value="Carta di credito">Carta di credito</option>
            <option value="PayPal">PayPal</option>
            <option value="Bonifico">Bonifico</option>
        </select>
        <label for="valuta">Valuta:</label>
        <select name="valuta" required>
            <option value="">-- Seleziona valuta --</option>
            <option value="EUR">EUR</option>
            <option value="USD">USD</option>
        </select>
        <table>
            <thead>
                <tr><th>Numero scatti</th><th>Risoluzione</th><th>Servizio</th><th>Prezzo</th><th>Rimuovi</th></tr>
            </thead>
            <tbody id="carrello-rullini-body"></tbody>
        </table>
        <div class="total" id="totale">Totale: € 0.00</div>
        <button type="submit" class="btn" style="margin-top: 20px;">Procedi al pagamento</button>
    </form>
<?php endif; ?>
</div>
<script>
window.onload = function() {
    aggiornaCarrelloRullini();
};
</script>
</body>
</html> 
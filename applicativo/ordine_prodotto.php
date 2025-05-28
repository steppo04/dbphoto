<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dbphoto";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connessione fallita: " . $conn->connect_error); }
if (!isset($_GET["id"])) { die("ID utente non specificato."); }
$userId = intval($_GET["id"]);

// Inizializza carrello se non esiste
if (!isset($_SESSION['carrello'])) {
    $_SESSION['carrello'] = [];
}

// Gestione aggiunta prodotto
if (isset($_POST['aggiungi'])) {
    $idProdotto = intval($_POST['id_prodotto']);
    $quantita = intval($_POST['quantita']);
    if ($idProdotto && $quantita > 0) {
        // Recupera info prodotto
        $sql = "SELECT p.ID_PRODOTTO, p.NOME, p.MARCA, p.MODELLO, p.PREZZO, c.NOME AS CATEGORIA FROM PRODOTTO p JOIN CATEGORIA c ON p.ID_CATEGORIA = c.ID_CATEGORIA WHERE p.ID_PRODOTTO = $idProdotto";
        $res = $conn->query($sql);
        if ($row = $res->fetch_assoc()) {
            // Se già presente, aggiorna quantità
            $trovato = false;
            foreach ($_SESSION['carrello'] as &$item) {
                if ($item['id'] == $idProdotto) {
                    $item['quantita'] += $quantita;
                    $trovato = true;
                    break;
                }
            }
            unset($item);
            if (!$trovato) {
                $_SESSION['carrello'][] = [
                    'id' => $row['ID_PRODOTTO'],
                    'nome' => $row['NOME'] . ' - ' . $row['MARCA'] . ' ' . $row['MODELLO'],
                    'prezzo' => $row['PREZZO'],
                    'quantita' => $quantita,
                    'categoria' => $row['CATEGORIA']
                ];
            }
        }
    }
}

// Gestione rimozione prodotto
if (isset($_POST['rimuovi'])) {
    $idx = intval($_POST['idx']);
    if (isset($_SESSION['carrello'][$idx])) {
        array_splice($_SESSION['carrello'], $idx, 1);
    }
}

// Prodotti
$sqlProd = "SELECT p.ID_PRODOTTO, p.NOME, p.MARCA, p.MODELLO, p.PREZZO, c.NOME AS CATEGORIA FROM PRODOTTO p JOIN CATEGORIA c ON p.ID_CATEGORIA = c.ID_CATEGORIA";
$resultProd = $conn->query($sqlProd);
$prodotti = [];
if ($resultProd->num_rows > 0) { while ($row = $resultProd->fetch_assoc()) { $prodotti[] = $row; } }

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
    isset($_POST['dati_ordine']) &&
    isset($_POST['id_indirizzo']) &&
    isset($_POST['metodo_pagamento']) &&
    isset($_POST['valuta'])
) {
    $idUtente = intval($_POST['id_utente']);
    $idIndirizzo = intval($_POST['id_indirizzo']);
    $metodoPagamento = $conn->real_escape_string($_POST['metodo_pagamento']);
    $valuta = $conn->real_escape_string($_POST['valuta']);
    $datiOrdine = json_decode($_POST['dati_ordine'], true);

    if ($datiOrdine && is_array($datiOrdine) && count($datiOrdine) > 0) {
        // Calcolo importo totale
        $importo = 0;
        foreach ($datiOrdine as $item) { $importo += floatval($item['prezzo']) * intval($item['quantita']); }

        // Recupera nuovo ID_ORDINE e ID_PAGAMENTO
        $res = $conn->query("SELECT MAX(ID_ORDINE) AS max_id FROM ORDINE");
        $row = $res->fetch_assoc();
        $idOrdine = ($row["max_id"] !== null) ? $row["max_id"] + 1 : 1;

        $res = $conn->query("SELECT MAX(ID_PAGAMENTO) AS max_id FROM PAGAMENTO");
        $row = $res->fetch_assoc();
        $idPagamento = ($row["max_id"] !== null) ? $row["max_id"] + 1 : 1;

        // Laboratorio fittizio (o scegli il primo)
        $res = $conn->query("SELECT ID_LABORATORIO FROM LABORATORIO LIMIT 1");
        $row = $res->fetch_assoc();
        $idLaboratorio = $row ? $row["ID_LABORATORIO"] : 1;

        $conn->begin_transaction();
        try {
            // 1. Inserisci PAGAMENTO
            $stmt = $conn->prepare("INSERT INTO PAGAMENTO (ID_PAGAMENTO, METODO_DI_PAGAMENTO, VALUTA) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $idPagamento, $metodoPagamento, $valuta);
            $stmt->execute();
            $stmt->close();

            // 2. Inserisci ORDINE con ID_PAGAMENTO valorizzato
            $stmt = $conn->prepare("INSERT INTO ORDINE (ID_ORDINE, ID_PAGAMENTO, DATA_ORDINE, TIPO_ORDINE, ID_LABORATORIO, ID_INDIRIZZO, ID_UTENTE, IMPORTO) VALUES (?, ?, NOW(), 'PRODOTTO', NULL, ?, ?, ?)");
            $stmt->bind_param("iiiid", $idOrdine, $idPagamento, $idIndirizzo, $idUtente, $importo);
            $stmt->execute();
            $stmt->close();

            // Inserisci prodotti in INCLUDE
            $stmt = $conn->prepare("INSERT INTO INCLUDE (ID_PRODOTTO, ID_ORDINE, QUANTITA) VALUES (?, ?, ?)");
            foreach ($datiOrdine as $item) {
                $idProdotto = intval($item['id']);
                $quantita = intval($item['quantita']);
                $stmt->bind_param("iii", $idProdotto, $idOrdine, $quantita);
                $stmt->execute();
            }
            $stmt->close();

            // Stato iniziale ordine
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
        die("Ordine vuoto o dati non validi.");
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8" />
<title>Ordine Prodotto</title>
<style>
    body { font-family: 'Segoe UI', sans-serif; padding: 20px; background: #f9f9f9;}
    .container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);}
    table { width: 100%; border-collapse: collapse; margin-top: 20px;}
    th, td { padding: 10px; border: 1px solid #ddd; text-align: center;}
    th { background: #007bff; color: white; }
    .btn { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; }
    .btn:hover { background: #218838; }
    .total { font-weight: bold; font-size: 1.2em; margin-top: 20px; }
    input[type=number], select { margin-top: 5px; margin-bottom: 15px; }
    .msg-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 20px; border-radius: 8px; margin-bottom: 30px; text-align: center; font-size: 1.2em; }
</style>
<?php if ($ordineConfermato && $idOrdine): ?>
<meta http-equiv="refresh" content="3;url=cliente.php?id=<?= $userId ?>">
<?php endif; ?>
<script>
let carrello = [];
function aggiungiAlCarrello() {
    const selectProdotto = document.getElementById('prodotto');
    const qtyInput = document.getElementById('quantita');
    const idProdotto = selectProdotto.value;
    const nomeProdotto = selectProdotto.options[selectProdotto.selectedIndex].text;
    const prezzo = parseFloat(selectProdotto.selectedOptions[0].getAttribute('data-prezzo'));
    const categoria = selectProdotto.selectedOptions[0].getAttribute('data-categoria');
    const quantita = parseInt(qtyInput.value);
    if (idProdotto === "" || quantita <= 0) {
        alert("Seleziona un prodotto valido e quantità > 0.");
        return;
    }
    const idx = carrello.findIndex(item => item.id == idProdotto);
    if (idx !== -1) {
        carrello[idx].quantita += quantita;
    } else {
        carrello.push({id: idProdotto, nome: nomeProdotto, prezzo: prezzo, quantita: quantita, categoria: categoria});
    }
    aggiornaCarrello();
}
function aggiornaCarrello() {
    const tbody = document.getElementById('carrello-body');
    tbody.innerHTML = '';
    let totale = 0;
    carrello.forEach((item, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.nome}</td>
            <td>${item.categoria}</td>
            <td>${item.quantita}</td>
            <td>€ ${item.prezzo.toFixed(2)}</td>
            <td>€ ${(item.prezzo * item.quantita).toFixed(2)}</td>
            <td><button type="button" onclick="rimuoviDalCarrello(${index})">Rimuovi</button></td>
        `;
        tbody.appendChild(row);
        totale += item.prezzo * item.quantita;
    });
    document.getElementById('totale').innerText = `Totale: € ${totale.toFixed(2)}`;
    document.getElementById('dati_ordine').value = JSON.stringify(carrello);
}
function beforeSubmit() {
    aggiornaCarrello();
    return validaForm();
}
function rimuoviDalCarrello(index) {
    carrello.splice(index, 1);
    aggiornaCarrello();
}
function validaForm() {
    if (carrello.length === 0) {
        alert("Devi aggiungere almeno un prodotto all'ordine.");
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
    <h2>Ordine Prodotti</h2>
    <label for="prodotto">Seleziona prodotto:</label>
    <select id="prodotto">
        <option value="">-- Seleziona --</option>
        <?php foreach ($prodotti as $prodotto): ?>
            <option 
                value="<?= $prodotto['ID_PRODOTTO'] ?>" 
                data-prezzo="<?= $prodotto['PREZZO'] ?>" 
                data-categoria="<?= htmlspecialchars($prodotto['CATEGORIA']) ?>">
                <?= htmlspecialchars($prodotto['NOME'] . " - " . $prodotto['MARCA'] . " " . $prodotto['MODELLO']) ?> (<?= htmlspecialchars($prodotto['CATEGORIA']) ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <label for="quantita">Quantità:</label>
    <input type="number" id="quantita" value="1" min="1" />
    <button class="btn" type="button" onclick="aggiungiAlCarrello()">Aggiungi al carrello</button>
    <form method="post" onsubmit="return beforeSubmit();">
        <input type="hidden" name="id_utente" value="<?= $userId ?>" />
        <input type="hidden" name="dati_ordine" id="dati_ordine" value="" />
        <label for="id_indirizzo">Seleziona indirizzo:</label>
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
                <tr><th>Prodotto</th><th>Categoria</th><th>Quantità</th><th>Prezzo Unit.</th><th>Subtotale</th><th>Rimuovi</th></tr>
            </thead>
            <tbody id="carrello-body"></tbody>
        </table>
        <div class="total" id="totale">Totale: € 0.00</div>
        <button type="submit" class="btn" style="margin-top: 20px;">Procedi al pagamento</button>
    </form>
<?php endif; ?>
</div>
<script>
window.onload = function() {
    aggiornaCarrello();
};
</script>
</body>
</html>
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dbphoto";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connessione fallita: " . $conn->connect_error); }
if (!isset($_GET["id"])) { die("ID utente non specificato."); }
$userId = intval($_GET["id"]);

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
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
        header("Location: cliente.php?id=$userId&indirizzo=ok");
        exit();
    } else {
        $errore = "Errore durante l'inserimento dell'indirizzo.";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Aggiungi indirizzo di spedizione</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f1f1;
            padding: 30px;
        }
        .container {
            background: white;
            max-width: 500px;
            margin: 0 auto;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h2 {
            color: #007bff;
            margin-bottom: 20px;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        input {
            padding: 10px;
            font-size: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .msg-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
    <h2>Aggiungi indirizzo di spedizione</h2>
    <?php if (isset($errore)): ?>
        <div class="msg-error"><?= htmlspecialchars($errore) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="via" placeholder="Via" required />
        <input type="text" name="citta" placeholder="Città" required />
        <input type="text" name="cap" placeholder="CAP" required />
        <input type="text" name="provincia" placeholder="Provincia" required />
        <input type="text" name="regione" placeholder="Regione" required />
        <button type="submit">Aggiungi indirizzo</button>
    </form>
</div>
</body>
</html> 
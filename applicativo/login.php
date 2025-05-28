<?php
// Connessione al database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dbphoto";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Login handling
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $userId = intval($_POST["userId"]);

    $sql = "SELECT RUOLO FROM UTENTE WHERE ID_UTENTE = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($ruolo);

    if ($stmt->fetch()) {
        $stmt->close();
        $conn->close();

        if (strtolower($ruolo) == "admin") {
            header("Location: admin.php?id=" . $userId);
            exit();
        } elseif (strtolower($ruolo) == "user" || strtolower($ruolo) == "cliente") {
            header("Location: cliente.php?id=" . $userId);
            exit();
        } else {
            echo "<script>alert('Ruolo non valido.');</script>";
        }
    } else {
        echo "<script>alert('ID utente non trovato.');</script>";
    }

    $stmt->close();
}

// Registrazione handling
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    // Recupera l'ID_UTENTE successivo
    $result = $conn->query("SELECT MAX(ID_UTENTE) AS max_id FROM UTENTE");
    $row = $result->fetch_assoc();
    $newUserId = ($row["max_id"] !== null) ? $row["max_id"] + 1 : 30;

    // Recupera l'ID_INDIRIZZO successivo
    $result = $conn->query("SELECT MAX(ID_INDIRIZZO) AS max_id FROM INDIRIZZI_DI_SPEDIZIONE");
    $row = $result->fetch_assoc();
    $newAddressId = ($row["max_id"] !== null) ? $row["max_id"] + 1 : 52;

    $nome = $_POST["nome"];
    $cognome = $_POST["cognome"];
    $telefono = $_POST["telefono"];
    $email = $_POST["email"];
    $via = $_POST["via"];
    $citta = $_POST["citta"];
    $cap = $_POST["cap"];
    $provincia = $_POST["provincia"];
    $regione = $_POST["regione"];

    $conn->begin_transaction();
    try {
        $stmt1 = $conn->prepare("INSERT INTO UTENTE (ID_UTENTE, NOME, COGNOME, TELEFONO, EMAIL, ATTIVO, NUM_ORDINI, RUOLO) VALUES (?, ?, ?, ?, ?, 'Y', 0, 'user')");
        $stmt1->bind_param("issss", $newUserId, $nome, $cognome, $telefono, $email);
        $stmt1->execute();

        $stmt2 = $conn->prepare("INSERT INTO INDIRIZZI_DI_SPEDIZIONE (ID_INDIRIZZO, VIA, CITTA, CAP, PROVINCIA, REGIONE, ID_UTENTE) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("isssssi", $newAddressId, $via, $citta, $cap, $provincia, $regione, $newUserId);
        $stmt2->execute();

        $conn->commit();
        echo "<script>alert('Registrazione completata! Il tuo ID utente è $newUserId.');</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Errore durante la registrazione.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Pagina di accesso</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(120deg, #f0f0f0, #e0f7ff);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }
        .auth-header {
            width: 100%;
            text-align: center;
            margin-top: 18px;
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .auth-header img {
            width: 120px;
            max-width: 60vw;
            height: auto;
            object-fit: contain;
            box-shadow: 0 2px 10px rgba(0,0,0,0.10);
            margin-bottom: 10px;
            border-radius: 12px;
        }
        .auth-header h1 {
            color: #007bff;
            font-size: 2em;
            margin: 0 0 4px 0;
            letter-spacing: -1px;
            font-family: 'Segoe UI',sans-serif;
            font-weight: 700;
        }
        .auth-header .slogan {
            color: #444;
            font-size: 1.08em;
            margin-bottom: 4px;
            font-weight: 500;
        }
        .auth-panels {
            display: flex;
            flex-wrap: nowrap;
            justify-content: center;
            gap: 18px;
            width: 100%;
            margin-top: 10px;
            align-items: flex-start;
        }
        .auth-panels form {
            background: #fff;
            padding: 18px 16px 14px 16px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,123,255,0.08);
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
            min-width: 210px;
            max-width: 300px;
            flex: 1 1 210px;
            border: 1px solid #e3f0ff;
        }
        .auth-panels h2 {
            margin-bottom: 4px;
            color: #007bff;
            font-size: 1.15em;
            font-weight: 600;
            letter-spacing: -0.5px;
        }
        .auth-panels label {
            color: #555;
            font-size: 0.98em;
            margin-bottom: 1px;
        }
        .auth-panels input, .auth-panels select, .auth-panels button {
            padding: 8px;
            font-size: 0.98em;
            border: 1px solid #c7e0fa;
            border-radius: 6px;
            width: 100%;
            box-sizing: border-box;
        }
        .auth-panels input:focus, .auth-panels select:focus {
            outline: none;
            border: 1.5px solid #007bff;
            background: #f4faff;
        }
        .auth-panels button {
            background-color: #007bff;
            color: white;
            border: none;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-top: 4px;
            transition: background-color 0.3s;
        }
        .auth-panels button:hover {
            background-color: #0056b3;
        }
        .auth-panels h4 {
            margin: 6px 0 0 0;
            color: #007bff;
            font-size: 1em;
            font-weight: 500;
        }
        .indirizzo-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 10px;
            width: 100%;
        }
        .indirizzo-grid input {
            margin: 0;
        }
        @media (max-width: 700px) {
            .auth-panels {
                flex-direction: column;
                gap: 12px;
                align-items: center;
            }
            .auth-panels form {
                min-width: 160px;
                max-width: 98vw;
            }
            .auth-header img {
                width: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-header">
        <img src="https://svilupporullino.com/cdn/shop/files/avatarsocial_1200x1200.jpg?v=1633790032" alt="Macchina fotografica">
        <h1>svilupporullino.com</h1>
        <div class="slogan">Scopri, gestisci e sviluppa i tuoi ricordi fotografici!</div>
        <div class="auth-panels">
            <form method="post">
                <h2>Login</h2>
                <label for="userId">ID Utente</label>
                <input type="number" name="userId" id="userId" required>
                <button type="submit" name="login">Login</button>
            </form>
            <form method="post">
                <h2>Registrati</h2>
                <input type="text" name="nome" placeholder="Nome" required>
                <input type="text" name="cognome" placeholder="Cognome" required>
                <input type="text" name="telefono" placeholder="Telefono" required>
                <input type="email" name="email" placeholder="Email" required>
                <h4>Indirizzo di spedizione</h4>
                <div class="indirizzo-grid">
                    <input type="text" name="via" placeholder="Via" required>
                    <input type="text" name="citta" placeholder="Città" required>
                    <input type="text" name="cap" placeholder="CAP" required>
                    <input type="text" name="provincia" placeholder="Provincia" required>
                    <input type="text" name="regione" placeholder="Regione" required style="grid-column: span 2;">
                </div>
                <button type="submit" name="register">Registrati</button>
            </form>
        </div>
    </div>
</body>
</html>

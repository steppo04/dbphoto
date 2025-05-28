<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dbphoto";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connessione fallita: " . $conn->connect_error); }

// Gestione aggiunta prodotto
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['azione']) && $_POST['azione'] === 'aggiungi' &&
    isset($_POST['nome']) && isset($_POST['marca']) && isset($_POST['modello']) && isset($_POST['prezzo']) && isset($_POST['descrizione'])
) {
    $nome = $conn->real_escape_string($_POST['nome']);
    $marca = $conn->real_escape_string($_POST['marca']);
    $modello = $conn->real_escape_string($_POST['modello']);
    $prezzo = $conn->real_escape_string($_POST['prezzo']);
    $descrizione = $conn->real_escape_string($_POST['descrizione']);
    // Recupera nuovo ID_PRODOTTO
    $res = $conn->query("SELECT MAX(ID_PRODOTTO) AS max_id FROM PRODOTTO");
    $row = $res->fetch_assoc();
    $newId = ($row["max_id"] !== null) ? $row["max_id"] + 1 : 1;
    $conn->query("INSERT INTO PRODOTTO (ID_PRODOTTO, NOME, MARCA, MODELLO, PREZZO, DESCRIZIONE, ID_CATEGORIA) VALUES ($newId, '$nome', '$marca', '$modello', '$prezzo', '$descrizione', 1)");
    $adminId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    header("Location: admin_prodotti.php?id=$adminId");
    exit();
}
// Gestione modifica prodotto
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['azione']) && $_POST['azione'] === 'modifica' &&
    isset($_POST['id']) && isset($_POST['nome']) && isset($_POST['marca']) && isset($_POST['modello']) && isset($_POST['prezzo']) && isset($_POST['descrizione'])
) {
    $id = intval($_POST['id']);
    $nome = $conn->real_escape_string($_POST['nome']);
    $marca = $conn->real_escape_string($_POST['marca']);
    $modello = $conn->real_escape_string($_POST['modello']);
    $prezzo = $conn->real_escape_string($_POST['prezzo']);
    $descrizione = $conn->real_escape_string($_POST['descrizione']);
    $conn->query("UPDATE PRODOTTO SET NOME='$nome', MARCA='$marca', MODELLO='$modello', PREZZO='$prezzo', DESCRIZIONE='$descrizione' WHERE ID_PRODOTTO=$id");
    $adminId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    header("Location: admin_prodotti.php?id=$adminId");
    exit();
}
// Gestione elimina prodotto
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['azione']) && $_POST['azione'] === 'elimina' && isset($_POST['id'])
) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM PRODOTTO WHERE ID_PRODOTTO=$id");
    $adminId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    header("Location: admin_prodotti.php?id=$adminId");
    exit();
}
// Recupera prodotti
$prodotti = [];
$res = $conn->query("SELECT * FROM PRODOTTO");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) { $prodotti[] = $row; }
}
$conn->close();

// Recupero l'ID_UTENTE admin dalla GET (o sessione se vuoi)
$adminId = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Prodotti</title>
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
        .prod-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-bottom: 30px;
        }
        .prod-table th, .prod-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .prod-table thead {
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
        .btn-add { background: #28a745; color: white; }
        .btn-edit { background: #ffc107; color: #333; }
        .btn-del { background: #dc3545; color: white; }
        .form-inline { display: flex; gap: 8px; align-items: center; margin-bottom: 18px; }
        .form-inline input { padding: 6px; border-radius: 4px; border: 1px solid #ccc; }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <a href="admin.php?id=<?= $adminId ?>">Home</a>
        <a href="admin_prodotti.php?id=<?= $adminId ?>" class="active">Prodotti</a>
        <a href="admin_ordini.php?id=<?= $adminId ?>">Ordini</a>
        <a href="admin_utenti.php?id=<?= $adminId ?>">Utenti</a>
        <a href="admin_recensioni.php?id=<?= $adminId ?>">Recensioni</a>
        <a href="admin_statistiche.php?id=<?= $adminId ?>">Statistiche</a>
    </div>
    <div class="main">
        <h1>Gestione Prodotti</h1>
        <form class="form-inline" method="post">
            <input type="hidden" name="azione" value="aggiungi">
            <input type="text" name="nome" placeholder="Nome" required>
            <input type="text" name="marca" placeholder="Marca" required>
            <input type="text" name="modello" placeholder="Modello" required>
            <input type="text" name="prezzo" placeholder="Prezzo" required>
            <input type="text" name="descrizione" placeholder="Descrizione" required>
            <button class="btn btn-add" type="submit">Aggiungi prodotto</button>
        </form>
        <table class="prod-table">
            <thead>
                <tr><th>ID</th><th>Nome</th><th>Marca</th><th>Modello</th><th>Prezzo</th><th>Descrizione</th><th>Azioni</th></tr>
            </thead>
            <tbody>
                <?php foreach ($prodotti as $prodotto): ?>
                <tr>
                    <form method="post" class="form-inline">
                        <input type="hidden" name="azione" value="modifica">
                        <input type="hidden" name="id" value="<?= $prodotto['ID_PRODOTTO'] ?>">
                        <td><?= $prodotto['ID_PRODOTTO'] ?></td>
                        <td><input type="text" name="nome" value="<?= htmlspecialchars($prodotto['NOME']) ?>" required></td>
                        <td><input type="text" name="marca" value="<?= htmlspecialchars($prodotto['MARCA']) ?>" required></td>
                        <td><input type="text" name="modello" value="<?= htmlspecialchars($prodotto['MODELLO']) ?>" required></td>
                        <td><input type="text" name="prezzo" value="<?= htmlspecialchars($prodotto['PREZZO']) ?>" required></td>
                        <td><input type="text" name="descrizione" value="<?= htmlspecialchars($prodotto['DESCRIZIONE']) ?>" required></td>
                        <td>
                            <button class="btn btn-edit" type="submit">Salva</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="azione" value="elimina">
                        <input type="hidden" name="id" value="<?= $prodotto['ID_PRODOTTO'] ?>">
                        <button class="btn btn-del" type="submit" onclick="return confirm('Sei sicuro di voler eliminare questo prodotto?');">Elimina</button>
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
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dbphoto";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connessione fallita: " . $conn->connect_error); }

// Gestione azioni
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['azione']) && isset($_POST['id_utente'])
) {
    $idUtente = intval($_POST['id_utente']);
    if ($_POST['azione'] === 'disabilita') {
        $conn->query("UPDATE UTENTE SET ATTIVO = 'N' WHERE ID_UTENTE = $idUtente");
    } elseif ($_POST['azione'] === 'riattiva') {
        $conn->query("UPDATE UTENTE SET ATTIVO = 'Y' WHERE ID_UTENTE = $idUtente");
    } elseif ($_POST['azione'] === 'reset_password') {
        $newPass = substr(md5(uniqid()), 0, 8);
        $conn->query("UPDATE UTENTE SET PASSWORD = '" . $conn->real_escape_string($newPass) . "' WHERE ID_UTENTE = $idUtente");
        $msg = "Nuova password per utente $idUtente: $newPass";
    } elseif ($_POST['azione'] === 'genera_codice') {
        $newCode = substr(md5(uniqid()), 0, 10);
        $conn->query("UPDATE UTENTE SET CODICE_ACCESSO = '" . $conn->real_escape_string($newCode) . "' WHERE ID_UTENTE = $idUtente");
        $msg = "Nuovo codice accesso per utente $idUtente: $newCode";
    }
    $adminId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    header("Location: admin_utenti.php?id=$adminId");
    exit();
}

// Filtri
$where = [];
$params = [];
$types = "";
if (!empty($_GET['nome'])) {
    $where[] = "NOME LIKE ?";
    $params[] = "%" . $_GET['nome'] . "%";
    $types .= "s";
}
if (!empty($_GET['cognome'])) {
    $where[] = "COGNOME LIKE ?";
    $params[] = "%" . $_GET['cognome'] . "%";
    $types .= "s";
}
if (!empty($_GET['email'])) {
    $where[] = "EMAIL LIKE ?";
    $params[] = "%" . $_GET['email'] . "%";
    $types .= "s";
}
$whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

$sql = "SELECT ID_UTENTE, NOME, COGNOME, EMAIL, TELEFONO, ATTIVO FROM UTENTE $whereSql ORDER BY COGNOME, NOME";
$stmt = $conn->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$res = $stmt->get_result();
$utenti = [];
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) { $utenti[] = $row; }
}
$stmt->close();
$conn->close();

$adminId = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Utenti</title>
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
        .filter-row input { padding: 7px; border-radius: 5px; border: 1px solid #ccc; }
        .user-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-bottom: 30px;
        }
        .user-table th, .user-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .user-table thead {
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
        .btn-dis { background: #dc3545; color: white; }
        .btn-ok { background: #28a745; color: white; }
        .btn-reset { background: #ffc107; color: #333; }
        .btn-code { background: #007bff; color: white; }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <a href="admin.php?id=<?= $adminId ?>">Home</a>
        <a href="admin_prodotti.php?id=<?= $adminId ?>">Prodotti</a>
        <a href="admin_ordini.php?id=<?= $adminId ?>">Ordini</a>
        <a href="admin_utenti.php?id=<?= $adminId ?>" class="active">Utenti</a>
        <a href="admin_recensioni.php?id=<?= $adminId ?>">Recensioni</a>
        <a href="admin_statistiche.php?id=<?= $adminId ?>">Statistiche</a>
    </div>
    <div class="main">
        <h1>Gestione Utenti</h1>
        <form class="filter-row" method="get">
            <input type="text" name="nome" placeholder="Nome" value="<?= isset($_GET['nome']) ? htmlspecialchars($_GET['nome']) : '' ?>">
            <input type="text" name="cognome" placeholder="Cognome" value="<?= isset($_GET['cognome']) ? htmlspecialchars($_GET['cognome']) : '' ?>">
            <input type="text" name="email" placeholder="Email" value="<?= isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '' ?>">
            <button class="btn btn-ok" type="submit">Filtra</button>
        </form>
        <table class="user-table">
            <thead>
                <tr><th>ID</th><th>Nome</th><th>Cognome</th><th>Email</th><th>Telefono</th><th>Attivo</th><th>Azioni</th></tr>
            </thead>
            <tbody>
                <?php foreach ($utenti as $utente): ?>
                <tr>
                    <td><?= $utente['ID_UTENTE'] ?></td>
                    <td><?= htmlspecialchars($utente['NOME']) ?></td>
                    <td><?= htmlspecialchars($utente['COGNOME']) ?></td>
                    <td><?= htmlspecialchars($utente['EMAIL']) ?></td>
                    <td><?= htmlspecialchars($utente['TELEFONO']) ?></td>
                    <td><?= $utente['ATTIVO'] === 'Y' ? 'Sì' : 'No' ?></td>
                    <td>
                        <?php if ($utente['ATTIVO'] === 'Y'): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="azione" value="disabilita">
                            <input type="hidden" name="id_utente" value="<?= $utente['ID_UTENTE'] ?>">
                            <button class="btn btn-dis" type="submit">Disabilita</button>
                        </form>
                        <?php else: ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="azione" value="riattiva">
                            <input type="hidden" name="id_utente" value="<?= $utente['ID_UTENTE'] ?>">
                            <button class="btn btn-ok" type="submit">Riattiva</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html> 
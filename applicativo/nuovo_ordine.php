<?php
if (!isset($_GET["id"])) {
    die("ID utente non specificato.");
}
$userId = intval($_GET["id"]);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Nuovo Ordine</title>
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
            text-align: center;
        }
        h2 {
            color: #007bff;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 15px 25px;
            margin: 15px;
            background-color: #007bff;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Scegli il tipo di ordine</h2>
    <a href="ordine_prodotto.php?id=<?= $userId ?>" class="btn">Acquisto Prodotto</a>
    <a href="ordine_rullino.php?id=<?= $userId ?>" class="btn">Sviluppo Rullino</a>
</div>
</body>
</html>

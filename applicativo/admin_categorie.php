<?php
// Qui puoi aggiungere un controllo di login admin se necessario
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Categorie</title>
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
        .cat-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-bottom: 30px;
        }
        .cat-table th, .cat-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .cat-table thead {
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
    </style>
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <a href="admin_prodotti.php">Prodotti</a>
        <a href="admin_categorie.php" class="active">Categorie</a>
        <a href="admin_servizi.php">Servizi sviluppo</a>
        <a href="admin_ordini.php">Ordini</a>
        <a href="admin_utenti.php">Utenti</a>
        <a href="admin_recensioni.php">Recensioni</a>
        <a href="admin_statistiche.php">Statistiche</a>
    </div>
    <div class="main">
        <h1>Gestione Categorie</h1>
        <button class="btn btn-add">Aggiungi categoria</button>
        <table class="cat-table">
            <thead>
                <tr><th>ID</th><th>Nome</th><th>Azioni</th></tr>
            </thead>
            <tbody>
                <!-- Qui andranno le categorie dal database -->
                <tr><td>1</td><td>Categoria demo</td><td><button class="btn btn-edit">Modifica</button><button class="btn btn-del">Elimina</button></td></tr>
            </tbody>
        </table>
    </div>
</div>
</body>
</html> 
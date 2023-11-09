<?php
    if (isset($_POST['func']) && $_POST['func'] == '1') {
        define('DB_HOST', 'localhost');
        define('DB_USER', 'root');
        define('DB_PASSWORD', '');
        define('DB_NAME', 'test_db');
        try {
            $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $query = 'SELECT * FROM `users` ORDER BY rand() LIMIT 1';
            $result = $pdo->query($query);
            $row = $result->fetch(PDO::FETCH_ASSOC);
            echo json_encode($row);
        } catch(PDOException $e) {
            echo 'Ошибка: '.$e->getMessage();
        }
    }

<?php
session_start();

function db() {
    static $pdo = null;
    if (!$pdo) {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=papoyisa_db;charset=utf8mb4',
            'root',   // usuario de XAMPP
            '',       // contraseña de XAMPP (vacía por defecto)
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
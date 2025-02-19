<?php
// filepath: /c:/xampp/htdocs/InstantMessage/public/api/auth.php
session_start();
header('Content-Type: application/json');

require_once '../../app/controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($action === 'login') {
        $result = AuthController::login($email, $password);
    } elseif ($action === 'register') {
        $result = AuthController::register($email, $password);
    } else {
        $result = ['success' => false, 'message' => 'Action non reconnue'];
    }

    echo json_encode($result);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Méthode non permise']);
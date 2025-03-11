<?php
header('Content-Type: application/json');

require_once '../../app/controllers/UserController.php';

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'getCurrentUser') {
        $user = UserController::getCurrentUser();
        echo json_encode(['user' => $user]);
        exit;
    }
    elseif ($_GET['action'] === 'searchUsers') {
        $query = $_GET['query'] ?? '';
        $users = UserController::searchUsers($query);
        echo json_encode(['users' => $users]);
        exit;
    }
    elseif ($_GET['action'] === 'updateUsername' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        session_start();
        $newUsername = $_POST['username'] ?? '';
        if (empty($newUsername)) {
            echo json_encode(['success' => false, 'message' => 'Le username ne peut pas être vide']);
            exit;
        }
        $result = UserController::updateUsername($_SESSION['user']['id'], $newUsername);
        echo json_encode($result);
        exit;
    }
}

echo json_encode(['error' => 'Action non spécifiée']);
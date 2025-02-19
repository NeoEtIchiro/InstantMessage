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
}

echo json_encode(['error' => 'Action non spécifiée']);
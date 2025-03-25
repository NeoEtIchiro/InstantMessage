<?php
// Définir le header pour renvoyer du JSON
header('Content-Type: application/json');

// Inclusion du contrôleur utilisateur
require_once '../../app/controllers/UserController.php';

// Vérifier si une action est définie via les paramètres GET
if (isset($_GET['action'])) {
    // Récupérer l'action demandée
    if ($_GET['action'] === 'getCurrentUser') {
        // Récupérer l'utilisateur courant
        $user = UserController::getCurrentUser();
        echo json_encode(['user' => $user]);
        exit;
    } elseif ($_GET['action'] === 'searchUsers') {
        // Recherche d'utilisateurs avec la requête passée en paramètre
        $query = $_GET['query'] ?? '';
        $users = UserController::searchUsers($query);
        echo json_encode(['users' => $users]);
        exit;
    } elseif ($_GET['action'] === 'updateUsername' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Démarrer la session pour accéder aux variables de session
        session_start();
        // Récupérer le nouveau nom d'utilisateur depuis les données POST
        $newUsername = $_POST['username'] ?? '';
        // Vérifier que le nouveau nom d'utilisateur n'est pas vide
        if (empty($newUsername)) {
            echo json_encode(['success' => false, 'message' => 'Le username ne peut pas être vide']);
            exit;
        }
        // Mettre à jour le nom d'utilisateur pour l'utilisateur courant
        $result = UserController::updateUsername($_SESSION['user']['id'], $newUsername);
        echo json_encode($result);
        exit;
    }
}

// Aucune action spécifiée, renvoyer une erreur en JSON
echo json_encode(['error' => 'Action non spécifiée']);
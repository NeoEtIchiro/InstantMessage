<?php
// fichier : /c:/xampp/htdocs/InstantMessage/public/api/auth.php

// Démarrage de la session
session_start();

// Définition du type de contenu de la réponse HTTP en JSON
header('Content-Type: application/json');

// Inclusion du contrôleur d'authentification
require_once '../../app/controllers/AuthController.php';

// Vérification que la méthode de la requête est POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des données POST
    $action = $_POST['action'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Vérification de l'action demandée
    if ($action === 'login') {
        // Appel de la méthode de connexion
        $result = AuthController::login($email, $password);
    } elseif ($action === 'register') {
        // Appel de la méthode d'inscription
        $result = AuthController::register($email, $password);
    } else {
        // Action non reconnue
        $result = ['success' => false, 'message' => 'Action non reconnue'];
    }

    // Envoi de la réponse au format JSON
    echo json_encode($result);
    exit;
}

// Si la méthode n'est pas POST, renvoi d'une erreur en JSON
echo json_encode(['success' => false, 'message' => 'Méthode non permise']);
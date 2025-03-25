<?php
// Définition du type de contenu de la réponse HTTP en JSON
header('Content-Type: application/json');

// Inclusion du contrôleur de conversation
require_once '../../app/controllers/ConversationController.php';

// Vérification que l'utilisateur est connecté
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    echo json_encode(['error' => 'Utilisateur non connecté']);
    exit;
}

// Récupération de l'identifiant de l'utilisateur courant
$currentUserId = $_SESSION['user']['id'];

// Vérification de l'existence du paramètre d'action dans l'URL
if (isset($_GET['action'])) {
    // Si l'action est de récupérer les conversations
    if ($_GET['action'] === 'getConversations') {
        $conversations = ConversationController::getConversationsForUser($currentUserId);
        echo json_encode(['conversations' => $conversations]);
        exit;
    }
    // Si l'action est de récupérer ou créer une conversation directe
    elseif ($_GET['action'] === 'getOrCreateConversation') {
        // Vérification que le paramètre userId est présent
        if (!isset($_GET['userId'])) {
            echo json_encode(['error' => 'Paramètre manquant']);
            exit;
        }
        // Conversion de l'identifiant de l'autre utilisateur en entier
        $otherUserId = intval($_GET['userId']);
        $conversationId = ConversationController::getOrCreateDirectConversation($currentUserId, $otherUserId);
        echo json_encode(['conversationId' => $conversationId]);
        exit;
    }
}

// Message d'erreur si aucune action n'a été spécifiée
echo json_encode(['error' => 'Action non spécifiée']);
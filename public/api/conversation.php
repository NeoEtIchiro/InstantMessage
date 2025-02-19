<?php
header('Content-Type: application/json');

require_once '../../app/controllers/ConversationController.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    echo json_encode(['error' => 'Utilisateur non connecté']);
    exit;
}
$currentUserId = $_SESSION['user']['id'];

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'getConversations') {
        $conversations = ConversationController::getConversationsForUser($currentUserId);
        echo json_encode(['conversations' => $conversations]);
        exit;
    }
    elseif ($_GET['action'] === 'getOrCreateConversation') {
        if (!isset($_GET['userId'])) {
            echo json_encode(['error' => 'Paramètre manquant']);
            exit;
        }
        $otherUserId = intval($_GET['userId']);
        $conversationId = ConversationController::getOrCreateDirectConversation($currentUserId, $otherUserId);
        echo json_encode(['conversationId' => $conversationId]);
        exit;
    }
}

echo json_encode(['error' => 'Action non spécifiée']);
<?php
header('Content-Type: text/html');

require_once '../../app/controllers/MessageController.php';
// Si besoin, vous pouvez inclure également UserController pour obtenir le login de l'expéditeur.

if (isset($_GET['action']) && $_GET['action'] === 'getMessages') {
    if (!isset($_GET['id'])) {
        echo "<p>Aucune conversation spécifiée.</p>";
        exit;
    }
    $conversationId = intval($_GET['id']);
    $messages = MessageController::getMessages($conversationId);
    if (!$messages || count($messages) === 0) {
        echo "<p>Aucun message dans cette conversation.</p>";
        exit;
    }
    // Affichage des messages (ici, on affiche l'ID de l'expéditeur en guise de placeholder)
    foreach ($messages as $msg) {
        echo "<div class='mb-2'>";
        echo "<strong>Utilisateur " . htmlspecialchars($msg->sender_id) . " :</strong> ";
        echo "<span>" . htmlspecialchars($msg->content) . "</span>";
        echo "</div>";
    }
    exit;
}

echo "<p>Action non spécifiée.</p>";
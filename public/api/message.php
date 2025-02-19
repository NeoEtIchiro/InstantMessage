<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'sendMessage') {
    header('Content-Type: application/json');
    require_once '../../app/controllers/MessageController.php';
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié']);
        exit;
    }
    $currentUserId = $_SESSION['user']['id'];
    $conversationId = $_POST['conversationId'] ?? null;
    $content = trim($_POST['content'] ?? '');
    if (!$conversationId || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Conversation ou contenu manquant']);
        exit;
    }
    $message = MessageController::sendMessage($conversationId, $currentUserId, $content);
    if ($message) {
        echo json_encode(['success' => true, 'message' => 'Message envoyé']);
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi du message']);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'getMessages') {
    header('Content-Type: text/html');
    require_once '../../app/controllers/MessageController.php';
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
    $currentUserId = $_SESSION['user']['id'] ?? null;
    foreach ($messages as $msg) {
        if ($msg->sender_id == $currentUserId) {
            // Message envoyé par moi -> alignement à droite
            echo "<div class='text-right mb-2'>";
            echo "<span class='bg-blue-500 text-white text-left p-2 rounded inline-block max-w-[700px]'>" . htmlspecialchars($msg->content) . "</span>";
            echo "</div>";
        } else {
            // Message des autres -> alignement à gauche
            echo "<div class='text-left mb-2 flex gap-2 items-center'>";
            echo "<div class='w-8 h-8 bg-gray-300 rounded-full'></div>";
            echo "<div class='flex flex-col'>";
            echo "<span class='text-sm'>" . htmlspecialchars($msg->login) . "</span>";
            echo "<span class='bg-gray-200 p-2 rounded inline-block max-w-[700px]'>" . htmlspecialchars($msg->content) . "</span>";
            echo "</div>";
            echo "</div>";
        }
    }
    exit;
}
echo "<p>Action non spécifiée.</p>";
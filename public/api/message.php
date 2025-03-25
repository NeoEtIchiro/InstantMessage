<?php
// Démarrage de la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Traitement de la requête d'envoi de message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'sendMessage') {
    header('Content-Type: application/json');

    // Inclusion du contrôleur des messages
    require_once '../../app/controllers/MessageController.php';

    // Vérification de l'authentification de l'utilisateur
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié']);
        exit;
    }

    $currentUserId = $_SESSION['user']['id'];
    $conversationId = $_POST['conversationId'] ?? null;
    $content = trim($_POST['content'] ?? '');

    // Vérification de la présence de l'ID de la conversation et du contenu du message
    if (!$conversationId || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Conversation ou contenu manquant']);
        exit;
    }

    // Envoi du message via le contrôleur
    $message = MessageController::sendMessage($conversationId, $currentUserId, $content);

    // Retourne une réponse JSON selon le résultat
    if ($message) {
        echo json_encode(['success' => true, 'message' => 'Message envoyé']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi du message']);
    exit;
}

// Traitement de la récupération des messages
if (isset($_GET['action']) && $_GET['action'] === 'getMessages') {
    header('Content-Type: text/html');

    // Inclusion du contrôleur des messages
    require_once '../../app/controllers/MessageController.php';

    // Vérification de la spécification de la conversation
    if (!isset($_GET['id'])) {
        echo "<p>Aucune conversation spécifiée.</p>";
        exit;
    }

    $conversationId = intval($_GET['id']);
    $messages = MessageController::getMessages($conversationId);

    // Vérification si des messages existent pour la conversation
    if (!$messages || count($messages) === 0) {
        echo "<p>Aucun message dans cette conversation.</p>";
        exit;
    }

    $currentUserId = $_SESSION['user']['id'] ?? null;

    // Boucle sur chaque message pour afficher son contenu
    foreach ($messages as $msg) {
        if ($msg->sender_id == $currentUserId) {
            // Message envoyé par l'utilisateur actuel -> alignement à droite
            echo "<div class='w-full flex justify-end mb-2 gap-2 items-center'>";
                echo "<div class='flex flex-col w-fit text-right justify-end'>";
                    echo "<span class='bg-blue-500 text-white p-2 break-words w-fit rounded inline-block max-w-[700px]'>" . htmlspecialchars($msg->content) . "</span>";
                    echo "<span class='text-sm text-gray-400'>" . htmlspecialchars($msg->time_stamp) . "</span>";
                echo "</div>";
            echo "</div>";
        } else {
            // Message envoyé par un autre utilisateur -> alignement à gauche
            echo "<div class='text-left mb-2 flex gap-2 items-center'>";
                echo "<div class='w-8 h-8 bg-gray-300 rounded-full'></div>";
                echo "<div class='flex flex-col'>";
                    echo "<span class='text-sm'>" . htmlspecialchars($msg->username) . "</span>";
                    echo "<span class='bg-gray-200 p-2 w-fit rounded inline-block max-w-[700px]'>" . htmlspecialchars($msg->content) . "</span>";
                    echo "<span class='text-sm text-gray-400'>" . htmlspecialchars($msg->time_stamp) . "</span>";
                echo "</div>";
            echo "</div>";
        }
    }
    exit;
}

// Message par défaut si aucune action n'est spécifiée
echo "<p>Action non spécifiée.</p>";
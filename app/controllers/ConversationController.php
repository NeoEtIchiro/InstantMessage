<?php
// Démarrer la session s'il n'existe pas déjà
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion des modèles et de la connexion à la base de données
require_once __DIR__ . '/../models/Conversation.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../database/ConnexionDB.php';

class ConversationController {
    // Récupère la conversation globale (chat avec tout le monde)
    public static function getGlobalConversation() {
        // Création d'une connexion à la base de données
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        
        // Préparation de la requête pour trouver une conversation de type 'global'
        $stmt = $conn->prepare("SELECT * FROM conversations WHERE type = 'global' LIMIT 1");
        $stmt->execute();
        
        // Récupération de la conversation globale
        $global = $stmt->fetch(PDO::FETCH_OBJ);
        
        return $global;
    }

    public static function getGlobalConversations() {
        // Création d'une connexion à la base de données
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        
        // Préparation de la requête pour trouver toutes les conversations de type 'global'
        $stmt = $conn->prepare("SELECT * FROM conversations WHERE type = 'global'");
        $stmt->execute();
        
        // Récupération de toutes les conversations globales
        $globals = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        return $globals;
    }

    // Retourne la liste des conversations de l'utilisateur avec le login de l'autre participant pour une conversation directe
    public static function getConversationsForUser($userId) {
        // Création d'une connexion à la base de données
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        
        // Récupération des conversations de l'utilisateur
        $conversations = Conversation::getConversationsForUser($conn, $userId);
        $result = [];
    
        // Récupération de toutes les conversations globales
        $globals = self::getGlobalConversations();
        foreach ($globals as $global) {
            $result[] = [
                'conversation_id' => $global->id,
                'type'            => 'global',
                'other_login'     => 'Chat with everyone',
                'other_username'  => $global->name
            ];
        }
    
        // Parcours des conversations directes ou de groupe
        foreach ($conversations as $conversation) {
            // Préparation de la requête pour récupérer l'autre participant
            $stmt = $conn->prepare("SELECT u.id, u.login 
                FROM conversation_users cu
                JOIN users u ON cu.user_id = u.id 
                WHERE cu.conversation_id = :convId AND u.id != :userId");
            $stmt->bindParam(':convId', $conversation->id, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Extraction des informations de l'autre participant
            $other = $stmt->fetch(PDO::FETCH_ASSOC);
            $other_username = $other ? User::getUsernameByLogin($conn, $other['login']) : null;
    
            // Ajout de la conversation au résultat avec les informations appropriées
            $result[] = [
                'conversation_id' => $conversation->id,
                'type' => $conversation->type,
                'other_login' => $other ? $other['login'] : 'Groupe',
                'other_username' => $other_username
            ];
        }
        return $result;
    }

    // Récupère ou crée une conversation directe entre l'utilisateur connecté et un autre utilisateur
    public static function getOrCreateDirectConversation($currentUserId, $otherUserId) {
        // Création d'une connexion à la base de données
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        
        // Préparation de la requête pour vérifier si la conversation directe existe déjà
        $query = "SELECT c.id FROM conversations c 
                  JOIN conversation_users cu1 ON c.id = cu1.conversation_id 
                  JOIN conversation_users cu2 ON c.id = cu2.conversation_id 
                  WHERE c.type = 'direct' AND cu1.user_id = :user1 AND cu2.user_id = :user2";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user1', $currentUserId, PDO::PARAM_INT);
        $stmt->bindParam(':user2', $otherUserId, PDO::PARAM_INT);
        $stmt->execute();
        
        // Si la conversation existe déjà, renvoyer son identifiant
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['id'];
        }
        
        // Sinon, créer une nouvelle conversation directe
        $conversation = Conversation::createDirectConversation($conn, $currentUserId, $otherUserId);
        return $conversation->id;
    }

    // Récupère ou crée une conversation de groupe pour l'utilisateur
    public static function getOrCreateGroupConversation($currentUserId){
        // Création d'une connexion à la base de données
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();

        // Préparation de la requête pour vérifier si une conversation de groupe existe déjà pour l'utilisateur
        $query = "SELECT c.id FROM conversations c 
                  JOIN conversation_users cu1 ON c.id = cu1.conversation_id 
                  WHERE c.type = 'group' AND cu1.user_id = :user1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user1', $currentUserId, PDO::PARAM_INT);
        $stmt->execute();
        
        // Si la conversation existe déjà, renvoyer son identifiant
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['id'];
        }
        
        // Sinon, créer une nouvelle conversation de groupe
        $conversation = Conversation::createGroupConversation($conn, $currentUserId, "Nouveau groupe");
        return $conversation->id;
    }
}
?>
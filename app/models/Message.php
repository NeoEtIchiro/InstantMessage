<?php
// Définition de la classe Message
class Message {
    public $id;
    public $conversation_id;
    public $sender_id;
    public $content;
    public $time_stamp;
    public $login;
    public $username; // Propriété pour le nom d'utilisateur

    // Constructeur de la classe Message
    public function __construct($id = null, $conversation_id = null, $sender_id = null, $content = '', $time_stamp = null, $login = null, $username = null) {
        // Initialisation des attributs de l'objet
        $this->id = $id;
        $this->conversation_id = $conversation_id;
        $this->sender_id = $sender_id;
        $this->content = $content;
        $this->time_stamp = $time_stamp;
        $this->login = $login;
        $this->username = $username;
    }

    // Méthode statique pour récupérer tous les messages d'une conversation
    // en effectuant une jointure avec la table des utilisateurs
    public static function getMessagesByConversation(PDO $conn, $conversationId) {
        // Requête SQL pour récupérer les messages et les informations associées de l'utilisateur
        $query = "SELECT m.*, u.login AS login, u.username AS username
                  FROM messages m 
                  JOIN users u ON m.sender_id = u.id 
                  WHERE m.conversation_id = :convId 
                  ORDER BY m.time_stamp ASC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':convId', $conversationId, PDO::PARAM_INT);
        $stmt->execute();
        
        // Tableau pour stocker les objets Message
        $messages = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Création d'un objet Message pour chaque ligne récupérée
            $messages[] = new Message(
                $row['id'], 
                $row['conversation_id'], 
                $row['sender_id'], 
                $row['content'], 
                $row['time_stamp'], 
                $row['login'],         // Récupération du login
                $row['username']       // Récupération du nom d'utilisateur
            );
        }
        return $messages; // Retourne la liste des messages
    }

    // Méthode pour enregistrer le message dans la base de données
    public function save(PDO $conn) {
        // Requête SQL pour insérer un nouveau message
        $query = "INSERT INTO messages (conversation_id, sender_id, content) VALUES (:convId, :senderId, :content)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':convId', $this->conversation_id, PDO::PARAM_INT);
        $stmt->bindParam(':senderId', $this->sender_id, PDO::PARAM_INT);
        $stmt->bindParam(':content', $this->content, PDO::PARAM_STR);
        
        // Exécution de la requête et gestion du résultat
        if ($stmt->execute()) {
            $this->id = $conn->lastInsertId(); // Mise à jour de l'ID avec le dernier identifiant inséré
            return true; // Renvoie true si la sauvegarde a réussi
        }
        return false; // Renvoie false en cas d'échec
    }
}
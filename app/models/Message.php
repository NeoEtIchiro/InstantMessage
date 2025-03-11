<?php
class Message {
    public $id;
    public $conversation_id;
    public $sender_id;
    public $content;
    public $time_stamp;
    public $login;
    public $username; // Ajout de la propriété username

    // Ajout du paramètre $username dans le constructeur
    public function __construct($id = null, $conversation_id = null, $sender_id = null, $content = '', $time_stamp = null, $login = null, $username = null) {
        $this->id = $id;
        $this->conversation_id = $conversation_id;
        $this->sender_id = $sender_id;
        $this->content = $content;
        $this->time_stamp = $time_stamp;
        $this->login = $login;
        $this->username = $username;
    }

    // Récupérer tous les messages d'une conversation en effectuant une jointure avec la table users
    public static function getMessagesByConversation(PDO $conn, $conversationId) {
        $query = "SELECT m.*, u.login AS login, u.username AS username
                  FROM messages m 
                  JOIN users u ON m.sender_id = u.id 
                  WHERE m.conversation_id = :convId 
                  ORDER BY m.time_stamp ASC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':convId', $conversationId, PDO::PARAM_INT);
        $stmt->execute();
        $messages = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $messages[] = new Message(
                $row['id'], 
                $row['conversation_id'], 
                $row['sender_id'], 
                $row['content'], 
                $row['time_stamp'], 
                $row['login'],         // login
                $row['username']       // username récupéré
            );
        }
        return $messages;
    }

    // Enregistre le message dans la base de données
    public function save(PDO $conn) {
        $query = "INSERT INTO messages (conversation_id, sender_id, content) VALUES (:convId, :senderId, :content)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':convId', $this->conversation_id, PDO::PARAM_INT);
        $stmt->bindParam(':senderId', $this->sender_id, PDO::PARAM_INT);
        $stmt->bindParam(':content', $this->content, PDO::PARAM_STR);
        if ($stmt->execute()) {
            $this->id = $conn->lastInsertId();
            return true;
        }
        return false;
    }
}
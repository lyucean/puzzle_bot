<?php

class TelegramDB {
    private $conn;

    function __construct($servername, $dbname, $username, $password) {
        try {
            $this->conn = new PDO("mysql:host=$servername;port=3306;dbname=$dbname", $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    function addMessage($chatId, $messageText, $messageDate): void
    {
        $sql = "INSERT INTO messages (chat_id, message_text, message_date) VALUES (:chat_id, :message_text, :message_date)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':chat_id', $chatId);
        $stmt->bindParam(':message_text', $messageText);
        $stmt->bindParam(':message_date', $messageDate);
        $stmt->execute();
    }

    function addImage($x, $y, $path) {
        $sql = "INSERT INTO images (x, y, path) VALUES (:x, :y, :path)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':x', $x);
        $stmt->bindParam(':y', $y);
        $stmt->bindParam(':path', $path);
        $stmt->execute();
    }

//    function createTable() {
//        $sql = "CREATE TABLE IF NOT EXISTS messages (
//            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//            chat_id VARCHAR(30) NOT NULL,
//            message_text TEXT NOT NULL,
//            message_date DATETIME NOT NULL
//        )";
//        $this->conn->exec($sql);
//    }

    function close() {
        $this->conn = null;
    }
}
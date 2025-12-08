<?php
require_once "/home/ewenevh/config/database.php";

class Admin {
    public static function getByUsername($username) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM vin_admins WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

<?php
require_once "app/models/Admin.php";

class LoginController {
    public function form() {
        // Si déjà connecté, rediriger vers l'admin
        if (isset($_SESSION['admin'])) {
            header("Location: index.php?page=admin");
            exit;
        }
        
        $error = $_GET['error'] ?? '';
        require "app/views/login.php";
    }

    public function check() {

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $admin = Admin::getByUsername($username);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin'] = $admin['username'];
            header("Location: index.php?page=admin");
            exit;
        } else {
            header("Location: index.php?page=login&error=Identifiants incorrects");
            exit;
        }
    }

    public function logout() {  
           
        session_destroy();
        header("Location: index.php?page=login");
        exit;
    }
}
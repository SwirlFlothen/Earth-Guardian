<?php
// Simple CSRF token helper
if (session_status() === PHP_SESSION_NONE) session_start();

function csrf_token(){
    if (empty($_SESSION['csrf_token'])){
        try{
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Exception $e){
            // fallback
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
    }
    return $_SESSION['csrf_token'];
}

function csrf_input(){
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES);
    return "<input type=\"hidden\" name=\"csrf_token\" value=\"$t\">";
}

function validate_csrf(){
    if (!isset($_POST['csrf_token'])) return false;
    if (!isset($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

?>
<?php
require_once 'config/db.php';
require_once 'core/model.php';
require_once 'core/view.php';
require_once 'core/controller.php';
include_once 'core/route.php';

//var_dump('bootstrap GET',$_GET);
//Route::start(DataBase::get_connection());

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Генерируем токен, если он еще не существует
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


// Проверяем, авторизован ли пользователь
    // Проверяем наличие токена remember_me
    if (isset($_COOKIE['remember_me'])) {
        $token = $_COOKIE['remember_me'];

        include_once 'servise/work.php';

        
        Work::remember();

    }



Route::start();

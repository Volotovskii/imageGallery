<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Генерируем токен, если он еще не существует
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


require_once __DIR__ . '/../app/controller/AuthController.php';
require_once __DIR__ . '/../app/controller/ImageContoller.php';
require_once __DIR__ . '/../app/controller/ComentsController.php';


if (!isset($_SESSION['user']['USER_ID']) && isset($_COOKIE['remember_me'])) {

    $auth = new Auth_Controller(); // Создаём экземпляр для вызова метода
    $user = $auth->getToken($_SESSION['user']['USER_ID']); // вернёт профиль юзера
    // Если токен действителен и он вообще есть вернёт id юзера

    if ($user) {
        $_SESSION['user'] = $user; // или $user->id

    }
}



$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


switch ($uri) {
    case '/imageGallery/public/':
        require_once __DIR__ . '/../app/view/index.php';
        //var_dump($_SESSION);
        break;
    case '/imageGallery/public/get-csrf-token':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            // Возвращаем токен
            echo json_encode(['token' => $_SESSION['csrf_token']]);
            exit;
        }
        header("Location: /imageGallery/public/");
        exit;
        break;
    case '/imageGallery/public/register':
        $auth = new Auth_Controller();
        $auth->register();
        exit;
        break;
    case '/imageGallery/public/login':
        $auth = new Auth_Controller();
        $auth->login();
        exit;
        break;
    case '/imageGallery/public/logout':
        echo json_encode(['logout' => $_SESSION]);
        if (isset($_SESSION['user'])) {
            // удалить юзера из ссесиии?:
            $auth = new Auth_Controller();
            $auth->logout($_SESSION['user']['USER_ID']);
        }
        header("Location: /imageGallery/public/");
        exit;
        break;
    case '/imageGallery/public/show_images':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $show_images = new Image_Contoller();
            $st = $show_images->showImages();
        }
        header("Location: /imageGallery/public/");
        exit;
        break;
    case '/imageGallery/public/upload':
        $images = new Image_Contoller();
        $images->addImages();
        //var_dump('1');
        break;
    case '/imageGallery/public/load_bucket_image':
        $images = new Image_Contoller();
        $images->delImage();
        exit;
        break;
    case '/imageGallery/public/comments':
        $comments = new Coments_Controller();
        $comments->addComments();
        //var_dump('1');
        exit;
        break;
    case '/imageGallery/public/show_comments':
        $comments = new Coments_Controller();
        $comments->showComments();
        exit;
    case '/imageGallery/public/delete_comment':
        $comments = new Coments_Controller();
        $comments->deleteComment();
        exit;
    default:
        header("Location: /imageGallery/public/");
        exit;
        break;
}

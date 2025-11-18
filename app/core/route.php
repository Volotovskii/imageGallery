<?php

class Route
{

    public static function start()
    {

        $url = isset($_GET['url']) ? $_GET['url'] : 'Auth/index';
        $routes = explode('/', $url);

        if (!empty($routes[0])) {
            $controller_name = $routes[0];
        }

        // получаем имя действия
        if (!empty($routes[1])) {
            $action_name = $routes[1];
        }



        //Если пользователь в сети
        //if (!isset($_SESSION['user']['USER_ID']) && isset($_COOKIE['remember_me'])) {
        if (isset($_COOKIE['remember_me'])) {
            //if (!isset($_SESSION['user']['USER_ID'])) {



            //$user = $auth->action_getToken($_SESSION['user']['USER_ID']); // вернёт профиль юзера
            // Если токен действителен и он вообще есть вернёт id юзера


            // if ($user) {
            //     $_SESSION['user'] = $user; // или $user->id

            // }

            //TODO
            // if ($action_name === 'index') {
            //     $controller_name = 'Auth';
            //     $action_name = 'index';
            // }
            // $controller_name = 'Auth';
            // $action_name = 'getToken';
            //var_dump('123');
            // $controller_name = 'Auth';
            // $action_name = 'getToken';
            // if ($action_name === 'getToken') {
            //     //var_dump('123');
            //     $controller_name = 'Auth';
            //     $action_name = 'getToken';
            // }
            //var_dump($action_name);
        } else {

            //var_dump($action_name);
            if (!empty($routes[0])) {

                $controller_name = $routes[0];
            }

            // получаем имя действия
            if (!empty($routes[1])) {
                $action_name = $routes[1];
            }
        }

        // Обработка CSRF-токена
        if ($controller_name === 'auth' && $action_name === 'get-csrf-token') {
            header('Content-Type: application/json');
            echo json_encode(['token' => $_SESSION['csrf_token']]);
            exit;
        }


        // далее нам надо определить адресаа конроллер модель и дейсвие в них (актион)
        // Переводит первый символ строки в верхний регистр
        $model_name = 'Model_' . ucfirst($controller_name);
        //большими для класса
        $controller_name = 'Controller_' . ucfirst($controller_name);
        $action_name = 'action_' . $action_name;

        // подцепляем файл с классом модели (файла модели может и не быть)
        $model_file = strtolower($model_name) . '.php';
        $model_path = "app/models/" . $model_file;
        //существует ли указанный файл
        if (file_exists($model_path)) {
            //include "app/models/" . $model_file;
            include __DIR__ . '/../models/'   . $model_file;
        }

        // подцепляем файл с классом контроллера
        $controller_file = strtolower($controller_name) . '.php';
        $controller_path = __DIR__ . '/../controllers/' . $controller_file;

        if (file_exists($controller_path)) {
            //var_dump('$controller_file', $controller_file);
            include __DIR__ . '/../controllers/'  . $controller_file;
        } else {
            //Route::ErrorPage404();
        }


        // создаем контроллер
        // по классуи проверяем его метод
        $controller = new $controller_name;
        $action = $action_name;
        if (method_exists($controller, $action)) {
            // вызываем действие контроллера
            $controller->$action();
        } else {
            //Route::ErrorPage404();
        }
    }

    public static function ErrorPage404()
    {
        //$host = 'http://' . $_SERVER['HTTP_HOST'] . '/';
        //header('HTTP/1.1 404 Not Found');
        //header("Status: 404 Not Found");
        header('Location: /imageGallery/public/');
        exit();
    }
}

<?php


require_once __DIR__ . '/../config/db.php';

require_once __DIR__ . '/../models/AuthModel.php';



class Controller_Auth extends Controller
{
    private $logger;

    private $db;
    private $user;

    public function __construct()
    {
        parent::__construct();

        $dataBase = new DataBase();
        $this->db =  $dataBase->get_connection();

        $this->user = new Auth_Model($this->db);
    }

    public function action_index()
    {
        //include __DIR__ . '/../views/auth/login.php';
        // передаём путь к заполнению и стандартному файлу
        //include __DIR__ . '/../views/auth/login_modal.php';
        $this->view->generate('/index.php', 'template_view.php');
        //var_dump('123');
    }

    public function action_loginOpen()
    {
        include __DIR__ . '/../views/auth/login_modal.html';
        //$this->view->generateModal('auth/login_modal.php');
    }

    // добавить токен как в логине и удаление после отпавки формы..
    public function action_register()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //$erorrs = []; Сделать в модели? 
            if (isset($_POST['token']) && hash_equals($_POST['token'], $_SESSION['csrf_token'])) {

                $request = [
                    'login' => $_POST['login'] ?? '',
                    'password' => $_POST['password'] ?? '',
                    'repeat-password' => $_POST['repeat-password'] ?? ''
                ];

                $errors  = $this->user->validate($request);


                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(['success' => false, 'errors' => $errors]);
                    exit();
                }
                // $request обработать внутри модели
                if ($this->user->register($request)) {
                    echo json_encode(['success' => true, 'message' => 'Регистрация успешна']);
                    exit();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Ошибка регистрации']);
                    exit();
                }
            }
            echo json_encode(['success' => false, 'errors' => ['csrf_token не верный.']]);
            header("Location: /imageGallery/public/");
            exit;
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'errors' => ['Недопустимый метод запроса.']]);
            header("Location: /imageGallery/public/");
            exit;
        }
    }


    public function action_login()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (isset($_POST['token']) && hash_equals($_POST['token'], $_SESSION['csrf_token'])) {


                $request = [
                    'login' => $_POST['login'] ?? '',
                    'password' => $_POST['password'] ?? ''
                ];

                $errors = $this->user->validateLogin($request);


                //если ошибка
                if ($errors['success'] === false) {
                    echo json_encode($errors);
                    exit();
                }



                //всё ок
                if ($errors['success'] === true) {
                    // $errors['message'] - данные о пользователи..
                    $_SESSION['user'] = $errors['message'];

                    // Если токен ОК и ошибок нету в полях, удаляем 
                    unset($_SESSION['csrf_token']);

                    if (isset($_POST['remember_me'])) {
                        // проверка токена
                        //$getToken = $this->action_getToken($errors['message']['login']); attemptLogin
                        $getToken = $this->user->attemptLogin($errors['message']['login']);
                        if (isset($getToken)) {
                            echo json_encode(['success' => true, 'message' => $getToken]);
                            exit;
                        } else {
                            // new токен для бд 
                            // $errors['message'] - id логина..
                            $this->user->rememberUser($errors['message']['USER_ID']);
                            echo json_encode(['success' => true, 'message' => 'Создал remember_me']);
                            exit;
                        }
                    }

                    // Логин успешен
                    echo json_encode(['success' => true, 'message' => 'Вход успешен']);
                    exit;
                }
            } else {
                // Ошибка по csrf
                http_response_code(403);
                echo json_encode(['success' => false, 'errors' => ['CSRF токен недействителен.']]);
                // TODO перезапустить страницу
                exit;
            }
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'errors' => ['Недопустимый метод запроса.']]);
            // если зашли по url то вернуть на страницу index
            // TODO если так переходить то ломаетсся форма (вход\регстарция)
            header("Location: /imageGallery/public/");
            exit;
        }
    }


    public function action_logout()
    {
        // очистить куки и сессию
        // удалить токен в БД если есть
        $user_id = $_SESSION['user']['USER_ID'] ?? null;

        if (isset($_SESSION['user']) && isset($_SESSION['user']['USER_ID'])) {
            // удалить токен из БД!
            $this->user->delToken($_SESSION['user']['USER_ID']);
        }

        session_destroy();
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
        }
        //убрал
        header('Location: /imageGallery/public');
        exit;
    }

    //TODO ?? для прокидывание в модель вход логин\пароль
    // я ищу всегда по id логину и далее сравниваю TODO
    // старнно искать по сессии
    public  function action_getToken($request = NULL)
    {
        var_dump('$request', $request);
        //$token_from_cookie = $_COOKIE['remember_me'];

        $userIdToAttempt = null; // Инициализируем NULL

        if (isset($request)) {
            $userIdToAttempt = $request;
        } elseif (isset($_SESSION['user']) && isset($_SESSION['user']['USER_ID'])) {
            $userIdToAttempt = $_SESSION['user']['USER_ID'];
            //var_dump('$userIdToAttempt',$userIdToAttempt);
        }

        // Если userIdToAttempt все еще NULL, значит у нас нет ID для попытки логина
        if ($userIdToAttempt === null) {
            //var_dump('$userIdToAttempt123',$userIdToAttempt);
            $this->action_logout(true);
            return NULL;
        }

        $user = $this->user->attemptLogin($userIdToAttempt);
        // ... остальной код
        //var_dump('$user',$user);



        // Если токен действителен и он вообще есть вернёт id юзера
        // Добавить удаление токена из баззы данных и перенаправление на вход
        // если токен и дата не верные 
        // $request = isset($request) ? $request :  $_SESSION['user']['USER_ID'];
        // $user = $this->user->attemptLogin($request);
        // var_dump('action_getToken', $user);

        if (isset($user)) {
            $_SESSION['user'] = $user; // или $user->id
            //var_dump($user);

            //return $user;
        } 
    }
}

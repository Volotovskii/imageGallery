<?php


require_once __DIR__ . '/../config/db.php';

require_once __DIR__ . '/../model/AuthModel.php';



class Auth_Controller
{
    private $logger;

    private $db;
    private $user;

    public function __construct()
    {


        $dataBase = new DataBase();
        $this->db =  $dataBase->get_connection();

        $this->user = new Auth_Model($this->db);
    }


    // добавить токен как в логине и удаление после отпавки формы..
    public function register()
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


    public function login()
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
                        $getToken = $this->getToken($errors['message']['login']);

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


    public function logout($user_id)
    {
        // очистить куки и сессию
        // удалить токен в БД если есть

        session_destroy();
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
            // удалить токен из БД!
            $this->user->delToken($user_id);
        }
    }

    //TODO ?? для прокидывание в модель вход логин\пароль
    public function getToken($request)
    {
        // Добавить удаление токена из баззы данных и перенаправление на вход
        // если токен и дата не верные
        return $this->user->attemptLogin($request);
    }
}

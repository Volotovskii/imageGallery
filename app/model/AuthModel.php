<?php

class Auth_Model
{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // для входа через вк сделал public
    public function loginSearch($login)
    {

        $query = 'SELECT * FROM user WHERE login = ?';
        $st = $this->db->prepare($query);
        $st->execute([$login]);
        return  $st->fetch(PDO::FETCH_ASSOC); // ассоциативный
    }


    private function idSearch($user_id)
    {

        $query = 'SELECT * FROM user WHERE user_id = ?';
        $st = $this->db->prepare($query);
        $st->execute([$user_id]);
        return  $st->fetch(PDO::FETCH_ASSOC); // ассоциативный
    }

    //в бд всталвяем юзера
    private function inserUser($user)
    {
        $query = 'INSERT INTO user (login, password, role) VALUES(?,?,?)';
        $st = $this->db->prepare($query);
        return $st->execute($user);
    }

    //в бд всталвяем токен для сессиии 
    private function insertToken($userId, $hashedToken)
    {
        $query = 'INSERT INTO user_token (user_id, token_hash, expires_at) VALUES (?,?,?)';

        $vladivostok_timezone = new DateTimeZone('Asia/Vladivostok');

        $data = new DateTime('now', $vladivostok_timezone);
        $data->modify('+4 minute');  // добавляем срок жизни + неделя  +1 hour
        $expires_at = $data->format('Y-m-d H:i:s');

        //$expires_at = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // Срок жизни 30 дней
        $st = $this->db->prepare($query);
        $st->execute([$userId, $hashedToken, $expires_at]);

        return $expires_at;
    }

    //поиск ткоена в бд
    private function searchToken($user_id)
    {
        // $query = 'SELECT user_id, token_hash FROM user_token WHERE expires_at > ?';
        // $st = $this->db->prepare($query);
        // $st->execute([date('Y-m-d H:i:s')]);

        $query = 'SELECT user_id, token_hash  FROM user_token WHERE user_id = ? AND expires_at > ?';
        $st = $this->db->prepare($query);
        $st->execute([$user_id, date('Y-m-d H:i:s')]);

        return $st->fetch(PDO::FETCH_ASSOC);
    }

    // Удаляем токен из БД
    public function delToken($user_id)
    {
        $query = 'DELETE FROM user_token WHERE user_id = ?';
        $st = $this->db->prepare($query);
        $st->execute([$user_id]);
    }

    //регистрация
    public function validate($request)
    {
        $errors = [];


        //TODO проверить пробелы в логине
        // отработать 'false'
        if (!isset($request['login']) || strlen($request['login']) <= 0) {
            //$errors['login'] = 'Логин не указан';
            $errors[]['login'] = 'Логин не указан';
        } elseif (strlen($request['login']) > 15) {
            $errors[]['login'] = 'Логин не должен быть больше 16 символов';
        } elseif ($this->loginSearch($request['login']) !== false) {
            $errors[]['login'] = 'Логин уже существует';
        }

        if (!isset($request['password']) || empty($request['password'])) {
            $errors[]['password'] = 'Пароль не указан';
        }

        if (!isset($request['repeat-password']) || empty($request['repeat-password'])) {
            $errors[]['repeat-password'] = 'Нужно повторить пароль';
        }

        if (isset($request['password']) && isset($request['repeat-password'])) {
            if ($request['password'] != $request['repeat-password']) {
                $errors[]['repeat-password'] = 'Пароли не совпадают';
            }
        }

        return $errors;
    }

    //вход
    public function validateLogin($request)
    {
        $errors = [];
        //$user = $this->loginSearch($request['login']) ?? '';


        // // //TODO проверить пробелы в логине
        // // отработать 0 в логине и пароле 
        // для логина - 0000 \ 0 и т.к 'false'
        // if (!isset($request['login']) || strlen($request['login']) <= 0) {
        //     $errors[]['login'] = 'Логин не указан';
        // } elseif (strlen($request['login']) > 15) {
        //     $errors[]['login'] = 'Логин не должен быть больше 16 символов';
        // } elseif ($user === false) {
        //     $errors[]['login'] = 'Логин не существует';
        // }

        // if (!isset($request['password']) || empty($request['password'])) {
        //     $errors[]['password'] = 'Пароль не указан';
        // } elseif (!isset($request['login']) || strlen($request['login']) <= 0) {
        //     $errors[]['password'] = 'Укажите логин';
        // } elseif (!password_verify($request['password'], $user['PASSWORD'])) {
        //     $errors[]['password'] = 'Пароль не верный';
        // }

        // Проверяем логин
        if (!isset($request['login']) || strlen($request['login']) <= 0) { // Исправлено
            $errors[]['login'] = 'Логин не указан';
        } elseif (strlen($request['login']) > 15) {
            $errors[]['login'] = 'Логин не должен быть больше 16 символов';
        } else {
            // Только если логин указан, пытаемся его найти
            $user = $this->loginSearch($request['login']);

            if ($user === false) {
                $errors[]['login'] = 'Логин не существует';
            } else {
                // Логин найден, проверяем пароль
                if (!isset($request['password']) || empty($request['password'])) {
                    $errors[]['password'] = 'Пароль не указан';
                } elseif (!password_verify($request['password'], $user['PASSWORD'])) { // Теперь $user не false
                    $errors[]['password'] = 'Пароль не верный';
                }
                // Если логин и пароль верны, $user будет содержать данные
            }
        }

        //return $errors;
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        } else {
            //передаём все данные клиента? $user['USER_ID']
            return ['success' => true, 'message' => $user];
        }
    }

    //Регистарция хэш + вызов inserUser
    public function register($request)
    {

        $user = [
            $request['login'],
            password_hash($request['password'], PASSWORD_DEFAULT),
            $request['role'] ?? 'user'
        ];
        return $this->inserUser($user);
    }

    // если выбрали Запомнить меня (insertToken для бд)
    public function rememberUser($userId)
    {

        $token = bin2hex(random_bytes(32));
        $hashedToken = password_hash($token, PASSWORD_DEFAULT); // Хешируем

        //strtotime для куков нужен timestamp!
        $expires_at = strtotime($this->insertToken($userId, $hashedToken));


        setcookie('remember_me', $token, $expires_at, '/', '', true, true);
    }

    // в данный момент изер id берём и сравниваем с датой
    // Валидация токена
    public function attemptLogin($request)
    {

        if (isset($_COOKIE['remember_me'])) {
            $token = $_COOKIE['remember_me'];

            // Ищем запись в базе данных по хешу токена

            $row = $this->searchToken($request);

            if ($row && password_verify($token, $row['token_hash'])) {
                // Токен валиден, возвращаем user_id
                return $this->idSearch($request);
            } else {

                // Токен не валиден или истек, удаляем cookie
                $this->delToken($_SESSION['user']['USER_ID']);
                setcookie('remember_me', '', time() - 3600, '/'); // Удаляем cookie

                //на страницу регстарции
                return 'Токен не валиден или истек, удаляем cookie';
            }
        }

        return NULL; // Пользователь не может быть автоматически залогинен
    }



}

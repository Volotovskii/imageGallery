<?php
require_once __DIR__ . '/../config/db.php';

require_once __DIR__ . '/../models/AuthModel.php';

//TODO
//Для провекра remember в бусрап файле
class Work
{


    public static function remember($request = NULL)
    {
        $dataBase = new DataBase();
        $db =  $dataBase->get_connection();

        $user = new Auth_Model($db);


        $userIdToAttempt = null; // Инициализируем NULL

        if (isset($request)) {
            $userIdToAttempt = $request;
        } elseif (isset($_SESSION['user']) && isset($_SESSION['user']['USER_ID'])) {
            $userIdToAttempt = $_SESSION['user']['USER_ID'];
        }

        // Если userIdToAttempt все еще NULL, значит у нас нет ID для попытки логина
        if ($userIdToAttempt === null) {
            //var_dump('$userIdToAttempt123',$userIdToAttempt);
            //$this->action_logout(true);
            return NULL;
        }

        $user = $user->attemptLogin($userIdToAttempt);


        if (isset($user)) {
            $_SESSION['user'] = $user; // или $user->id
            //var_dump($user);

            //return $user;
        }
    }
}

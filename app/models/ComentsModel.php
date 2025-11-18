<?php

class Coments_Model
{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // image_id	int	
    // author	int	
    // text	text NULL
    private function addDb($image_id, $user_id, $user_login, $comments)
    {
        $query = 'INSERT INTO comments (image_id, author_id, author, text) VALUES (?, ?, ?, ?)';
        $st = $this->db->prepare($query);
        //$st->execute([$image_id, $user_id, $comments]);
        return $st->execute([$image_id, $user_id, $user_login, $comments]);
    }

    // Проверяем есть image TODO 
    // в моменте автор мог удалить его
    private function getImages($image_id)
    {
        $query = 'SELECT * FROM images WHERE id = ?';

        $st = $this->db->prepare($query);
        $st->execute([$image_id]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    //выводим все комменты привязка image_id
    private function getAllComments($image_id)
    {
        $query = 'SELECT * FROM comments WHERE image_id = ? ORDER BY created_at DESC';

        $st = $this->db->prepare($query);
        $st->execute([$image_id]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    //удаляем коммент через транзакцию
    public function getDelComments1($commentId)
    {
        // $this->db — PDO, ожидается, что PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        try {
            // Начинаем транзакцию
            $this->db->beginTransaction();

            // получаем коомент и блокируем его FOR UPDATE
            $select = $this->db->prepare('SELECT id FROM comments WHERE id = :id FOR UPDATE');
            $select->execute([':id' => $commentId]);
            $row = $select->fetch(PDO::FETCH_ASSOC);

            // Если комментарий не найден просто фиксируем и false
            if (!$row) {
                $this->db->commit();
                return false;
            }

            // удаляем коммент
            $delete = $this->db->prepare('DELETE FROM comments WHERE id = :id');
            $delete->execute([':id' => $commentId]);

            // если не смогли удалить откатываем транзацию
            if ($delete->rowCount() === 0) {
                $this->db->rollBack();
                return false;
            }


            // Фиксируем транзакцию
            $this->db->commit();
            return true;

            //если бд не доступна упадём в ошибку
        } catch (\Throwable $e) {
            // В случае ошибки откатываем транзакцию и пробрасываем исключение
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    //проверяем текст коммента
    public function validate($data)
    {


        $errors = [];

        if (strlen($data['message']) <= 0) {
            $errors[] = 'Введите комментарий.';
        }

        //if (strlen($data['message']) > 100) {
        if (mb_strlen($data['message']) > 100) {
            $errors[] = 'Комментарий больше 100 символов.';
        }

        if (!isset($data['id']) || empty($data['id'])) {
            $errors[] = 'Фотография не выбранна.';
        }


        if (!isset($_SESSION['user']['USER_ID']) || empty($_SESSION['user']['USER_ID'])) {
            $errors[] = 'Вы не авторизированны.';
        }

        if (!($this->getImages($data['id']))) {
            $errors[] = 'Владелец удалил фотографию(';
        }

        return $errors;
    }

    //Добавляем коммент
    public function addComments($data)
    {

        // проверяем есть ли фотка при загрузке к бд! мб её в моменте удалили как тогда отработает доабвления комента?
        // Обработка результат + ошибки возражаем их? или тру? или в другую функцю валиадацию вывести хм
        //$_SESSION['user']['USER_ID'];
        $ad = $this->addDb($data['id'], $_SESSION['user']['USER_ID'], $_SESSION['user']['login'], $data['message']);

        //решил через транзакции
        // if ($ad) {
        //     return $ad;
        // }
        return $ad;
    }

    //выводим все комменты
    public function showComments($image_id)
    {
        //выводим для опредл. изображ.
        //запрашиваем юзера и если сходится с тем которые выводим добавляем в js кнопку удаления


        $comments = $this->getAllComments($image_id);

        // если пользователь не вошёл в аккаунт
        if (!isset($_SESSION['user']) && !isset($_SESSION['user']["USER_ID"])) {
            return $comments;
        }

        $user = $_SESSION['user']["USER_ID"];


        //меняем напрямую по ссылке
        foreach ($comments as &$comment) {
            // поменять в БД author_id и author логин именно 
            if ($comment['author_id'] === $user) {
                $comment['is_owner'] = true;
            } else {
                $comment['is_owner'] = false;
            }
        }

        return $comments;
        //return $this->getImages($image_id);

    }
}

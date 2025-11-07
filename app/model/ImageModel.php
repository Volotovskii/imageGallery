<?php

//namespace app\model;


class Image_Model
{

    private $db;


    public function __construct($db)
    {
        $this->db = $db;
    }

    // добавляем изображение имя , уникаольное имя и клиент который создал?
    private function addImages($fileName, $path_file, $user_id)
    {
        $query = 'INSERT INTO images (file_name,unique_name,user_id) VALUES (?,?,?)';

        $st = $this->db->prepare($query);
        //$st->bind_param("s", $fileName);
        $st->execute([$fileName, $path_file, $user_id]);
        // $st->execute();
        //$st->close();
        //$stmt = $this->db->prepare("INSERT INTO images (filename) VALUES (?)");
        //$stmt->bind_param("s", $filename);
        //$stmt->execute();
        //$stmt->close();
    }


    // выводим все image сортируем DESC
    private function getAllImages()
    {
        $query = 'SELECT * FROM images ORDER BY uploaded_at DESC';

        $st = $this->db->prepare($query);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    // сделаю через транзакцию.
    private function delImageDb($image_id)
    {

        try {
            // Начинаем транзакцию
            $this->db->beginTransaction();

            // получаем image и блокируем его FOR UPDATE
            $select = $this->db->prepare('SELECT id FROM images WHERE id = :id FOR UPDATE');
            $select->execute([':id' => $image_id]);
            $row = $select->fetch(PDO::FETCH_ASSOC);

            // Если фотография не найден просто фиксируем и false

            if (!$row) {
                $this->db->commit();
                return false;
            }

            // удаляем image
            $delete = $this->db->prepare('DELETE FROM images WHERE id = :id');
            $delete->execute([':id' => $image_id]);

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

    // Проверяем есть image TODO 
    // в моменте автор мог удалить его
    private function getImages($image_id)
    {
        $query = 'SELECT * FROM images WHERE id = ?';

        $st = $this->db->prepare($query);
        $st->execute([$image_id]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function validate($file)
    {

        // $errors = [];
        // $errors[]['login'] = 'Логин не указан';
        // if ($request['userfile']['type'] === ALLOWED_MIME_TYPES) {
        //     //var_dump($request['userfile']['type']);
        //     return $request['userfile']['type'];
        // }
        // return $errors;
        // Если файл добавлен
        //return true;

        $errors = [];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Произошла ошибка при загрузке файла.';
        }

        $filetype = mime_content_type($file['tmp_name']);
        if (!in_array($filetype, ALLOWED_MIME_TYPES)) {
            $errors[] = 'Неподдерживаемый тип файла.';
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'Превышено максимальное ограничение размера файла.';
        }

        if (!isset($_SESSION['user']['USER_ID']) || empty($_SESSION['user']['USER_ID'])) {
            $errors[] = 'Вы не авторизированны.';
        }

        return $errors;
    }

    // проверим и  пометим(owner) если фотки пользователя который авторизирован
    // Так же атрибут для кнопки удаления
    public function showGallery()
    {

        $images = $this->getAllImages();

        if (!isset($_SESSION['user']['USER_ID']) || empty($_SESSION['user']['USER_ID'])) {
            return $images;
        }

        $user = $_SESSION['user']["USER_ID"];

        //TODO изменить ответ? не передавать пользователя кто создал фотку?

        //меняем напрямую по ссылке
        foreach ($images as &$image) {
            if ($image['user_id'] === $user) {
                $image['is_owner'] = true;
                $image['image_delete'] = true;
            } else {
                $image['is_owner'] = false;
                $image['image_delete'] = true;
            }
        }

        return $images;
    }

    public function uploadImage($file)
    {


        //TODO проверять существует ли папка куда грузим? s3

        //TODO генерируем уникальное наименования файла в базе оно и + оригинал так же какой клиент загрузил

        //последний компонент имени
        $filename = basename($file['name']);

        // расширение файла
        $file_extension = pathinfo($filename, PATHINFO_EXTENSION);

        // Уникальное имя для файла
        // от времени  TODO гуид ?? 
        $unique_filename = md5(uniqid(rand(), true)) . '.' . $file_extension;

        // путь к файлу на сервере уникальный
        $path_file = UPLOAD_DIR . $unique_filename;


        // Перемещаем загруженный файл
        if (move_uploaded_file($file['tmp_name'], $path_file)) {
            //загружаем в бд
            //проверить доп есть ли такой юзер? или хвтит при входе хм
            $this->addImages($filename, $unique_filename, $_SESSION['user']['USER_ID']);

            return true;
        }

        return false;
    }

    //получаем id удаляемого изображения
    // в папке у нас уникальные фотографии с image_id получаем его
    public function delImage($image_id)
    {

        // Проверяем есть ли такая фотография 
        $image = $this->getImages($image_id);

        if (!$image) {
            echo json_encode(['success' => false, 'message' => 'Изображение не найдено.']);
            exit();
        }

        // Проверяем какой id пришёл с сайта и какой у фотки если не сходится не удаляем
        if ($image[0]['user_id'] !== $_SESSION['user']['USER_ID']) {
            echo json_encode(['success' => false, 'message' => 'У вас нет прав для удаления этого изображения.']);
            exit();
        }

        if ($this->delImageDb($image_id)) {
        }


        //$filePath = 'path/to/your/photo.jpg'; 

        $del_image = $this->delImageDb($image_id);

        $filePath = UPLOAD_DIR . $image[0]['unique_name']; // путь к файлу на сервере уникальный
        if (unlink($filePath)) {
        } else {
            echo json_encode(['success' => false, 'message' => 'Изображение не найденно.']);
            exit();
        }

        return  $del_image;
    }
}

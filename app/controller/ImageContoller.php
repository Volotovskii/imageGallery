<?php

//namespace app\controllers;

//use app\model\Image_Model;

require_once __DIR__ . '/../config/db.php';
//require_once __DIR__ . '/../models/ImageModel.php'; // Подключаем модель Image_Model

require_once __DIR__ . '/../model/ImageModel.php';
//use app\models\Image_Model; // Импортируем класс Image_Model

//use DataBase; // Импортируем класс DataBase из глобального пространства имен
//use Image_Model;

class Image_Contoller
{

    private $db;
    private $image;

    public function __construct()
    {
        $DataBase = new DataBase();

        $this->db = $DataBase->get_connection();

        $this->image = new Image_Model($this->db);
    }

    //Показываем все фотографии
    public function showImages()
    {
        header('Content-Type: application/json');

        // верну фотографии + owner и ещё атрибут для кнопки удаления.. обдумать
        $image_all = $this->image->showGallery();


        echo json_encode(['success' => true, 'images' => $image_all]);
        exit;



        return $image_all;
    }

    // добавляем фотографию
    public function addImages()
    {

        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {

            // Передовать ли токен при выборе файла подтянуть
            //if (isset($_POST['token']) && hash_equals($_POST['token'], $_SESSION['csrf_token'])) {

            $file = $_FILES['image'];

            // проверка файла валидация 
            $errors = $this->image->validate($file);

            if (!empty($errors)) {
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            }

            if ($this->image->uploadImage($file)) {
                echo json_encode(['success' => true, 'message' => ['Файл загружен']]);
                exit;
            } else {
                echo json_encode(['success' => false, 'errors' => ['Ошибка загрузки файла']]);
                exit;
            }
        }
        header("Location: /imageGallery/public/");
    }

    // удаляем фотграфию && коменты
    public function delImage()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['image_id'])) {


            // Получаем ID изображения из URL-параметра
            $image_id = $_POST['image_id'];
            $om = $this->image->delImage($image_id);

            
            echo json_encode(['success' => true, 'delImage' => $om]);
            exit;
        }
        header("Location: /imageGallery/public/");
    }
}

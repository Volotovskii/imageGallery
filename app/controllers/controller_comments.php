<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/ComentsModel.php';
//Controller_Auth extends Controller
class Controller_Comments extends Controller
{

    private $db;
    private $comments;

    public function __construct()
    {
        $DataBase = new DataBase();
        $this->db = $DataBase->get_connection();

        $this->comments = new Coments_Model($this->db);
    }

    public function action_showComments()
    {

        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['image_id'])) {

            // для показа комента нам нужна фотка для которой мы показываем их
            // в самом коментарии есть id пользователя котоорый его добавил , может сделать имя? и сразу его прогружать?
            // передаём только id изображения

            $comments_all = $this->comments->showComments($_GET['image_id']);



            if (is_array($comments_all)) {
                echo json_encode(['success' => true, 'comments' => $comments_all]);
                exit;
            } else {       
                echo json_encode(['success' => false, 'error' => 'Ошибка при получении комментариев из базы данных.']);
                exit;
            }

        }

        header("Location: /imageGallery/public/");
    }

    public function action_addComments()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Ожидаем json поэтому работаем с ним отправили через пост
            $json_data = file_get_contents('php://input');

            // ставим тру работаем с ассоциативным и декодируем 
            $data = json_decode($json_data, true);


            // проверка файла валидация 
            $errors = $this->comments->validate($data);

            if (!empty($errors)) {
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            }
            $yu = $this->comments->addComments($data);
            if ($yu) {
                echo json_encode(['success' => true, 'message' => $yu]);
                exit;
            } else {
                echo json_encode(['success' => false, 'errors' => $yu]);
                exit;
            }
        }
        header("Location: /imageGallery/public/");
        // echo json_encode(['success' => true, 'message' => $data]);
        // exit;
    }

    public function action_deleteComment()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $json_data = file_get_contents('php://input');
            $data = json_decode($json_data, true);

            $delComment = $this->comments->getDelComments1($data['comment_id']);

            if ($delComment) {
                echo json_encode(['success' => true, 'delComment' => $delComment]);
                exit;
            }

            echo json_encode(['success' => false, 'delComment' => $delComment]);
            exit;
        }
        header("Location: /imageGallery/public/");
    }
}

<?php 

    define('UPLOAD_DIR', __DIR__ . '/../../public/images/');
    define('COMMENT_DIR', __DIR__ . 'comments');

    define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
    define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/gif']);


    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASSWORD', '777');
    define('DB_NAME', 'test');

?>
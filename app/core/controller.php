<?php

class Controller
{

    public $model;
    public $view;

    function __construct()
    {
        //будет использоваться для генерации представлений
        $this->view = new View();
    }


}

//TODO делаем в общем контроллере и прокидываем 
// <?php
// class Controller
// {
//     public $model;
//     public $view;
//     protected $db;

//     function __construct($db)
//     {
//         $this->view = new View();
//         $this->db = $db;
//     }
// }
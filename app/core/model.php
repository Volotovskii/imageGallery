<?php 
//Общий класс для всего моделей реализовываем общий метод для многих где исполуется один и тот же  
//
//
//

class Model
{
    public $string;
    public function __construct()
    {
        $this->string = "MVC + PHP = Awesome!";
    }
}
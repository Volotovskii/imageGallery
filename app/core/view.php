<?php

class View
{
    //$content_view - внутри шаблона текст
    function generate($content_view, $template_view, $data = null)
    {
        //подключается файл шаблона, который включает файл представления.
        //include  __DIR__ . 'app/views/' . $template_view;
        //$this->view->generate( __DIR__ . '/../views/auth/login.php', __DIR__ . '/../views/template_view.php');
        //var_dump($template_view);
 
        //include  __DIR__ . '/../views/auth/login_modal.php' .  $template_view;
        include  __DIR__ . '/../views/' .  $template_view;
    }

    // общий метод для модальных оконо (без доп контента)
    function generateModal($content_view, $data = null)
    {

        include  __DIR__ . '/../views/' .  $content_view;
    }
}

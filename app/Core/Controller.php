<?php

namespace App\Core;

class Controller
{
    public function view($view, $data = [])
    {
        extract($data);
        $viewPath = ROOT.'/app/Views/'.$view.'.php';

        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            echo "View not found: $view";
        }
    }

    public function redirect($url)
    {
        header('Location: '.$url);
        exit();
    }
}

<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\FlashHelper;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }

        return $this->view('login');
    }

    public function login()
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $userModel = new User;
        $user = $userModel->attempt($username, $password);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            FlashHelper::set('success', 'Welcome Back', 'Login successful.');
            header('Location: /');
            exit;
        } else {
            FlashHelper::set('error', 'Login Failed', 'Invalid credentials');
            header('Location: /login');
            exit;
        }
    }

    public function logout()
    {
        session_destroy();
        header('Location: /login');
        exit;
    }
}

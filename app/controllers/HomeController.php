<?php

class HomeController
{
    public function index()
    {
        // Redirect to dashboard if logged in, otherwise to login
        if (isset($_SESSION['user_id'])) {
            header('Location: /Quan_ly_trung_tam/public/dashboard');
        } else {
            header('Location: /Quan_ly_trung_tam/public/login');
        }
        exit;
    }
}
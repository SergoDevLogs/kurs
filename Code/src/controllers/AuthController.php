<?php
// src/controllers/AuthController.php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Employee.php';

class AuthController {
    private $pdo;
    private $userModel;
    private $employeeModel;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
        $this->employeeModel = new Employee($pdo);
    }
    
    public function loginForm() {
        if (isset($_SESSION['user_id'])) {
            $this->redirectByRole($_SESSION['user_role']);
            return;
        }
        require_once __DIR__ . '/../views/auth/login.php';
    }
    
    public function login() {
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $user = $this->userModel->login($login, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user['employee_contract'];
            $_SESSION['user_name'] = $user['employee_fullname'];
            $_SESSION['user_role'] = $user['user_role'];
            $_SESSION['user_establishment'] = $user['establishment_adress'];
            
            $this->redirectByRole($user['user_role']);
            return;
        }
        
        $error = 'Неверный логин или пароль';
        require_once __DIR__ . '/../views/auth/login.php';
    }
    
    public function logout() {
        session_destroy();
        header('Location: /login');
        exit;
    }
    
    public function registerForm() {
        $this->checkAdmin();
        
        $establishments = $this->employeeModel->getEstablishments();
        
        require_once __DIR__ . '/../views/user/register.php';
    }
    
    public function register() {
        $this->checkAdmin();
        
        $fullname = $_POST['fullname'] ?? '';
        $position = $_POST['position'] ?? '';
        $establishment = $_POST['establishment'] ?? '';
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $roleMap = [
            'Продавец-кассир' => 'cashier',
            'Складской работник' => 'warehouse',
            'Товаровед' => 'merchandiser'
        ];
        $role = $roleMap[$position] ?? '';
        
        if ($fullname && $position && $establishment && $login && $password && $role) {
            try {
                $this->pdo->beginTransaction();
                
                $employeeId = $this->employeeModel->create($fullname, $position, $establishment);
                $this->userModel->create($employeeId, $login, $password, $role);
                
                $this->pdo->commit();
                $_SESSION['success'] = 'Пользователь успешно создан';
            } catch (Exception $e) {
                $this->pdo->rollBack();
                $_SESSION['error'] = 'Ошибка при создании: ' . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = 'Заполните все поля';
        }
        
        header('Location: /admin/users');
        exit;
    }
    
    public function listUsers() {
        $this->checkAdmin();
        
        $users = $this->userModel->getAllWithDetails();
        require_once __DIR__ . '/../views/user/list.php';
    }
    
    public function deleteUser() {
        $this->checkAdmin();
        
        $contract = $_POST['contract'] ?? '';
        if ($contract && $contract != $_SESSION['user_id']) {
            $this->userModel->delete($contract);
            $_SESSION['success'] = 'Пользователь удален';
        }
        
        header('Location: /admin/users');
        exit;
    }
    
    private function checkAdmin() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
    }
    
    private function redirectByRole($role) {
        switch ($role) {
            case 'cashier':
                header('Location: /cashier');
                break;
            case 'warehouse':
                header('Location: /warehouse');
                break;
            case 'merchandiser':
                header('Location: /merchandiser');
                break;
            case 'admin':
                header('Location: /admin');
                break;
            default:
                header('Location: /login');
        }
        exit;
    }
}

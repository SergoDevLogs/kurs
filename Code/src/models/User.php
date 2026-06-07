<?php
// src/models/User.php

class User {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function login($login, $password) {
        $stmt = $this->pdo->prepare("
            SELECT u.*, e.employee_fullname, e.employee_position, e.establishment_adress
            FROM users u
            JOIN employee e ON u.employee_contract = e.employee_contract
            WHERE u.user_login = ? AND u.user_password = ?
        ");
        $stmt->execute([$login, $password]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($employeeContract, $login, $password, $role) {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (employee_contract, user_login, user_password, user_role)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$employeeContract, $login, $password, $role]);
    }
    
    public function getAllWithDetails() {
        $stmt = $this->pdo->query("
            SELECT u.*, e.employee_fullname, e.employee_position, e.establishment_adress
            FROM users u
            JOIN employee e ON u.employee_contract = e.employee_contract
            ORDER BY u.employee_contract
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function delete($employeeContract) {
        // Сначала удаляем пользователя
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE employee_contract = ?");
        $stmt->execute([$employeeContract]);
        
        // Затем удаляем сотрудника (каскадное удаление не настроено в БД)
        $stmt2 = $this->pdo->prepare("DELETE FROM employee WHERE employee_contract = ?");
        return $stmt2->execute([$employeeContract]);
    }
    
    public function getById($contract) {
        $stmt = $this->pdo->prepare("
            SELECT u.*, e.employee_fullname, e.employee_position, e.establishment_adress
            FROM users u
            JOIN employee e ON u.employee_contract = e.employee_contract
            WHERE u.employee_contract = ?
        ");
        $stmt->execute([$contract]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

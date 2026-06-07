<?php
// src/models/Employee.php

class Employee {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function create($fullname, $position, $establishment) {
        $stmt = $this->pdo->query("SELECT COALESCE(MAX(employee_contract), 0) + 1 FROM employee");
        $nextId = $stmt->fetchColumn();
        
        $stmt = $this->pdo->prepare("
            INSERT INTO employee (employee_contract, employee_fullname, employee_position, establishment_adress)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$nextId, $fullname, $position, $establishment]);
        return $nextId;
    }
    
    public function getEstablishments() {
        $stmt = $this->pdo->query("
            SELECT establishment_adress FROM establishment ORDER BY establishment_adress
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

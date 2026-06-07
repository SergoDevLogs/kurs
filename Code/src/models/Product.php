<?php
// src/models/Product.php

class Product {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAll() {
        $stmt = $this->pdo->query("
            SELECT product_article, product_name, product_selfcost, product_factcost 
            FROM product 
            ORDER BY product_article
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($article) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM product WHERE product_article = ?
        ");
        $stmt->execute([$article]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

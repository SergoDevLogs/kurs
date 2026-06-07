<?php
// src/models/Stock.php

class Stock {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
public function getByEstablishmentPaginated($address, $page = 1, $perPage = 10, $sort = 'product_name', $order = 'ASC') {
    $offset = ($page - 1) * $perPage;
    
    $allowedSort = ['product_name', 'quantity', 'product_selfcost'];
    if (!in_array($sort, $allowedSort)) {
        $sort = 'product_name';
    }
    
    $sql = "
        SELECT c.establishment_adress, c.product_article, c.containing_num as quantity,
               p.product_name, p.product_selfcost
        FROM containing c
        JOIN product p ON c.product_article = p.product_article
        WHERE c.establishment_adress = ?
        ORDER BY $sort $order
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$address, $perPage, $offset]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getByEstablishmentCount($address) {
    $stmt = $this->pdo->prepare("
        SELECT COUNT(*) as total 
        FROM containing 
        WHERE establishment_adress = ?
    ");
    $stmt->execute([$address]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}
    
    public function getStocksCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM containing");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    public function getAllStocksPaginated($page = 1, $perPage = 10, $sort = 'establishment_adress', $order = 'ASC') {
        $offset = ($page - 1) * $perPage;
        
        $allowedSort = ['establishment_adress', 'product_name', 'quantity', 'product_selfcost'];
        if (!in_array($sort, $allowedSort)) {
            $sort = 'establishment_adress';
        }
        
        $sql = "
            SELECT c.establishment_adress, c.product_article, c.containing_num as quantity,
                   p.product_name, p.product_selfcost
            FROM containing c
            JOIN product p ON c.product_article = p.product_article
            ORDER BY $sort $order
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$perPage, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllStocks() {
        $stmt = $this->pdo->query("
            SELECT c.establishment_adress, c.product_article, c.containing_num as quantity,
                   p.product_name, p.product_selfcost
            FROM containing c
            JOIN product p ON c.product_article = p.product_article
            ORDER BY c.establishment_adress, p.product_name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

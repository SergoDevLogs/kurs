<?php
// src/models/Transportation.php

class Transportation {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAll($startDate = null, $endDate = null, $page = 1, $perPage = 10, $sort = 'transportation_date', $order = 'DESC') {
        $offset = ($page - 1) * $perPage;
        
        $allowedSort = ['transportation_date', 'transportation_distance', 'product_name'];
        if (!in_array($sort, $allowedSort)) {
            $sort = 'transportation_date';
        }
        
        $sql = "
            SELECT t.transportation_id, t.product_article, p.product_name,
                   t.establishment_adress_from, t.establishment_adress_to,
                   t.transportation_status, t.transportation_type,
                   t.transportation_distance, t.transportation_date
            FROM transportation t
            JOIN product p ON t.product_article = p.product_article
            WHERE 1=1
        ";
        $params = [];
        
        if ($startDate) {
            $sql .= " AND t.transportation_date >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND t.transportation_date <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY $sort $order
                  LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCount($startDate = null, $endDate = null) {
        $sql = "SELECT COUNT(*) as total FROM transportation WHERE 1=1";
        $params = [];
        
        if ($startDate) {
            $sql .= " AND transportation_date >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND transportation_date <= ?";
            $params[] = $endDate;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    public function create($productArticle, $fromAddress, $toAddress, $type, $distance, $status = 1) {
        $stmt = $this->pdo->prepare("
            INSERT INTO transportation (product_article, establishment_adress_from, 
                                       establishment_adress_to, transportation_type, 
                                       transportation_distance, transportation_status,
                                       transportation_date)
            VALUES (?, ?, ?, ?, ?, ?, CURRENT_DATE)
        ");
        return $stmt->execute([$productArticle, $fromAddress, $toAddress, $type, $distance, $status]);
    }
    
    public function updateStatus($transportationId, $status) {
        $stmt = $this->pdo->prepare("UPDATE transportation SET transportation_status = ? WHERE transportation_id = ?");
        return $stmt->execute([$status, $transportationId]);
    }
}

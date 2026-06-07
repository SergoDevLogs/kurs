<?php
// src/models/Bill.php

class Bill {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getSalesCount($startDate = null, $endDate = null, $establishmentAdress = null) {
        $sql = "
            SELECT COUNT(DISTINCT b.bill_id) as total
            FROM bill b
            JOIN bill_content bc ON b.bill_id = bc.bill_id
            WHERE 1=1
        ";
        $params = [];
        
        if ($startDate) {
            $sql .= " AND b.bill_timedate >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND b.bill_timedate <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        if ($establishmentAdress) {
            $sql .= " AND b.establishment_adress = ?";
            $params[] = $establishmentAdress;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    public function getSales($startDate = null, $endDate = null, $establishmentAdress = null, $page = 1, $perPage = 10, $sort = 'bill_timedate', $order = 'DESC') {
        $offset = ($page - 1) * $perPage;
        
        $allowedSort = ['bill_timedate', 'total_amount', 'items_count'];
        if (!in_array($sort, $allowedSort)) {
            $sort = 'bill_timedate';
        }
        
        $sql = "
            SELECT b.bill_timedate, b.establishment_adress,
                   b.bill_paytype, b.loyalty_card_number,
                   SUM(bc.bill_summ) as total_amount,
                   COUNT(bc.bill_content_id) as items_count
            FROM bill b
            JOIN bill_content bc ON b.bill_id = bc.bill_id
            WHERE 1=1
        ";
        $params = [];
        
        if ($startDate) {
            $sql .= " AND b.bill_timedate >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND b.bill_timedate <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        if ($establishmentAdress) {
            $sql .= " AND b.establishment_adress = ?";
            $params[] = $establishmentAdress;
        }
        
        $sql .= " GROUP BY b.bill_id, b.bill_timedate, b.establishment_adress, b.bill_paytype, b.loyalty_card_number
                  ORDER BY $sort $order
                  LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSalesAll($startDate = null, $endDate = null, $establishmentAdress = null) {
        $sql = "
            SELECT b.bill_timedate, b.establishment_adress,
                   b.bill_paytype, b.loyalty_card_number,
                   SUM(bc.bill_summ) as total_amount,
                   COUNT(bc.bill_content_id) as items_count
            FROM bill b
            JOIN bill_content bc ON b.bill_id = bc.bill_id
            WHERE 1=1
        ";
        $params = [];
        
        if ($startDate) {
            $sql .= " AND b.bill_timedate >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND b.bill_timedate <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        if ($establishmentAdress) {
            $sql .= " AND b.establishment_adress = ?";
            $params[] = $establishmentAdress;
        }
        
        $sql .= " GROUP BY b.bill_id, b.bill_timedate, b.establishment_adress, b.bill_paytype, b.loyalty_card_number
                  ORDER BY b.bill_timedate DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSalesStats($startDate, $endDate, $establishmentAdress = null) {
        $sql = "
            SELECT DATE(b.bill_timedate) as sale_date,
                   COUNT(DISTINCT b.bill_id) as transactions_count,
                   SUM(bc.bill_summ) as total_amount,
                   SUM(bc.bill_content_count) as items_sold
            FROM bill b
            JOIN bill_content bc ON b.bill_id = bc.bill_id
            WHERE b.bill_timedate >= ? AND b.bill_timedate <= ?
        ";
        $params = [$startDate, $endDate . ' 23:59:59'];
        
        if ($establishmentAdress) {
            $sql .= " AND b.establishment_adress = ?";
            $params[] = $establishmentAdress;
        }
        
        $sql .= " GROUP BY DATE(b.bill_timedate) ORDER BY sale_date";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSalesByEstablishment($startDate, $endDate) {
        $stmt = $this->pdo->prepare("
            SELECT b.establishment_adress,
                   COUNT(DISTINCT b.bill_id) as transactions_count,
                   SUM(bc.bill_summ) as total_revenue,
                   SUM(bc.bill_content_count) as total_items
            FROM bill b
            JOIN bill_content bc ON b.bill_id = bc.bill_id
            WHERE b.bill_timedate >= ? AND b.bill_timedate <= ?
            GROUP BY b.establishment_adress
            ORDER BY total_revenue DESC
        ");
        $stmt->execute([$startDate, $endDate . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
public function getPaymentTypeStats($startDate, $endDate, $establishmentAdress = null) {
    $sql = "
        SELECT b.bill_paytype,
               COUNT(*) as count,
               SUM(bc.bill_summ) as total_amount
        FROM bill b
        JOIN bill_content bc ON b.bill_id = bc.bill_id
        WHERE b.bill_timedate >= ? AND b.bill_timedate <= ?
    ";
    $params = [$startDate, $endDate . ' 23:59:59'];
    
    if ($establishmentAdress) {
        $sql .= " AND b.establishment_adress = ?";
        $params[] = $establishmentAdress;
    }
    
    $sql .= " GROUP BY b.bill_paytype";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $payTypes = ['Наличные', 'Карта', 'Приложение'];
    $formatted = [];
    foreach ($results as $result) {
        $formatted[] = [
            'type' => $payTypes[$result['bill_paytype']] ?? 'Неизвестно',
            'count' => $result['count'],
            'total_amount' => $result['total_amount']
        ];
    }
    return $formatted;
}
}

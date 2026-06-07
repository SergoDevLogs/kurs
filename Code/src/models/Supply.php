<?php
// src/models/Supply.php

class Supply {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAll($startDate = null, $endDate = null, $establishmentAdress = null, $page = 1, $perPage = 10, $sort = 'supply_date_send', $order = 'DESC') {
        $offset = ($page - 1) * $perPage;
        
        $allowedSort = ['supply_date_send', 'total_cost', 'total_quantity', 'supplier_name'];
        if (!in_array($sort, $allowedSort)) {
            $sort = 'supply_date_send';
        }
        
        $sql = "
            SELECT s.supply_id, s.establishment_adress, s.supply_date_send, 
                   s.supply_date_recieved, s.supply_state,
                   sup.supplier_name,
                   SUM(cs.content_supply_num) as total_quantity,
                   SUM(cs.content_supply_cost) as total_cost
            FROM supply s
            JOIN sending_supply ss ON s.supply_id = ss.supply_id
            JOIN supplier sup ON ss.supplier_id = sup.supplier_id
            JOIN content_supply cs ON s.supply_id = cs.supply_id
            WHERE 1=1
        ";
        $params = [];
        
        if ($startDate) {
            $sql .= " AND s.supply_date_send >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND s.supply_date_send <= ?";
            $params[] = $endDate;
        }
        if ($establishmentAdress) {
            $sql .= " AND s.establishment_adress = ?";
            $params[] = $establishmentAdress;
        }
        
        $sql .= " GROUP BY s.supply_id, s.establishment_adress, s.supply_date_send, 
                         s.supply_date_recieved, s.supply_state, sup.supplier_name
                  ORDER BY $sort $order
                  LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCount($startDate = null, $endDate = null, $establishmentAdress = null) {
        $sql = "
            SELECT COUNT(DISTINCT s.supply_id) as total
            FROM supply s
            JOIN sending_supply ss ON s.supply_id = ss.supply_id
            JOIN supplier sup ON ss.supplier_id = sup.supplier_id
            JOIN content_supply cs ON s.supply_id = cs.supply_id
            WHERE 1=1
        ";
        $params = [];
        
        if ($startDate) {
            $sql .= " AND s.supply_date_send >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND s.supply_date_send <= ?";
            $params[] = $endDate;
        }
        if ($establishmentAdress) {
            $sql .= " AND s.establishment_adress = ?";
            $params[] = $establishmentAdress;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    public function updateStatus($supplyId, $status) {
        $stmt = $this->pdo->prepare("UPDATE supply SET supply_state = ? WHERE supply_id = ?");
        return $stmt->execute([$status, $supplyId]);
    }
    
    public function getSuppliers() {
        $stmt = $this->pdo->query("SELECT supplier_id, supplier_name FROM supplier ORDER BY supplier_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function create($establishmentAdress, $supplierId, $items) {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->query("SELECT COALESCE(MAX(supply_id), 0) + 1 FROM supply");
            $supplyId = $stmt->fetchColumn();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO supply (supply_id, establishment_adress, supply_date_send, supply_state)
                VALUES (?, ?, CURRENT_DATE, 1)
            ");
            $stmt->execute([$supplyId, $establishmentAdress]);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO sending_supply (supply_id, supplier_id, sending_supply_count)
                VALUES (?, ?, ?)
            ");
            $totalCount = array_sum(array_column($items, 'quantity'));
            $stmt->execute([$supplyId, $supplierId, $totalCount]);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO content_supply (product_article, supply_id, content_supply_num, content_supply_cost)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($items as $item) {
                $stmt->execute([
                    $item['product_article'],
                    $supplyId,
                    $item['quantity'],
                    $item['cost']
                ]);
            }
            
            $this->pdo->commit();
            return $supplyId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}

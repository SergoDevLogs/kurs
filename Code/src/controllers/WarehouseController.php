<?php
// src/controllers/WarehouseController.php

require_once __DIR__ . '/../models/Stock.php';
require_once __DIR__ . '/../models/Supply.php';

class WarehouseController {
    private $pdo;
    private $stockModel;
    private $supplyModel;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->stockModel = new Stock($pdo);
        $this->supplyModel = new Supply($pdo);
        $this->checkAuth();
    }
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'warehouse') {
            header('Location: /login');
            exit;
        }
    }
    
    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'establishment_adress';
        $order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
        
        $stocks = $this->stockModel->getAllStocksPaginated($page, $perPage, $sort, $order);
        $totalCount = $this->stockModel->getStocksCount();
        $totalPages = ceil($totalCount / $perPage);
        
        if (isset($_GET['ajax'])) {
            ob_start();
            include __DIR__ . '/../views/partials/stocks_table.php';
            $tableHtml = ob_get_clean();
            ob_start();
            include __DIR__ . '/../views/partials/pagination.php';
            $paginationHtml = ob_get_clean();
            header('Content-Type: application/json');
            echo json_encode(['tableHtml' => $tableHtml, 'paginationHtml' => $paginationHtml]);
            exit;
        }
        
        require_once __DIR__ . '/../views/warehouse/index.php';
    }
    
    public function supplies() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'supply_date_send';
        $order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
        
        $supplies = $this->supplyModel->getAll($startDate, $endDate, null, $page, $perPage, $sort, $order);
        $totalCount = $this->supplyModel->getCount($startDate, $endDate);
        $totalPages = ceil($totalCount / $perPage);
        
        if (isset($_GET['ajax'])) {
            ob_start();
            include __DIR__ . '/../views/partials/warehouse_supplies_table.php';
            $tableHtml = ob_get_clean();
            ob_start();
            include __DIR__ . '/../views/partials/pagination.php';
            $paginationHtml = ob_get_clean();
            header('Content-Type: application/json');
            echo json_encode(['tableHtml' => $tableHtml, 'paginationHtml' => $paginationHtml]);
            exit;
        }
        
        require_once __DIR__ . '/../views/warehouse/supplies.php';
    }
    
    public function updateStatus() {
        $supplyId = $_POST['supply_id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        if ($supplyId && $status) {
            $this->supplyModel->updateStatus($supplyId, $status);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }
}

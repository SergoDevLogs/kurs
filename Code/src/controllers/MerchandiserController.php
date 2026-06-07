<?php
// src/controllers/MerchandiserController.php

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Stock.php';
require_once __DIR__ . '/../models/Supply.php';
require_once __DIR__ . '/../models/Transportation.php';
require_once __DIR__ . '/../models/Bill.php';

class MerchandiserController {
    protected $pdo;
    protected $productModel;
    protected $stockModel;
    protected $supplyModel;
    protected $transportationModel;
    protected $billModel;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->productModel = new Product($pdo);
        $this->stockModel = new Stock($pdo);
        $this->supplyModel = new Supply($pdo);
        $this->transportationModel = new Transportation($pdo);
        $this->billModel = new Bill($pdo);
        $this->checkAuth();
    }
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['merchandiser', 'admin'])) {
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
    
    require_once __DIR__ . '/../views/merchandiser/index.php';
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
            include __DIR__ . '/../views/partials/merchandiser_supplies_table.php';
            $tableHtml = ob_get_clean();
            ob_start();
            include __DIR__ . '/../views/partials/pagination.php';
            $paginationHtml = ob_get_clean();
            header('Content-Type: application/json');
            echo json_encode(['tableHtml' => $tableHtml, 'paginationHtml' => $paginationHtml]);
            exit;
        }
        
        require_once __DIR__ . '/../views/merchandiser/supplies.php';
    }
    
    public function updateSupplyStatus() {
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
    
    public function createSupplyForm() {
        $products = $this->productModel->getAll();
        $suppliers = $this->supplyModel->getSuppliers();
        $establishments = $this->getEstablishments();
        
        require_once __DIR__ . '/../views/merchandiser/create_supply.php';
    }
    
    public function createSupply() {
        $establishment = $_POST['establishment'] ?? '';
        $supplierId = $_POST['supplier_id'] ?? '';
        $items = [];
        
        $productArticles = $_POST['product_article'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $costs = $_POST['cost'] ?? [];
        
        for ($i = 0; $i < count($productArticles); $i++) {
            if (!empty($productArticles[$i]) && !empty($quantities[$i])) {
                $items[] = [
                    'product_article' => $productArticles[$i],
                    'quantity' => $quantities[$i],
                    'cost' => $costs[$i] ?? 0
                ];
            }
        }
        
        if ($establishment && $supplierId && count($items) > 0) {
            try {
                $this->supplyModel->create($establishment, $supplierId, $items);
                $_SESSION['success'] = 'Поставка успешно создана';
            } catch (Exception $e) {
                $_SESSION['error'] = 'Ошибка при создании поставки: ' . $e->getMessage();
            }
        }
        
        header('Location: /merchandiser/supplies');
        exit;
    }
    
    public function transportations() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'transportation_date';
        $order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
        
        $transportations = $this->transportationModel->getAll($startDate, $endDate, $page, $perPage, $sort, $order);
        $totalCount = $this->transportationModel->getCount($startDate, $endDate);
        $totalPages = ceil($totalCount / $perPage);
        $products = $this->productModel->getAll();
        $establishments = $this->getEstablishments();
        
        if (isset($_GET['ajax'])) {
            ob_start();
            include __DIR__ . '/../views/partials/transportations_table.php';
            $tableHtml = ob_get_clean();
            ob_start();
            include __DIR__ . '/../views/partials/pagination.php';
            $paginationHtml = ob_get_clean();
            header('Content-Type: application/json');
            echo json_encode(['tableHtml' => $tableHtml, 'paginationHtml' => $paginationHtml]);
            exit;
        }
        
        require_once __DIR__ . '/../views/merchandiser/transportations.php';
    }
    
    public function createTransportation() {
        if ($_SESSION['user_role'] !== 'admin') {
            header('Location: /merchandiser');
            exit;
        }
        
        $productArticle = $_POST['product_article'] ?? '';
        $fromAddress = $_POST['from_address'] ?? '';
        $toAddress = $_POST['to_address'] ?? '';
        $type = $_POST['type'] ?? 1;
        $distance = $_POST['distance'] ?? 0;
        
        if ($productArticle && $fromAddress && $toAddress) {
            $this->transportationModel->create($productArticle, $fromAddress, $toAddress, $type, $distance);
            $_SESSION['success'] = 'Перемещение создано';
        }
        
        header('Location: /merchandiser/transportations');
        exit;
    }
    
    public function efficiencyReport() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $salesByStore = $this->billModel->getSalesByEstablishment($startDate, $endDate);
        $salesStats = $this->billModel->getSalesStats($startDate, $endDate);
        $paymentStats = $this->billModel->getPaymentTypeStats($startDate, $endDate);
        
        require_once __DIR__ . '/../views/merchandiser/efficiency_report.php';
    }
    
    public function exportEfficiencyExcel() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $salesByStore = $this->billModel->getSalesByEstablishment($startDate, $endDate);
        
        $vendorAutoload = __DIR__ . '/../../vendor/autoload.php';
        if (!file_exists($vendorAutoload)) {
            $this->exportEfficiencyCsv();
            return;
        }
        
        require_once $vendorAutoload;
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Эффективность магазинов');
        
        $sheet->setCellValue('A1', 'Магазин');
        $sheet->setCellValue('B1', 'Кол-во транзакций');
        $sheet->setCellValue('C1', 'Выручка (₽)');
        $sheet->setCellValue('D1', 'Продано товаров');
        $sheet->setCellValue('E1', 'Средний чек (₽)');
        
        $row = 2;
        foreach ($salesByStore as $store) {
            $sheet->setCellValue('A' . $row, $store['establishment_adress']);
            $sheet->setCellValue('B' . $row, $store['transactions_count']);
            $sheet->setCellValue('C' . $row, $store['total_revenue']);
            $sheet->setCellValue('D' . $row, $store['total_items']);
            $sheet->setCellValue('E' . $row, $store['total_revenue'] / $store['transactions_count']);
            $row++;
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="efficiency_' . date('Y-m-d') . '.xlsx"');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    public function exportEfficiencyCsv() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $salesByStore = $this->billModel->getSalesByEstablishment($startDate, $endDate);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="efficiency_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['Магазин', 'Кол-во транзакций', 'Выручка (₽)', 'Продано товаров', 'Средний чек (₽)']);
        
        foreach ($salesByStore as $store) {
            fputcsv($output, [
                $store['establishment_adress'],
                $store['transactions_count'],
                $store['total_revenue'],
                $store['total_items'],
                $store['total_revenue'] / $store['transactions_count']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    protected function getEstablishments() {
        $stmt = $this->pdo->query("SELECT establishment_adress FROM establishment ORDER BY establishment_adress");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

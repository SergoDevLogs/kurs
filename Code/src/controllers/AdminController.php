<?php
// src/controllers/AdminController.php

require_once __DIR__ . '/../controllers/MerchandiserController.php';
require_once __DIR__ . '/../models/Bill.php';
require_once __DIR__ . '/../models/Stock.php';
require_once __DIR__ . '/../models/Supply.php';
require_once __DIR__ . '/../models/Transportation.php';

class AdminController extends MerchandiserController {
    protected $billModel;
    
    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->billModel = new Bill($pdo);
    }
    
    public function dashboard() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $salesStats = $this->billModel->getSalesStats($startDate, $endDate);
        $salesByStore = $this->billModel->getSalesByEstablishment($startDate, $endDate);
        $paymentStats = $this->billModel->getPaymentTypeStats($startDate, $endDate);
        
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
    
    public function stocks() {
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
        
        require_once __DIR__ . '/../views/admin/stocks.php';
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
            include __DIR__ . '/../views/partials/admin_supplies_table.php';
            $tableHtml = ob_get_clean();
            ob_start();
            include __DIR__ . '/../views/partials/pagination.php';
            $paginationHtml = ob_get_clean();
            header('Content-Type: application/json');
            echo json_encode(['tableHtml' => $tableHtml, 'paginationHtml' => $paginationHtml]);
            exit;
        }
        
        require_once __DIR__ . '/../views/admin/supplies.php';
    }
    
    public function exportSuppliesExcel() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $supplies = $this->supplyModel->getAll($startDate, $endDate, null, 1, 999999, 'supply_date_send', 'DESC');
        
        $vendorAutoload = __DIR__ . '/../../vendor/autoload.php';
        if (!file_exists($vendorAutoload)) {
            $this->exportSuppliesCsv();
            return;
        }
        
        require_once $vendorAutoload;
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Поставки');
        
        $sheet->setCellValue('A1', 'Дата отправки');
        $sheet->setCellValue('B1', 'Магазин');
        $sheet->setCellValue('C1', 'Поставщик');
        $sheet->setCellValue('D1', 'Кол-во товаров');
        $sheet->setCellValue('E1', 'Сумма (₽)');
        $sheet->setCellValue('F1', 'Статус');
        
        $row = 2;
        foreach ($supplies as $supply) {
            $statuses = ['Неизвестно', 'Отправлена', 'Доставлена'];
            $sheet->setCellValue('A' . $row, date('d.m.Y', strtotime($supply['supply_date_send'])));
            $sheet->setCellValue('B' . $row, $supply['establishment_adress']);
            $sheet->setCellValue('C' . $row, $supply['supplier_name']);
            $sheet->setCellValue('D' . $row, $supply['total_quantity']);
            $sheet->setCellValue('E' . $row, $supply['total_cost']);
            $sheet->setCellValue('F' . $row, $statuses[$supply['supply_state']] ?? 'Неизвестно');
            $row++;
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="supplies_' . date('Y-m-d') . '.xlsx"');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    public function exportSuppliesCsv() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $supplies = $this->supplyModel->getAll($startDate, $endDate, null, 1, 999999, 'supply_date_send', 'DESC');
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="supplies_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['Дата отправки', 'Магазин', 'Поставщик', 'Кол-во товаров', 'Сумма (₽)', 'Статус']);
        
        foreach ($supplies as $supply) {
            $statuses = ['Неизвестно', 'Отправлена', 'Доставлена'];
            fputcsv($output, [
                date('d.m.Y', strtotime($supply['supply_date_send'])),
                $supply['establishment_adress'],
                $supply['supplier_name'],
                $supply['total_quantity'],
                $supply['total_cost'],
                $statuses[$supply['supply_state']] ?? 'Неизвестно'
            ]);
        }
        
        fclose($output);
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
            include __DIR__ . '/../views/partials/admin_transportations_table.php';
            $tableHtml = ob_get_clean();
            ob_start();
            include __DIR__ . '/../views/partials/pagination.php';
            $paginationHtml = ob_get_clean();
            header('Content-Type: application/json');
            echo json_encode(['tableHtml' => $tableHtml, 'paginationHtml' => $paginationHtml]);
            exit;
        }
        
        require_once __DIR__ . '/../views/admin/transportations.php';
    }
    
    public function exportTransportationsExcel() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $transportations = $this->transportationModel->getAll($startDate, $endDate, 1, 999999, 'transportation_date', 'DESC');
        
        $vendorAutoload = __DIR__ . '/../../vendor/autoload.php';
        if (!file_exists($vendorAutoload)) {
            $this->exportTransportationsCsv();
            return;
        }
        
        require_once $vendorAutoload;
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Перемещения');
        
        $sheet->setCellValue('A1', 'Дата');
        $sheet->setCellValue('B1', 'Товар');
        $sheet->setCellValue('C1', 'Откуда');
        $sheet->setCellValue('D1', 'Куда');
        $sheet->setCellValue('E1', 'Тип');
        $sheet->setCellValue('F1', 'Расстояние (км)');
        $sheet->setCellValue('G1', 'Статус');
        
        $row = 2;
        foreach ($transportations as $trans) {
            $statuses = ['Неизвестно', 'В пути', 'Доставлено'];
            $types = ['Внешняя', 'Внутренняя'];
            $sheet->setCellValue('A' . $row, date('d.m.Y', strtotime($trans['transportation_date'])));
            $sheet->setCellValue('B' . $row, $trans['product_name']);
            $sheet->setCellValue('C' . $row, $trans['establishment_adress_from']);
            $sheet->setCellValue('D' . $row, $trans['establishment_adress_to']);
            $sheet->setCellValue('E' . $row, $types[$trans['transportation_type']] ?? 'Неизвестно');
            $sheet->setCellValue('F' . $row, $trans['transportation_distance']);
            $sheet->setCellValue('G' . $row, $statuses[$trans['transportation_status']] ?? 'Неизвестно');
            $row++;
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="transportations_' . date('Y-m-d') . '.xlsx"');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    public function exportTransportationsCsv() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $transportations = $this->transportationModel->getAll($startDate, $endDate, 1, 999999, 'transportation_date', 'DESC');
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="transportations_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['Дата', 'Товар', 'Откуда', 'Куда', 'Тип', 'Расстояние (км)', 'Статус']);
        
        foreach ($transportations as $trans) {
            $statuses = ['Неизвестно', 'В пути', 'Доставлено'];
            $types = ['Внешняя', 'Внутренняя'];
            fputcsv($output, [
                date('d.m.Y', strtotime($trans['transportation_date'])),
                $trans['product_name'],
                $trans['establishment_adress_from'],
                $trans['establishment_adress_to'],
                $types[$trans['transportation_type']] ?? 'Неизвестно',
                $trans['transportation_distance'],
                $statuses[$trans['transportation_status']] ?? 'Неизвестно'
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    public function efficiencyReport() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $salesByStore = $this->billModel->getSalesByEstablishment($startDate, $endDate);
        $salesStats = $this->billModel->getSalesStats($startDate, $endDate);
        $paymentStats = $this->billModel->getPaymentTypeStats($startDate, $endDate);
        
        require_once __DIR__ . '/../views/admin/efficiency_report.php';
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
}

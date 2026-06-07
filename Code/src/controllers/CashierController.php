<?php
// src/controllers/CashierController.php

require_once __DIR__ . '/../models/Stock.php';
require_once __DIR__ . '/../models/Bill.php';

class CashierController {
    private $pdo;
    private $stockModel;
    private $billModel;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->stockModel = new Stock($pdo);
        $this->billModel = new Bill($pdo);
        $this->checkAuth();
    }
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'cashier') {
            header('Location: /login');
            exit;
        }
    }
    
    public function index() {
        $establishment = $_SESSION['user_establishment'];
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'product_name';
        $order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
        
        $stocks = $this->stockModel->getByEstablishmentPaginated($establishment, $page, $perPage, $sort, $order);
        $totalCount = $this->stockModel->getByEstablishmentCount($establishment);
        $totalPages = ceil($totalCount / $perPage);
        
        if (isset($_GET['ajax'])) {
            ob_start();
            include __DIR__ . '/../views/partials/cashier_stocks_table.php';
            $tableHtml = ob_get_clean();
            ob_start();
            include __DIR__ . '/../views/partials/pagination.php';
            $paginationHtml = ob_get_clean();
            header('Content-Type: application/json');
            echo json_encode(['tableHtml' => $tableHtml, 'paginationHtml' => $paginationHtml]);
            exit;
        }
        
        require_once __DIR__ . '/../views/cashier/index.php';
    }
    
    public function salesReport() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'bill_timedate';
        $order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
        
        $sales = $this->billModel->getSales($startDate, $endDate, $_SESSION['user_establishment'], $page, $perPage, $sort, $order);
        $stats = $this->billModel->getSalesStats($startDate, $endDate, $_SESSION['user_establishment']);
        $paymentStats = $this->billModel->getPaymentTypeStats($startDate, $endDate, $_SESSION['user_establishment']);
        $totalCount = $this->billModel->getSalesCount($startDate, $endDate, $_SESSION['user_establishment']);
        $totalPages = ceil($totalCount / $perPage);
        
        if (isset($_GET['ajax'])) {
            ob_start();
            include __DIR__ . '/../views/partials/cashier_sales_table.php';
            $tableHtml = ob_get_clean();
            ob_start();
            include __DIR__ . '/../views/partials/pagination.php';
            $paginationHtml = ob_get_clean();
            header('Content-Type: application/json');
            echo json_encode(['tableHtml' => $tableHtml, 'paginationHtml' => $paginationHtml, 'stats' => $stats, 'paymentStats' => $paymentStats]);
            exit;
        }
        
        require_once __DIR__ . '/../views/cashier/sales.php';
    }
    
    public function exportSalesExcel() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $sales = $this->billModel->getSalesAll($startDate, $endDate, $_SESSION['user_establishment']);
        
        if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            $this->exportSalesCsv();
            return;
        }
        
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Продажи');
        
        $sheet->setCellValue('A1', 'Дата');
        $sheet->setCellValue('B1', 'Магазин');
        $sheet->setCellValue('C1', 'Сумма (₽)');
        $sheet->setCellValue('D1', 'Кол-во товаров');
        $sheet->setCellValue('E1', 'Тип оплаты');
        $sheet->setCellValue('F1', 'Карта лояльности');
        
        $row = 2;
        foreach ($sales as $sale) {
            $payType = ['Наличные', 'Карта', 'Приложение'][$sale['bill_paytype']] ?? 'Неизвестно';
            $sheet->setCellValue('A' . $row, date('d.m.Y H:i', strtotime($sale['bill_timedate'])));
            $sheet->setCellValue('B' . $row, $sale['establishment_adress']);
            $sheet->setCellValue('C' . $row, $sale['total_amount']);
            $sheet->setCellValue('D' . $row, $sale['items_count']);
            $sheet->setCellValue('E' . $row, $payType);
            $sheet->setCellValue('F' . $row, $sale['loyalty_card_number'] ?? 'Нет');
            $row++;
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="sales_' . date('Y-m-d') . '.xlsx"');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    public function exportSalesCsv() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $sales = $this->billModel->getSalesAll($startDate, $endDate, $_SESSION['user_establishment']);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="sales_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['Дата', 'Магазин', 'Сумма (₽)', 'Кол-во товаров', 'Тип оплаты', 'Карта лояльности']);
        
        foreach ($sales as $sale) {
            $payType = ['Наличные', 'Карта', 'Приложение'][$sale['bill_paytype']] ?? 'Неизвестно';
            fputcsv($output, [
                date('d.m.Y H:i', strtotime($sale['bill_timedate'])),
                $sale['establishment_adress'],
                $sale['total_amount'],
                $sale['items_count'],
                $payType,
                $sale['loyalty_card_number'] ?? 'Нет'
            ]);
        }
        
        fclose($output);
        exit;
    }
}

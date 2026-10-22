<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\ImportUploadController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\ExcelExportController;
use App\Models\Invoice;
use App\Http\Controllers\AiChatController;

Route::middleware('auth')->group(function () {

Route::post('/ai/chat', [AiChatController::class, 'chat'])->middleware(['auth'])->name('ai.chat');
    // فواتير
    Route::get('/invoices/{invoice}/pdf', [InvoicePdfController::class, 'download'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/preview', [InvoicePdfController::class, 'preview'])->name('invoices.preview');
    Route::get('/invoices/{invoice}/print', function(Invoice $invoice) {
        return view('invoice-print', ['invoice' => $invoice->load('items', 'customer', 'supplier')]);
    })->name('invoice.print');

    // كاشير
    Route::get('/receipt/{invoice}', [ReceiptController::class, 'show'])->name('receipt.pos');

    // باركود
    Route::get('/barcode/labels', [BarcodeController::class, 'print'])->name('barcode.print');

    // Import
    Route::post('/admin/import-products/upload', [ImportUploadController::class, 'upload'])->name('import.products.upload');

    // مرفقات
    Route::post('/attachments/upload', [AttachmentController::class, 'upload'])->name('attachments.upload');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'delete'])->name('attachments.delete');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');

    // أقساط
    Route::post('/invoices/{invoice}/installments/generate', [InstallmentController::class, 'generate'])->name('installments.generate');
    Route::post('/installments/{installment}/pay', [InstallmentController::class, 'pay'])->name('installments.pay');
    Route::delete('/installments/{installment}', [InstallmentController::class, 'destroy'])->name('installments.destroy');

    // تصدير التقارير Excel — احترافي
    Route::get('/reports/export/{type}', [ExcelExportController::class, 'export'])->name('reports.export');

});

Route::get('/', function () {
    return view('welcome');
});
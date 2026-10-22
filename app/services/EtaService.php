<?php

namespace App\Services;

use App\Models\Invoice;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class EtaService
{
    protected Client $client;
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected ?string $accessToken = null;

    public function __construct()
    {
        $this->baseUrl      = config('eta.sandbox') ? 'https://api.preprod.invoicing.eta.gov.eg' : 'https://api.invoicing.eta.gov.eg';
        $this->clientId     = config('eta.client_id', '');
        $this->clientSecret = config('eta.client_secret', '');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 30,
            'headers'  => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        ]);
    }

    public function getAccessToken(): ?string
    {
        try {
            $response = $this->client->post('/connect/token', [
                'form_params' => [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);
            $data = json_decode($response->getBody(), true);
            $this->accessToken = $data['access_token'] ?? null;
            return $this->accessToken;
        } catch (RequestException $e) {
            Log::error('ETA Token Error: ' . $e->getMessage());
            return null;
        }
    }

    public function submitInvoice(Invoice $invoice): array
    {
        if (!$this->accessToken) {
            $this->getAccessToken();
        }

        if (!$this->accessToken) {
            return ['success' => false, 'error' => 'فشل الحصول على التوكن من منظومة الضرائب'];
        }

        $document = $this->buildDocument($invoice);

        try {
            $response = $this->client->post('/api/v1/documentsubmissions', [
                'headers' => ['Authorization' => 'Bearer ' . $this->accessToken],
                'json'    => ['documents' => [$document]],
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['acceptedDocuments'][0])) {
                $accepted = $data['acceptedDocuments'][0];
                return [
                    'success'    => true,
                    'uuid'       => $accepted['uuid'] ?? null,
                    'long_id'    => $accepted['longId'] ?? null,
                    'hash_key'   => $accepted['hashKey'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error'   => $data['rejectedDocuments'][0]['error']['details'][0]['message'] ?? 'خطأ غير معروف',
            ];
        } catch (RequestException $e) {
            Log::error('ETA Submit Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getInvoiceStatus(string $uuid): array
    {
        if (!$this->accessToken) $this->getAccessToken();

        try {
            $response = $this->client->get("/api/v1/documents/{$uuid}/details", [
                'headers' => ['Authorization' => 'Bearer ' . $this->accessToken],
            ]);
            return ['success' => true, 'data' => json_decode($response->getBody(), true)];
        } catch (RequestException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function buildDocument(Invoice $invoice): array
    {
        $items = [];
        foreach ($invoice->items as $item) {
            $items[] = [
                'description'           => $item->product_name,
                'itemType'              => 'GS1',
                'itemCode'              => $item->product_code ?? $item->product_name,
                'unitType'              => 'EA',
                'quantity'              => (float) $item->quantity,
                'unitValue'             => [
                    'currencySold'      => 'SAR',
                    'amountSAR'         => (float) $item->unit_price,
                    'amountSold'        => (float) $item->unit_price,
                    'currencyExchangeRate' => 1,
                ],
                'salesTotal'            => (float) $item->total,
                'discount'              => ['rate' => (float) $item->discount_percent, 'amount' => (float) $item->discount_amount],
                'taxableItems'          => [],
                'total'                 => (float) $item->total,
                'valueDifference'       => 0,
                'totalTaxableFees'      => 0,
                'netTotal'              => (float) $item->total,
                'itemsDiscount'         => 0,
            ];
        }

        return [
            'issuer' => [
                'address' => [
                    'branchID'    => '0',
                    'country'     => 'EG',
                    'governate'   => config('eta.governate', 'Cairo'),
                    'regionCity'  => config('eta.city', 'Cairo'),
                    'street'      => config('eta.street', 'Main Street'),
                    'buildingNumber' => config('eta.building', '1'),
                ],
                'type'       => 'B',
                'id'         => config('eta.issuer_id', ''),
                'name'       => config('app.name', 'Orion ERP'),
            ],
            'receiver' => [
                'address' => ['country' => 'EG'],
                'type'    => $invoice->customer?->tax_number ? 'B' : 'P',
                'id'      => $invoice->customer?->tax_number ?? '0000',
                'name'    => $invoice->customer?->name ?? 'عميل نقدي',
            ],
            'documentType'    => 'I',
            'documentTypeVersion' => '1.0',
            'dateTimeIssued'  => $invoice->invoice_date->format('Y-m-d') . 'T00:00:00Z',
            'taxpayerActivityCode' => config('eta.activity_code', '4711'),
            'internalID'      => $invoice->invoice_number,
            'invoiceLines'    => $items,
            'totalDiscountAmount'  => (float) $invoice->discount_amount,
            'totalSalesAmount'     => (float) $invoice->subtotal,
            'netAmount'            => (float) ($invoice->subtotal - $invoice->discount_amount),
            'taxTotals'            => [],
            'totalAmount'          => (float) $invoice->total,
            'extraDiscountAmount'  => 0,
            'totalItemsDiscountAmount' => 0,
        ];
    }
}
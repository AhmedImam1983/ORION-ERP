<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected Client $client;
    protected string $provider;

    public function __construct()
    {
        $this->client   = new Client(['timeout' => 30]);
        $this->provider = config('whatsapp.provider', 'callmebot');
    }

    /**
     * إرسال رسالة واتساب
     */
    public function send(string $phone, string $message): array
    {
        $phone = $this->formatPhone($phone);

        return match($this->provider) {
            'ultramsg'  => $this->sendUltraMsg($phone, $message),
            'twilio'    => $this->sendTwilio($phone, $message),
            default     => $this->sendCallMeBot($phone, $message),
        };
    }

    /**
     * CallMeBot — مجاني للتجربة
     * التسجيل: ابعت "I allow callmebot to send me messages" لـ +34 644 80 03 61
     */
    protected function sendCallMeBot(string $phone, string $message): array
    {
        $apiKey = config('whatsapp.callmebot_key', '');
        if (!$apiKey) {
            return ['success' => false, 'error' => 'CallMeBot API Key غير موجود'];
        }

        try {
            $url = 'https://api.callmebot.com/whatsapp.php?' . http_build_query([
                'phone'  => $phone,
                'text'   => $message,
                'apikey' => $apiKey,
            ]);

            $response = $this->client->get($url);
            $body     = (string) $response->getBody();

            if (str_contains($body, 'Message queued')) {
                return ['success' => true, 'message' => 'تم إرسال الرسالة بنجاح'];
            }

            return ['success' => false, 'error' => $body];
        } catch (\Exception $e) {
            Log::error('WhatsApp CallMeBot Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * UltraMsg — مدفوع وموثوق
     * التسجيل: https://ultramsg.com
     */
    protected function sendUltraMsg(string $phone, string $message): array
    {
        $instanceId = config('whatsapp.ultramsg_instance', '');
        $token      = config('whatsapp.ultramsg_token', '');

        if (!$instanceId || !$token) {
            return ['success' => false, 'error' => 'UltraMsg credentials غير موجودة'];
        }

        try {
            $response = $this->client->post("https://api.ultramsg.com/{$instanceId}/messages/chat", [
                'form_params' => [
                    'token'  => $token,
                    'to'     => $phone,
                    'body'   => $message,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            if ($data['sent'] === 'true') {
                return ['success' => true, 'id' => $data['id']];
            }

            return ['success' => false, 'error' => $data['error'] ?? 'خطأ غير معروف'];
        } catch (\Exception $e) {
            Log::error('WhatsApp UltraMsg Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Twilio — رسمي ومعتمد
     */
    protected function sendTwilio(string $phone, string $message): array
    {
        $sid   = config('whatsapp.twilio_sid', '');
        $token = config('whatsapp.twilio_token', '');
        $from  = config('whatsapp.twilio_from', '');

        if (!$sid || !$token) {
            return ['success' => false, 'error' => 'Twilio credentials غير موجودة'];
        }

        try {
            $response = $this->client->post(
                "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json",
                [
                    'auth'        => [$sid, $token],
                    'form_params' => [
                        'From' => 'whatsapp:' . $from,
                        'To'   => 'whatsapp:' . $phone,
                        'Body' => $message,
                    ],
                ]
            );

            $data = json_decode($response->getBody(), true);
            return ['success' => true, 'sid' => $data['sid']];
        } catch (\Exception $e) {
            Log::error('WhatsApp Twilio Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * رسالة تأكيد فاتورة
     */
    public function sendInvoiceConfirmation(\App\Models\Invoice $invoice): array
    {
        $phone = $invoice->customer?->phone;
        if (!$phone) return ['success' => false, 'error' => 'لا يوجد رقم هاتف للعميل'];

        $message = "🧾 *Orion ERP*\n\n";
        $message .= "مرحباً {$invoice->customer->name}،\n\n";
        $message .= "تم إصدار فاتورة جديدة بنجاح:\n\n";
        $message .= "📋 رقم الفاتورة: *{$invoice->invoice_number}*\n";
        $message .= "📅 التاريخ: {$invoice->invoice_date->format('d/m/Y')}\n";
        $message .= "💰 الإجمالي: *" . number_format($invoice->total, 2) . " ر.س*\n";
        $message .= "💵 المدفوع: " . number_format($invoice->paid_amount, 2) . " ر.س\n";

        if ($invoice->remaining_amount > 0) {
            $message .= "⏳ المتبقي: *" . number_format($invoice->remaining_amount, 2) . " ر.س*\n";
        } else {
            $message .= "✅ *تم السداد كاملاً*\n";
        }

        $message .= "\nشكراً لتعاملكم معنا 🙏";

        return $this->send($phone, $message);
    }

    /**
     * رسالة تذكير بالدفع
     */
    public function sendPaymentReminder(\App\Models\Customer $customer, float $amount): array
    {
        $phone = $customer->phone;
        if (!$phone) return ['success' => false, 'error' => 'لا يوجد رقم هاتف'];

        $message = "⏰ *تذكير بالدفع*\n\n";
        $message .= "مرحباً {$customer->name}،\n\n";
        $message .= "نود تذكيركم بوجود مديونية بقيمة:\n";
        $message .= "💰 *" . number_format($amount, 2) . " ر.س*\n\n";
        $message .= "نرجو سداد المبلغ في أقرب وقت.\n";
        $message .= "للاستفسار تواصل معنا 📞\n\n";
        $message .= "شكراً — Orion ERP";

        return $this->send($phone, $message);
    }

    /**
     * رسالة ترحيب بعميل جديد
     */
    public function sendWelcome(\App\Models\Customer $customer): array
    {
        $phone = $customer->phone;
        if (!$phone) return ['success' => false, 'error' => 'لا يوجد رقم هاتف'];

        $message = "👋 *أهلاً وسهلاً*\n\n";
        $message .= "مرحباً {$customer->name}،\n\n";
        $message .= "يسعدنا انضمامكم كعميل جديد!\n";
        $message .= "نحن هنا لخدمتكم دائماً 😊\n\n";
        $message .= "— فريق Orion ERP";

        return $this->send($phone, $message);
    }

    /**
     * تنسيق رقم الهاتف
     */
    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '+2' . $phone;
        } elseif (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        return $phone;
    }
}
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Provider
    |--------------------------------------------------------------------------
    | callmebot  — مجاني للتجربة (شخصي فقط)
    | ultramsg   — مدفوع وموثوق للأعمال
    | twilio     — رسمي ومعتمد من Meta
    */
    'provider' => env('WHATSAPP_PROVIDER', 'callmebot'),

    // CallMeBot (مجاني)
    // للتفعيل: ابعت "I allow callmebot to send me messages" على واتساب لـ +34 644 80 03 61
    'callmebot_key' => env('WHATSAPP_CALLMEBOT_KEY', ''),

    // UltraMsg (مدفوع)
    // التسجيل: https://ultramsg.com
    'ultramsg_instance' => env('WHATSAPP_ULTRAMSG_INSTANCE', ''),
    'ultramsg_token'    => env('WHATSAPP_ULTRAMSG_TOKEN', ''),

    // Twilio (رسمي)
    // التسجيل: https://twilio.com
    'twilio_sid'   => env('TWILIO_ACCOUNT_SID', ''),
    'twilio_token' => env('TWILIO_AUTH_TOKEN', ''),
    'twilio_from'  => env('TWILIO_WHATSAPP_FROM', ''),
];
<?php

return [

    'whatsapp' => [
        'gateway_url' => env('WHATSAPP_GATEWAY_URL', env('WHATSAPP_URL', 'https://e4protech-whatsapp-gateway.onrender.com/api/whatsapp/send')),
        'secret' => env('WHATSAPP_SECRET', env('WHATSAPP_API_SECRET', 'E4ProTech_Secret_Key_2026_Secure')),
    ],

];

<?php

return [

    'whatsapp' => [
        'gateway_url' => env('WHATSAPP_GATEWAY_URL', 'https://whatsapp-gateway-k6vv.onrender.com/api/whatsapp/send'),
        'secret' => env('WHATSAPP_API_SECRET', 'E4ProTech_Secret_Key_2026_Secure'),
    ],

];

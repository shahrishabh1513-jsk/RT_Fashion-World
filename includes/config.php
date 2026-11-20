<?php
/* =====================================================================
   RT FASHION HUB – configuration  (XAMPP defaults; edit if yours differ)
   ===================================================================== */
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'rt_fashion',      // your database name
        'user' => 'root',            // XAMPP default
        'pass' => '',                // XAMPP default is an empty password
        'charset' => 'utf8mb4',
    ],
    'site' => [
        'brand'     => 'RT FASHION HUB',
        'tagline'   => 'Style That Defines You.',
        'email'     => 'contact@rtfashionhub.com',
        'phone'     => '+91 78619 76128',
        'phone_href'=> '+917861976128',
        'location'  => 'Vadodara, Gujarat, India',
        'address'   => 'Opp. Sumandeep College, Near Suzlon Company, Vadodara, Gujarat 391760, India',
        'hours'     => 'Monday to Saturday: 10:00 AM – 6:00 PM',
        'github_url'=> 'https://github.com/shahrishabh1513-jsk/RT_Fashion-World',
        'social'    => ['instagram' => '', 'facebook' => '', 'whatsapp' => 'https://wa.me/917861976128'],
        'currency'  => '₹',
        'gst_rate'  => 0.05,         // 5% GST, shown separately at checkout
        'shipping_fee' => 0,         // flat delivery charge in INR
        'gstin'     => '',           // add your GSTIN to print it on bills
        'bill_terms'=> 'Goods once sold can be returned as per the store return policy. Please keep this bill for returns and support.',
    ],
    /* true = show the password-reset link on screen (XAMPP has no mail server). Set false in production. */
    'debug'    => true,
    'timezone' => 'Asia/Kolkata',
];

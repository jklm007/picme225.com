<?php
$ch = curl_init('http://109.199.123.69:8080/chat/whatsappNumbers/picme_whatsapp');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: 6BE2FB65-6676-48FB-BE16-162621590D6A', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['numbers' => ['22577436121', '225077436121', '2250714950219', '225714950219']]));
echo curl_exec($ch);
curl_close($ch);

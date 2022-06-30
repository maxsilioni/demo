<?php
define('BOT_TOKEN', '');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

$post = array('url' => '');
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, API_URL."setWebhook");
curl_setopt($ch, CURLOPT_POST,1);

curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
$result=curl_exec ($ch);
curl_close ($ch);
echo "<pre>";
print_r($result);
print_r($post);
echo "</pre>";
?>

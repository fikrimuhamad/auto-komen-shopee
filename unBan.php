<?php
date_default_timezone_set('Asia/Jakarta');

// Fungsi untuk membaca cookies dari file
function readCookiesFromFile($filePath)
{
    try {
        $cookies = file_get_contents($filePath);
        return trim($cookies);
    } catch (Exception $error) {
        error_log('Error reading cookies from file: ' . $error->getMessage());
        return null;
    }
}


$cookiesFilePath = 'cookie.txt';
$cookies = readCookiesFromFile($cookiesFilePath);

if (!$cookies) {
    error_log('Cookies not available. Please check your cookies.txt file.');
    exit(1);
}
function getSessionId()
{
    global $cookies, $sessionId;

    $sessionUrl = 'https://live.shopee.co.id/webapi/v1/session';

    $options = [
        'http' => [
            'header' => 'Cookie: ' . $cookies,
        ],
    ];

    $context = stream_context_create($options);

    $response = file_get_contents($sessionUrl, false, $context);

    if ($response) {
        $sessionData = json_decode($response, true);

        if ($sessionData && $sessionData['err_code'] === 0 && $sessionData['data'] && $sessionData['data']['session']) {
            $sessionId = $sessionData['data']['session']['session_id'];
        } else {
            echo 'Error getting session ID: ' . $sessionData['err_msg'] . PHP_EOL;
        }
    } else {
        echo 'Error getting session ID.' . PHP_EOL;
    }
}
unBanLagi:
getSessionId();
$uid =  pilihan("ID\n");
$banUrl = 'https://live.shopee.co.id/webapi/v1/session/' . $sessionId . '/comment/ban';

$postData = [
    'is_ban' => false,
    'ban_uid' => $uid,
];

$options = [
    'http' => [
        'header' => [
            'Cookie: ' . $cookies,
            'Content-Type: application/json',
        ],
        'method' => 'POST',
        'content' => json_encode($postData),
    ],
];

$context = stream_context_create($options);

$response = file_get_contents($banUrl, false, $context);

if ($response) {
    $responseData = json_decode($response, true);
    $messages = $responseData['err_msg'];
    echo strtoupper($messages) . ' UNBANNED CHAT USER UID: ' . $uid . PHP_EOL . PHP_EOL;
} else {
    echo 'Error banning user.' . PHP_EOL;
}
goto unBanLagi;
// Fungsi untuk memilih
function text($text)
{
    echo $text . " => : ";
    $select = trim(fgets(STDIN));
    return $select;
}

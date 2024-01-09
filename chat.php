<?php
date_default_timezone_set('Asia/Jakarta');

// Read cookie from file
$cookieFile = './cookie.txt';
$cookieSHOPEE = @file_get_contents($cookieFile);
if (empty($cookieSHOPEE)) {
    echo 'COOKIE TIDAK ADA / TIDAK DITEMUKAN PADA FILE cookie.txt';
    exit;
} else {
    echo "COOKIE DITEMUKAN...\n";
}
// Contoh penggunaan
$client = new ShopeeApiClient();
$sessionID = $client->getSession();
$client->setSessionID($sessionID);
echo "SESSION DITEMUKAN ID: " . $client->getSessionID() . "\n\n";


define('SHOPEE_API_URL', 'SHOPEE LIVE URL PASTE DISINI / https://chatroom-live.shopee.co.id/api/v1/fetch/chatroom/');
define('SLEEP_TIME', 3);


$processedMessages = [];

function makeCurlRequest($url, $headers)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Execute cURL request
    $result = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        // Handle cURL error (log to file, print a message, etc.)
        echo 'Error: ' . curl_error($ch);
    }

    // Check for HTTP response code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode >= 400) {
        // Handle HTTP error (log to file, print a message, etc.)
        echo 'HTTP Error: ' . $httpCode;
    }

    // Close cURL resource
    curl_close($ch);

    return $result;
}

while (true) {
    // Make cURL request
    $headers = [
        'authority: chatroom-live.shopee.co.id',
        'accept: application/json, text/plain, */*',
        'accept-language: id-ID,id;q=0.7',
        'client-info: platform=9',
        'cookie: ' . $cookieSHOPEE,
        'origin: https://live.shopee.co.id',
        'referer: https://live.shopee.co.id/',
        'sec-ch-ua: "Not_A Brand";v="8", "Chromium";v="120", "Brave";v="120"',
        'sec-ch-ua-mobile: ?0',
        'sec-ch-ua-platform: "Windows"',
        'sec-fetch-dest: empty',
        'sec-fetch-mode: cors',
        'sec-fetch-site: same-site',
        'sec-gpc: 1',
        'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    ];

    $result = makeCurlRequest(SHOPEE_API_URL, $headers);

    // Extract data from JSON response
    $responseJson = json_decode($result, true);

    // Check if data is available
    if (isset($responseJson['data']['message'][0]['msgs'])) {
        $messages = $responseJson['data']['message'][0]['msgs'];
        $timestamp = $responseJson['data']['timestamp'];

        // Extract id, display_name, and content
        $extractedData = array_map(function ($message) {
            return [
                'id' => $message['id'],
                'nickname' => $message['nickname'],
                'display_name' => $message['display_name'],
                'content' => json_decode($message['content'], true)['content'],
            ];
        }, $messages);

        // Filter out already processed messages
        $newMessages = array_filter($extractedData, function ($message) use ($processedMessages) {
            return !in_array($message['content'], $processedMessages);
        });

        // Print extracted data for new messages
        if (!empty($newMessages)) {
            $date = date('d/m H:i:s', $timestamp);
            echo "===| NEW MESSAGE |==\n";
            foreach ($newMessages as $message) {
                echo "TIME: " . $date . "\n";
                echo "NAMA: " . $message['display_name'] . "\n";
                echo "MESSAGE: " . $message['content'] . "\n\n";
                // Add the processed message to the array
                $processedMessages[] = $message['content'];
            }
        }
    }

    sleep(SLEEP_TIME); // Sleep for 3 seconds before the next request
}

class ShopeeApiClient
{
    private $ShopeeLive;
    private $session;

    public function __construct()
    {
        $this->ShopeeLive = $this->createShopeeLive();
        $this->session = "";
    }

    public function getSessionID()
    {
        return $this->session;
    }

    public function setSessionID($session)
    {
        $this->session = $session;
    }

    public function getSession()
    {
        $cookie = file_get_contents("./cookie.txt");

        $shopeeCreator = $this->createShopeeLiveForCreator($cookie);

        try {
            $response = file_get_contents("https://creator.shopee.co.id/supply/api/lm/sellercenter/realtime/sessionList?page=1&pageSize=10&name=", false, $shopeeCreator);
            $data = json_decode($response, true);
            return $data['data']['list'][0]['sessionId'];
        } catch (Exception $error) {
            echo $error->getMessage();
        }
    }

    private function createShopeeLive()
    {
        $headers = array(
            'baseURL' => 'https://live.shopee.co.id/webapi/v1',
            'authority' => 'live.shopee.co.id',
            'accept' => 'application/json, text/plain, */*',
            'accept-language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'client-info' => 'platform=9',
            'cookie' => $this->session,
            'sec-ch-ua' => '"Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        );

        return $this->createShopeeLiveWithHeaders($headers);
    }

    private function createShopeeLiveForCreator($cookie)
    {
        $headers = array(
            'authority' => 'creator.shopee.co.id',
            'accept' => 'application/json',
            'accept-language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'content-type' => 'application/json',
            'cookie' => $cookie,
            'language' => 'en',
            'sec-ch-ua' => '"Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'x-env' => 'live',
            'x-region' => 'id',
            'x-region-domain' => 'co.id',
            'x-region-timezone' => '+0700',
        );

        return $this->createShopeeLiveWithHeaders($headers);
    }

    private function createShopeeLiveWithHeaders($headers)
    {
        $options = array(
            'http' => array(
                'header' => implode("\r\n", array_map(function ($key, $value) {
                    return "$key: $value";
                }, array_keys($headers), $headers)),
            ),
        );

        $context = stream_context_create($options);

        return $context;
    }
}

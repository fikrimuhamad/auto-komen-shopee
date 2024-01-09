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
echo "SESSION DITEMUKAN ID: " . $client->getSessionID() . "\n";
cekULANGgetID:
$katakataSHOPEE = input("TEXT PESAN");

// Memastikan panjang string tidak melebihi 150 karakter
$katakataSHOPEE = substr($katakataSHOPEE, 0, 150);

// Pemeriksaan panjang string
if (str_word_count($katakataSHOPEE) > 150) {
    echo 'PESAN TIDAK BOLEH LEBIH DARI 150 KATA';
    goto cekULANGgetID;
} else {

    sleep(1); // Sleep for 3 seconds before the next request


    $url = 'https://live.shopee.co.id/webapi/v1/session/' . $sessionID . '/message';

    $headers = [
        'authority: live.shopee.co.id',
        'accept: application/json, text/plain, */*',
        'accept-language: id-ID,id;q=0.6',
        'client-info: platform=9',
        'cookie: ' . $cookieSHOPEE,
        'content-type: application/json',
        'origin: https://live.shopee.co.id',
        'referer: https://live.shopee.co.id/pc/live?session=' . $sessionID,
        'sec-ch-ua: "Not_A Brand";v="8", "Chromium";v="120", "Brave";v="120"',
        'sec-ch-ua-mobile: ?0',
        'sec-ch-ua-platform: "Windows"',
        'sec-fetch-dest: empty',
        'sec-fetch-mode: cors',
        'sec-fetch-site: same-origin',
        'sec-gpc: 1',
        'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    ];

    $data = [
        'uuid' => 'kkAEvzSgrSsd2asSsdsb4Ejb0xTowxw',
        'usersig' => '6TTXWHvsdFsdph6cS__-lB9Y8_fYdO1xoLzcltGIi9MfscVdVN-',
        'content' => '{"type":101,"content":"' . $katakataSHOPEE . '"}',
        'pin' => false,
    ];

    $options = [
        'http' => [
            'header' => implode("\r\n", $headers),
            'method' => 'POST',
            'content' => json_encode($data),
        ],
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === FALSE) {
        echo 'Error fetching data.';
    } else {
        // Parse JSON response
        $responseData = json_decode($response, true);

        // Check if err_msg exists
        if (isset($responseData['err_msg'])) {
            $errMsg = $responseData['err_msg'];

            // Add your custom handling for err_msg
            if ($errMsg === 'YourCustomErrorMessage') {
                echo 'Custom Error Handling: ' . $errMsg . PHP_EOL;
            } else {
                echo 'Status Message: ' . $errMsg . PHP_EOL;
            }
        }

        // Check if data.message_id exists
        if (isset($responseData['data']['message_id'])) {
            echo "Message ID: " . $responseData['data']['message_id'] . PHP_EOL . "Message: $katakataSHOPEE\n\n";
            goto cekULANGgetID;
        }
    }
}
// FUNGSI UNTUK INPUT //
function input($text)
{
    echo $text . " => : ";
    $a = trim(fgets(STDIN));
    return $a;
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

        $axiosCreator = $this->createShopeeLiveForCreator($cookie);

        try {
            $response = file_get_contents("https://creator.shopee.co.id/supply/api/lm/sellercenter/realtime/sessionList?page=1&pageSize=10&name=", false, $axiosCreator);
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

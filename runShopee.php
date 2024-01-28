function endLive()
{
    global $sessionId, $cookie;
    $apiUrl = "https://live.shopee.co.id/api/v1/session/{$sessionId}/end/";

    $requestHeaders = [
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", [
                'Host: live.shopee.co.id',
                'User-Agent: ShopeeID/3.15.24 (com.beeasy.shopee.id; build:3.15.24; iOS 16.7.2) Alamofire/5.0.5 language=id app_type=1',
                'Content-Type: application/json',
                'Cookie: ' . $cookie,
            ]),
        ],
    ];

    $context = stream_context_create($requestHeaders);

    try {
        $response = file_get_contents($apiUrl, false, $context);
        $json_response = json_decode($response, true);
        if ($json_response['err_code'] === 0 && $json_response['err_msg']) {
            echo "BERHASIL MEMBERHENTIKAN STREAMING SESSION: $sessionId\n";
        } elseif ($json_response['err_code'] === 3000057) {
            echo "STREAMING SESSION $sessionId TIDAK ADA LIVE!!\n";
        } elseif ($json_response['err_code'] === 3000059) {
            echo "STREAMING SESSION $sessionId SUDAH BERHENTI!!\n";
        } else {
            echo "GAGAL MEMBERHENTIKAN STREAMING SESSION: $sessionId | ERROR: " . $json_response['err_msg'];
        }
    } catch (Exception $e) {
        // Handle exception
        echo $e->getMessage();
    }
}

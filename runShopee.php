<?php
$bannedUsers = [];
$processedMessages = [];
$lastBotMessageID = [];
// JANGAN DIUBAH YANG INI
$keyFilePath = 'key.txt';
$key = readKeyFromFile($keyFilePath);

if (!$key) {
    error_log('KEY TIDAK DITEMUKAN!! TOLONG CEK KEMBALI FILE key.txt');
    exit(1);
}


$cookiesFilePath = 'cookie.txt';
$cookies = readCookiesFromFile($cookiesFilePath);

if (!$cookies) {
    error_log('COOKIE TIDAK DITEMUKAN!! TOLONG CEK KEMBALI FILE cookie.txt');
    exit(1);
}


$banwordFilePath = 'bannedText.txt';
$bannedWords = readBannedWordsFromFile($banwordFilePath);
if (!$bannedWords) {
    error_log('BANNEDWORD TIDAK DITEMUKAN!! TOLONG CEK KEMBALI FILE bannedText.txt');
    exit(1);
}



echo "SEDANG MENGAMBIL DATA LIVE..." . PHP_EOL;
// INI JUGA
getData();
inputLagi:
echo "-----------|[ MENU ]|-----------" . PHP_EOL;
echo "SILAHKAN PILIH MENU YANG ANDA INGINKAN" . PHP_EOL . PHP_EOL;
echo "1. GET INFO DATA RTMP LIVE" . PHP_EOL;
echo "2. AUTO KOMENTAR + AUTO GET USERSIG + AUTO BANNED FILTER KATA-KATA" . PHP_EOL;
echo "3. GET KOMENTAR + AUTO BANNED FILTER KATA-KATA" . PHP_EOL;
echo "4. AUTO PIN PRODUK / *SOON AUTO PIN BY REQUEST" . PHP_EOL;
echo "5. BALES KOMENTAR / PIN KOMENTAR" . PHP_EOL;
echo "6. AUTO SHOW VOUCHER SEIAP 1 MENIT" . PHP_EOL;
echo "7. AUTO KOMENTAR" . PHP_EOL;
echo "8. END LIVE" . PHP_EOL . PHP_EOL;

$menuSelect =  input("TENTUKAN PILIHAN ANDA ??" . PHP_EOL);

if ($menuSelect == 1) {

    getRMTP();
} elseif ($menuSelect == 2) {
    echo PHP_EOL . "KATA-KATA PADA FILE KEYWORD YANG TERSEDIA" . PHP_EOL;
    $keywordData = include "keyword.php";

    // Menampilkan semua data pada $keywordData
    foreach ($keywordData as $keyword => $response) {
        echo "Keyword: $keyword, Response: $response" . PHP_EOL;
    }
    echo PHP_EOL . "\nPESAN :" . PHP_EOL;
    echo "EDIT KATA-KATA DIATAS PADA FILE keyword.php" . PHP_EOL;

    while (true) {
        checkMessage();
        sleep(3);
    }
} elseif ($menuSelect == 3) {

    while (true) {
        GetMessage();
        sleep(3);
    }
} elseif ($menuSelect == 4) {

    echo 'ATUR PIN PRODUK SESUAI PILIHANMU' . PHP_EOL;
    echo '1. SETIAP 60 DETIK' . PHP_EOL;
    echo '2. ATUR WAKTU SENDIRI' . PHP_EOL;
    $menuPin =  readline("TENTUKAN PILIHAN ANDA ??" . PHP_EOL);
    if ($menuPin == 1) {
        echo PHP_EOL . 'AUTO PIN PRODUK SETIAP 60 DETIK' . PHP_EOL;
        $jedaPin = 61;
        while (true) {
            showItem();
        }
    } elseif ($menuPin == 2) {
        echo "MASUKKAN DELAY PIN PRODUK" . PHP_EOL;
        $jedaPin = readline("DETIK *(1-10000) => : ");
        $menit = $jedaPin / 60;
        // memunculkan result detik / 60
        echo PHP_EOL . 'AUTO PIN PRODUK SETIAP ' . number_format($menit) . ' MENIT' . PHP_EOL;
        while (true) {
            showItem();
        }
    } else {
        echo "[ GAGAL!! ] PILIHAN TIDAK DITEMUKAN!!" . PHP_EOL;
        goto inputLagi;
    }
} elseif ($menuSelect == 5) {

    menuKomen:
    do {
        echo "1. KOMEN BIASA\n2. PIN KOMEN" . PHP_EOL;
        $komenMenu =  readline("PILIH MENU:" . PHP_EOL);
        if ($komenMenu == "1") {
            // PERULANGAN UNTUK MENGIRIM PESAN LAGI
            komenLagi:
            $katakataSHOPEE = input("TEXT KOMENTAR");
            $katakataSHOPEE = substr($katakataSHOPEE, 0, 150);
            // Pemeriksaan panjang string
            if (str_word_count($katakataSHOPEE) > 150) {
                echo 'PESAN TIDAK BOLEH LEBIH DARI 150 KATA';
            } else {
                komenLive($katakataSHOPEE) . PHP_EOL . PHP_EOL;
                goto komenLagi;
            }
        } else if ($komenMenu == "2") {
            pinLagi:
            // PERULANGAN UNTUK MENGIRIM PESAN LAGI
            $katakataSHOPEE = input("TEXT PIN KOMENTAR");
            $katakataSHOPEE = substr($katakataSHOPEE, 0, 150);
            // Pemeriksaan panjang string
            if (str_word_count($katakataSHOPEE) > 150) {
                echo 'PESAN TIDAK BOLEH LEBIH DARI 150 KATA';
            } else {
                pinkomenLive($katakataSHOPEE) . PHP_EOL . PHP_EOL;
                $pilihan =  readline("PIN KOMEN LAGI ?? (Y / N)" . PHP_EOL);
                do {
                    if ($pilihan == "y" || $pilihan == "Y") {
                        goto pinLagi;
                    } else {
                        // menjalankan kembali sc Shopee
                        exec('start cmd /k php ShopeeRun.php');
                    }
                } while ($pilihan == "y" || $pilihan == "Y");
            }
        } else {
            echo "[ GAGAL!! ] PILIHAN TIDAK DITEMUKAN!!\nANDA AKAN DIKEMBALIKAN KEPILIHAN MENU!!" . PHP_EOL . PHP_EOL;
            sleep(2);
            goto menuKomen;
        }
    } while ($komenMenu == "1" || $komenMenu == "2");
} elseif ($menuSelect == 6) {

    echo PHP_EOL . 'AUTO SHOW VOUCHER SETIAP 1 MENIT' . PHP_EOL;
    while (true) {
        sleep(30); //jika ingin diubah ditambah 30, cntoh disamping 40. jika kamu jalankan dia akan delay hampir 1.menit 10detik karena ada delay tambahan dari loading api websitenya
        showVoc();
        echo 'JEDA... SHOW VOUCHER LAGI SETELAH 1MENIT' . PHP_EOL;
    }
} elseif ($menuSelect == 7) {

    $katakataSHOPEE = input("MASUKKAN TEXT KOMENTAR");
    $katakataSHOPEE = substr($katakataSHOPEE, 0, 150);
    // Pemeriksaan panjang string
    if (str_word_count($katakataSHOPEE) > 150) {
        echo 'PESAN TIDAK BOLEH LEBIH DARI 150 KATA';
    } else {
        echo "MASUKKAN JEDA KIRIM KOMEN" . PHP_EOL;
        $jedaNgulang = readline("DETIK *(1-10000) => : ");
        $menit = $jedaNgulang / 60;
        // memunculkan result detik / 60
        echo 'MENGIRIM PESAN BERULANG SETIAP ' . number_format($menit) . ' MENIT' . PHP_EOL;
        while (true) {
            komenLiveNgulang($katakataSHOPEE);
            echo 'JEDA... MENGIRIM KOMEN LAGI SETELAH ' . number_format($menit) . ' MENIT' . PHP_EOL;
            sleep($jedaNgulang);
        }
    }
} elseif ($menuSelect == 8) {

    endLive();
    // } elseif ($menuSelect == 7) {
} else {
    echo "[ GAGAL!! ] PILIHAN TIDAK DITEMUKAN!!" . PHP_EOL;
    goto inputLagi;
}

function input($text)
{
    echo $text . "  => : ";
    $select = trim(fgets(STDIN));
    return $select;
}
function getData()
{
    global $cookies, $key, $sessionId, $deviceId, $sellerId, $chatroomId, $shareurl, $usersig, $sellerId, $usernameId;
    try {
        $getSession = api("https://mas.mba/apiShopee/api.php?key=$key&cookies=" . urlencode($cookies));
        $sessionData = json_decode($getSession, true);
        if (!$sessionData) {
            echo $getSession;
            exit(1);
        }

        $errCode = $sessionData["err_code"] ?? null;
        $data = $sessionData["data"] ?? null;

        if ($errCode === 0 && $data && isset($data["session"])) {
            $session = $data["session"];
            $sessionId = $session["session_id"] ?? '-';
            $deviceId = $session["device_id"] ?? '-';
            $sellerId = $session["uid"] ?? '-';
            $timestamp = $session["start_time"] ?? 0;
            $usernameId = $session['username'] ?? '-';
            $chatroomId = $session['chatroom_id'] ?? '-';
            $usersig = $data['usersig'] ?? '-';
            $shareurl = $data['share_url'] ?? '-';
            $Title = $session["title"] ?? '-';
            $Live = $session["status"] ?? '-';
            $timestamp_in_seconds = $timestamp / 1000;
            $tanggalMulai = strtoupper(date("d/M", $timestamp_in_seconds));
            $jamMulai = strtoupper(date("g:i a", $timestamp_in_seconds));
            $statusLive = ($Live == "1") ? "RUNNING" : "STOP";

            echo PHP_EOL . "--------|[ INFO DATA LIVE ]|--------" . PHP_EOL;
            echo 'LIVE TITLE: ' . $Title . PHP_EOL;
            echo 'LIVE TANGGAL: ' . $tanggalMulai . ' ' . $jamMulai . PHP_EOL;
            echo 'STATUS LIVE: ' . $statusLive . PHP_EOL;
            echo 'USERNAME: ' . $usernameId . PHP_EOL;
            echo 'SELLER ID: ' . $sellerId . PHP_EOL;
            echo PHP_EOL . '------|[ SESSION DATA LIVE ]|------' . PHP_EOL;
            echo 'SESSION LIVE: ' . $sessionId . PHP_EOL;
            echo 'DEVICEID LIVE: ' . $deviceId . PHP_EOL;
            echo 'CHATROOM LIVE: ' . $chatroomId . PHP_EOL;
            echo 'USERSIG LIVE: ' . $usersig . PHP_EOL;
            echo PHP_EOL . '------|[ SHARE URL LIVE ]|------' . PHP_EOL;
            echo 'URL LIVE: ' . $shareurl . PHP_EOL . PHP_EOL;
        } else {
            throw new Exception("Error obtaining SESSIONID: " . ($sessionData["err_msg"] ?? 'Unknown Error'));
        }
    } catch (Exception $e) {
        echo $e->getMessage() . PHP_EOL;
    }
}

function getRMTP()
{
    global $cookies, $sessionId, $key;
    try {
        $getRMTP = api("https://mas.mba/apiShopee/rmtp.php?key=$key&cookies=" . urlencode($cookies));
        $RMTPdata = json_decode($getRMTP, true);
        if ($RMTPdata === null && json_last_error() !== JSON_ERROR_NONE) {
            echo $getRMTP;
            exit(1);
        }

        $errCode = $RMTPdata["err_code"] ?? null;
        $data = $RMTPdata["data"] ?? null;

        if ($errCode === 0 && $data && isset($data["push_url_list"])) {
            $keyLive = $data['push_url_list'][1];
            $misahKey = preg_split("/\/(live|livestreaming)\//", $keyLive, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $type = $misahKey[0];
            $key = $type . '/' . $misahKey[1] . '/';
            $rtmp = $misahKey[2];
            echo PHP_EOL . '===| STREAMING KEY INFO |===' . PHP_EOL . PHP_EOL;
            echo 'DASHBOARD LIVE: https://creator.shopee.co.id/dashboard/live/' . $sessionId . PHP_EOL;
            echo 'RTMP FULL: ' . $keyLive . PHP_EOL . PHP_EOL;
            echo 'KEY: ' . $key . PHP_EOL;
            echo 'RTMP: ' . $rtmp . PHP_EOL;
            echo PHP_EOL . '===| CARA LIVE SHOOPE |===' . PHP_EOL;
            echo 'CARA LIVENYA GIMANA??,' . PHP_EOL;
            echo 'LIVE LANGSUNG DARI SHOPE APP, DAN KETIKA SUDAH PLAY LANGSUNG AJH CLOSE APPNYA / HILANGIN,' . PHP_EOL;
            echo 'LALU START DARI MULTI LOOP / TOOLS YANG KALIAN GUNAKAN!!' . PHP_EOL . PHP_EOL;
            echo 'DATA RTMP DISAVE PADA FILE dataRTMP.txt' . PHP_EOL . PHP_EOL;
            $fp = fopen("dataRTMP.txt", 'w');
            fwrite($fp, "RMTP: $rtmp\nKEY: $key\n\nRTMP FULL: $keyLive");
            fclose($fp);
        } else {
            echo 'ERROR MENDAPATKAN SESSIONID : ' . $RMTPdata['err_msg'] . PHP_EOL;
        }
    } catch (Exception $e) {
        echo $e->getMessage() . PHP_EOL;
    }
}


function banUser($uid)
{
    global $sessionId, $cookies;

    $banUrl = 'https://live.shopee.co.id/webapi/v1/session/' . $sessionId . '/comment/ban';

    $postData = [
        'is_ban' => true,
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
        echo strtoupper($messages) . ' BANNED CHAT USER UID: ' . $uid . PHP_EOL . PHP_EOL;
    } else {
        echo 'Error banning user.' . PHP_EOL;
    }
}

//bot komen
function komenLive($message)
{
    global $sessionId, $cookies, $deviceId, $responseKomen, $usersig;

    $komenUrl = 'https://live.shopee.co.id/webapi/v1/session/' . $sessionId . '/message';

    $postData = [
        'uuid' => $deviceId,
        'usersig' => $usersig,
        'content' => '{"type":101,"content":"' . $message . '"}',
        'pin' => false,
    ];

    $options = [
        'http' => [
            'header' => [
                'Cookie: ' . $cookies,
                'Content-Type: application/json',
                'referer: https://live.shopee.co.id/pc/live?session=' . $sessionId,
            ],
            'method' => 'POST',
            'content' => json_encode($postData),
        ],
    ];

    $context = stream_context_create($options);

    $responseKomen = file_get_contents($komenUrl, false, $context);

    if ($responseKomen === FALSE) {
        echo 'Error fetching data.';
    } else {
        // Parse JSON response
        $responseData = json_decode($responseKomen, true);

        // Check if err_msg exists
        if (isset($responseData['err_msg'])) {
            $errMsg = $responseData['err_msg'];

            // Add your custom handling for err_msg
            if ($errMsg === 'YourCustomErrorMessage') {
                echo 'Custom Error Handling: ' . $errMsg . PHP_EOL;
            } else {
                echo 'STATUS PESAN BOT: ' . strtoupper($errMsg);
            }
        }

        // Check if data.message_id exists
        if (isset($responseData['data']['message_id'])) {
            echo " ( " . $responseData['data']['message_id'] . " )" . PHP_EOL . PHP_EOL;
        }
    }
}

function komenLiveNgulang($message)
{
    global $sessionId, $cookies, $deviceId, $responseKomen, $usersig;

    $komenUrl = 'https://live.shopee.co.id/webapi/v1/session/' . $sessionId . '/message';

    $postData = [
        'uuid' => $deviceId,
        'usersig' => $usersig,
        'content' => '{"type":101,"content":"' . $message . '"}',
        'pin' => false,
    ];

    $options = [
        'http' => [
            'header' => [
                'Cookie: ' . $cookies,
                'Content-Type: application/json',
                'referer: https://live.shopee.co.id/pc/live?session=' . $sessionId,
            ],
            'method' => 'POST',
            'content' => json_encode($postData),
        ],
    ];

    $context = stream_context_create($options);

    $responseKomen = file_get_contents($komenUrl, false, $context);

    if ($responseKomen === FALSE) {
        echo 'Error fetching data.';
    } else {
        // Parse JSON response
        $responseData = json_decode($responseKomen, true);

        // Check if err_msg exists
        if (isset($responseData['err_msg'])) {
            $errMsg = $responseData['err_msg'];

            // Add your custom handling for err_msg
            if ($errMsg === 'YourCustomErrorMessage') {
                echo 'Custom Error Handling: ' . $errMsg . PHP_EOL;
            } else {
                echo 'STATUS PESAN BOT: ' . strtoupper($errMsg);
            }
        }

        // Check if data.message_id exists
        if (isset($responseData['data']['message_id'])) {
            echo " ( " . $responseData['data']['message_id'] . " )" . PHP_EOL . PHP_EOL;
        }
    }
}

function pinkomenLive($message)
{
    global $sessionId, $cookies, $deviceId, $responseKomen, $usersig;

    $komenUrl = 'https://live.shopee.co.id/webapi/v1/session/' . $sessionId . '/message';

    $postData = [
        'uuid' => $deviceId,
        'usersig' => $usersig,
        'content' => '{"type":101,"content":"' . $message . '"}',
        'pin' => true,
    ];

    $options = [
        'http' => [
            'header' => [
                'Cookie: ' . $cookies,
                'Content-Type: application/json',
                'referer: https://live.shopee.co.id/pc/live?session=' . $sessionId,
            ],
            'method' => 'POST',
            'content' => json_encode($postData),
        ],
    ];

    $context = stream_context_create($options);

    $responseKomen = file_get_contents($komenUrl, false, $context);

    if ($responseKomen === FALSE) {
        echo 'Error fetching data.';
    } else {
        // Parse JSON response
        $responseData = json_decode($responseKomen, true);

        // Check if err_msg exists
        if (isset($responseData['err_msg'])) {
            $errMsg = $responseData['err_msg'];

            // Add your custom handling for err_msg
            if ($errMsg === 'YourCustomErrorMessage') {
                echo 'Custom Error Handling: ' . $errMsg . PHP_EOL;
            } else {
                echo 'STATUS PESAN BOT: ' . strtoupper($errMsg);
            }
        }

        // Check if data.message_id exists
        if (isset($responseData['data']['message_id'])) {
            echo " ( " . $responseData['data']['message_id'] . " )" . PHP_EOL . PHP_EOL;
        }
    }
}

function showVoc()
{
    global $cookies, $sessionId, $key;

    try {
        $url = 'https://mas.mba/apiShopee/voucher.php?key=' . $key . '&sessionid=' . $sessionId . '&cookies=' . urlencode($cookies);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $result = curl_exec($ch);

        if ($result === false) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);
        echo $result;
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage() . PHP_EOL;
    }
}

function endLive()
{
    global $sessionId, $cookies;
    $apiUrl = "https://live.shopee.co.id/api/v1/session/$sessionId/end/";

    $requestHeaders = [
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", [
                'Host: live.shopee.co.id',
                'User-Agent: ShopeeID/3.15.24 (com.beeasy.shopee.id; build:3.15.24; iOS 16.7.2) Alamofire/5.0.5 language=id app_type=1',
                'Content-Type: application/json',
                'Cookie: ' . $cookies,
            ]),
        ],
    ];

    $context = stream_context_create($requestHeaders);

    try {
        $response = file_get_contents($apiUrl, false, $context);
        $json_response = json_decode($response, true);
        if ($json_response['err_code'] === 0) {
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

//auto komen + ban filter kata-kata
function checkMessage()
{
    global $chatroomId, $deviceId, $cookies, $processedMessages, $bannedUsers, $user, $lastBotMessageID, $userLastResponseTime, $sellerId;

    $apiUrl = 'https://chatroom-live.shopee.co.id/api/v1/fetch/chatroom/' . $chatroomId . '/message?uuid=' . $deviceId;

    $options = [
        'http' => [
            'header' => 'Cookie: ' . $cookies,
        ],
    ];

    $context = stream_context_create($options);

    $response = file_get_contents($apiUrl, false, $context);

    if ($response === false) {
        echo 'Error making request.' . PHP_EOL;
        return null; // Return null in case of an error
    }

    // Extract data from JSON response
    $responseJson = json_decode($response, true);

    // Check if data is available
    if (!isset($responseJson['data']['message'][0]['msgs'])) {
        return null;
    }

    $messages = $responseJson['data']['message'][0]['msgs'];
    $timestamp = $responseJson['data']['timestamp'];

    // Extract id, display_name, and content
    $extractedData = array_map(function ($message) {
        return [
            'id' => $message['id'],
            'uid' => $message['uid'],
            'nickname' => $message['nickname'],
            'display_name' => $message['display_name'],
            'content' => json_decode($message['content'], true)['content'],
        ];
    }, $messages);

    // Filter out already processed messages
    $newMessages = array_filter($extractedData, function ($message) use ($processedMessages) {
        return !in_array($message['content'], $processedMessages);
    });

    foreach ($newMessages as $message) {
        $user = $message['uid'];
        if ($sellerId === $user) {
            // Continue to the next iteration if the user is the seller
            continue;
        }

        // Print extracted data for new messages
        if (!empty($newMessages)) {
            $date = date('d/m H:i:s', $timestamp);
            echo "===| NEW MESSAGE |===" . PHP_EOL;
            echo "TIME: " . $date . PHP_EOL . "";
            echo "UID: " . $message['uid'] . PHP_EOL . "";
            echo "NAMA: " . $message['display_name'] . " ( " . $message['nickname'] . " )" . PHP_EOL;
            echo "MESSAGE: " . $message['content'] . PHP_EOL . "";
            echo "STATUS: ";



            // Add the processed message to the array
            $processedMessages[] = $message['content'];

            // Check for banned words
            if (containsBannedWords($message['content'])) {
                echo 'DITEMUKAN KATA-KATA YANG DIFILTER!!' . PHP_EOL;

                if (!in_array($message['uid'], $bannedUsers)) {
                    banUser($message['uid']);
                    $bannedUsers[] = $message['uid'];
                } else {
                    echo 'USER SUDAH DIBANNED CHAT!! SKIP!!' . PHP_EOL . PHP_EOL;
                }
            } else {
                echo 'TIDAK DITEMUKAN KATA-KATA YANG DIFILTER!!' . PHP_EOL . PHP_EOL;
            }



            $currentTime = time();
            // Check if the user has been responded to in the last 5 seconds
            if (isset($userLastResponseTime[$user]) && ($currentTime - $userLastResponseTime[$user]) < 5) {
                file_put_contents('auto_reply.txt', '');
                continue; // Skip processing this message
            }


            // Include file with keyword-response pairs
            $keywordData = include "keyword.php";

            // Check if the message contains a specific phrase to be skipped
            $skipPhrases = ["Saya bergabung Lelang Time!", "Saya bergabung Lelang Yuk!", "Saya bergabung Cepet Cepetan Time!", "Saya bergabung Cepet Cepetan Dapat harga murah!", "Saya bergabung Lelang Yuk Guyss!"];
            $skipMessage = false;
            foreach ($skipPhrases as $skipPhrase) {
                if (strpos(strtolower($message['content']), strtolower($skipPhrase)) !== false) {
                    $skipMessage = true;
                    break; // Stop checking after the first match
                }
            }

            if ($skipMessage) {
                // Skip processing this message
                file_put_contents('auto_reply.txt', '');
                continue;
            }

            $foundKeyword = false;
            foreach ($keywordData as $keyword => $response) {
                if (strpos(strtolower($message['content']), $keyword) !== false) {
                    $personalizedResponse = 'Hallo Kak ' . $message['nickname'] . ", " . $response;
                    file_put_contents('auto_reply.txt', $personalizedResponse);
                    $foundKeyword = true;
                    break; // Stop checking after the first match
                }
            }

            // If no keyword was found, write a default personalized message
            if (!$foundKeyword) {
                $defaultResponse = 'Hallo Kak ' . $message['nickname'] . ", Semua barang sesuai dengan gambar ya kak, bisa langsung cek Keranjang, silakan diorder kakak.";
                file_put_contents('auto_reply.txt', $defaultResponse);
            }
        }
        // Update the last response time for the user
        $userLastResponseTime[$user] = $currentTime;
        $lastBotMessageID[] = $message;
    }
    $messageFilePath = 'auto_reply.txt';

    // Membaca isi file auto_reply.txt
    $message = file_get_contents($messageFilePath);

    // Mengirim pesan jika isi file tersedia
    if (!empty($message)) {
        komenLive($message);
        $lastBotMessageID[] = $message;

        // Menghapus isi file agar tidak dikirim lagi
        file_put_contents($messageFilePath, '');
    }
}

function GetMessage()
{
    global $chatroomId, $deviceId, $cookies, $processedMessages, $bannedUsers, $sessionId, $sellerId;

    $apiUrl = 'https://chatroom-live.shopee.co.id/api/v1/fetch/chatroom/' . $chatroomId . '/message?uuid=' . $deviceId;

    $options = [
        'http' => [
            'header' => 'Cookie: ' . $cookies,
        ],
    ];

    $context = stream_context_create($options);

    $response = file_get_contents($apiUrl, false, $context);

    if ($response === false) {
        echo 'Error making request.' . PHP_EOL;
        return null; // Return null in case of an error
    }

    // Extract data from JSON response
    $responseJson = json_decode($response, true);

    // Check if data is available
    if (isset($responseJson['data']['message'][0]['msgs'])) {
        $messages = $responseJson['data']['message'][0]['msgs'];
        $timestamp = $responseJson['data']['timestamp'];

        // Extract id, display_name, and content
        $extractedData = array_map(function ($message) {
            return [
                'id' => $message['id'],
                'uid' => $message['uid'],
                'nickname' => $message['nickname'],
                'display_name' => $message['display_name'],
                'content' => json_decode($message['content'], true)['content'],
            ];
        }, $messages);

        // Filter out already processed messages
        $newMessages = array_filter($extractedData, function ($message) use ($processedMessages) {
            return !in_array($message['content'], $processedMessages);
        });

        foreach ($newMessages as $message) {
            $user = $message['uid'];
            if ($sellerId === $user) {
                // Continue to the next iteration if the user is the seller
                continue;
            }

            // Print extracted data for new messages
            if (!empty($newMessages)) {
                $date = date('d/m H:i:s', $timestamp);
                echo "===| NEW MESSAGE |===" . PHP_EOL;
                echo "TIME: " . $date . PHP_EOL . "";
                echo "UID: " . $message['uid'] . PHP_EOL . "";
                echo "NAMA: " . $message['display_name'] . PHP_EOL . "";
                echo "MESSAGE: " . $message['content'] . PHP_EOL . "";
                echo "STATUS: ";

                // Add the processed message to the array
                $processedMessages[] = $message['content'];

                // Check for banned words
                if (containsBannedWords($message['content'])) {
                    echo 'DITEMUKAN KATA-KATA YANG DIFILTER!!' . PHP_EOL;

                    if (!in_array($message['uid'], $bannedUsers)) {
                        banUser($message['uid']);
                        $bannedUsers[] = $message['uid'];
                    } else {
                        echo 'USER SUDAH DIBANNED CHAT!! SKIP!!' . PHP_EOL . PHP_EOL;
                    }
                } else {
                    echo 'TIDAK DITEMUKAN KATA-KATA YANG DIFILTER!!' . PHP_EOL . PHP_EOL;
                }
            }
        }
    }
}

//pin produk
function getItemData()
{
    global $cookies, $sessionId, $acakNomorProduk, $DataItem, $produkItem, $totalProduk;

    $sessionUrl = "https://live.shopee.co.id/webapi/v1/session/$sessionId/host/items?visible=true&offset=0&limit=100";

    $options = [
        'http' => [
            'header' => 'Cookie: ' . $cookies,
            'referer' => "https://live.shopee.co.id/pc/preview?session=$sessionId",
        ],
    ];

    $context = stream_context_create($options);

    $sessionData = file_get_contents($sessionUrl, false, $context);
    if ($sessionData) {
        // Decode JSON string into an array
        $sessionData = json_decode($sessionData, true);

        // Check if 'total_count' is greater than 0
        if ($sessionData['data']['total_count'] > 0) {
            // Get total count
            $totalProduk = $sessionData['data']['total_count'];

            // Generate random index
            $acakNomorProduk = mt_rand(0, $totalProduk - 1);

            // Get random item data
            $NoProduk = $sessionData['data']['items'][$acakNomorProduk];
            $produkItem = "ID: " . $NoProduk['item_id'] . PHP_EOL . "NAMA: " . $NoProduk['name'];

            // Modify the response data to include only the random item
            $sessionData = $NoProduk;
        } else {
            // If total_count is 0, set response to an empty array
            $sessionData = array();
        }

        // Encode modified response back to JSON
        $DataItem = json_encode($sessionData);

        // Return the modified response
        return $DataItem;
    } else {
        // Return an error message if JSON decoding fails
        return 'Error decoding JSON data.';
    }
}

function showItem()
{
    global $cookies, $sessionId, $DataItem, $acakNomorProduk, $produkItem, $jedaPin;
    // Panggil fungsi getItemData() terlebih dahulu

    $sessionIdData = "https://live.shopee.co.id/webapi/v1/session/$sessionId/show";

    $data = [
        'item' => $DataItem, // Ubah $DataItem menjadi array
    ];

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r" . PHP_EOL .
                'Cookie: ' . $cookies,
            'method'  => 'POST',
            'content' => json_encode($data), // Ubah 'content' menjadi json_encode($data)
        ),
    );

    $context = stream_context_create($options);

    $sessionIdData = file_get_contents($sessionIdData, false, $context);

    if ($sessionIdData) {
        // Decode JSON string into an array
        $sessionIdData = json_decode($sessionIdData, true);
        getItemData();

        if ($sessionIdData['err_code'] === 0) {
            $statusPinProduk = $sessionIdData['err_msg'];
            echo "SET PIN ETALASE NO " . $acakNomorProduk + 1 . PHP_EOL . "$produkItem" . PHP_EOL;
            echo "STATUS PIN PRODUK: ";
            sleep($jedaPin);
            echo strtoupper($statusPinProduk) . " MENAMPILKAN ETALASE NO " . $acakNomorProduk + 1 . "!!" . PHP_EOL . PHP_EOL;

            // Return the session ID for further use
        } else {
            echo 'ERROR MENDAPATKAN SESSIONID : ' . $sessionIdData['err_msg'] . PHP_EOL;
        }
    } else {
        echo 'Error decoding JSON data.' . PHP_EOL;
    }
}



function api($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

    $resultApi = curl_exec($ch);
    curl_close($ch);
    return $resultApi;
}


function readBannedWordsFromFile($filePath)
{
    try {
        $bannedWords = explode(PHP_EOL . "", file_get_contents($filePath));
        // Menghapus string kosong atau yang hanya terdiri dari spasi dari $bannedWords
        $bannedWords = array_filter($bannedWords, function ($word) {
            return trim($word) !== '';
        });
        $bannedWords = array_map('trim', $bannedWords);
        return $bannedWords;
    } catch (Exception $error) {
        error_log('Error reading banned words from file: ' . $error->getMessage());
        return [];
    }
}

function readKeyFromFile($filePath)
{
    try {
        $cookies = file_get_contents($filePath);
        return trim($cookies);
    } catch (Exception $error) {
        error_log('Error reading cookies from file: ' . $error->getMessage());
        return null;
    }
}
function containsBannedWords($content)
{
    global $bannedWords;

    $lowerContent = strtolower($content);
    return array_reduce($bannedWords, function ($carry, $word) use ($lowerContent) {
        return $carry || strpos($lowerContent, strtolower($word)) !== false;
    }, false);
}

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

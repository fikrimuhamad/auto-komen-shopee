<?php
echo "===| ENTER USERSIG DATA |==\n";
$usersig = input("");
if(empty($usersig)){
    echo'USERSIG TIDAK BOLEH KOSONG!!';
    exit;
}

function readCookiesFromFile($filePath) {
    try {
        $cookies = file_get_contents($filePath);
        return trim($cookies);
    } catch (Exception $error) {
        error_log('Error reading cookies from file: ' . $error->getMessage());
        return null;
    }
}

function readBannedWordsFromFile($filePath) {
    try {
        $bannedWords = file_get_contents($filePath);
        $bannedWords = explode("\n", $bannedWords);
        $bannedWords = array_map('trim', $bannedWords);
        return $bannedWords;
    } catch (Exception $error) {
        error_log('Error reading banned words from file: ' . $error->getMessage());
        return [];
    }
}

$cookiesFilePath = 'cookie.txt';
$banwordFilePath = 'bannedText.txt';

$cookies = readCookiesFromFile($cookiesFilePath);

if (!$cookies) {
    error_log('Cookies not available. Please check your cookies.txt file.');
    exit(1);
}

$bannedWords = readBannedWordsFromFile($banwordFilePath);

$sessionId = null;
$chatroomId = null;
$deviceId = null;

$bannedUsers = [];
$processedMessages = [];
$lastBotMessageID = [];
function getSessionId() {
    global $cookies, $sessionId, $chatroomId, $deviceId, $sellerId, $usersig;

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
            $titleLIVE = $sessionData['data']['session']['title'];
            $sellerId = $sessionData['data']['session']['uid'];
            $usernameId = $sessionData['data']['session']['username'];
            $sessionId = $sessionData['data']['session']['session_id'];
            $chatroomId = $sessionData['data']['session']['chatroom_id'];
            $deviceId = $sessionData['data']['session']['device_id'];

            echo PHP_EOL . '===| SESSION INFO |===' . PHP_EOL;
            echo 'TITLE LIVE: ' . $titleLIVE . PHP_EOL;
            echo 'SELLER NAME: ' . $usernameId . PHP_EOL;
            echo 'SELLER ID: ' . $sellerId . PHP_EOL;
                    
            echo PHP_EOL . '===| LIVE INFO |===' . PHP_EOL;
            echo 'USERSIG LIVE: ' . $usersig . PHP_EOL;
            echo 'UUID / DEVICEID LIVE: ' . $deviceId . PHP_EOL;
            echo 'CHATROOM LIVE: ' . $chatroomId . PHP_EOL;
            echo 'SESSION LIVE: ' . $sessionId . PHP_EOL . PHP_EOL;

            checkMessage();
        } else {
            echo 'Error getting session ID: ' . $sessionData['err_msg'] . PHP_EOL;
        }
    } else {
        echo 'Error getting session ID.' . PHP_EOL;
    }
}

function input($text)
{
    echo $text . " => : ";
    $a = trim(fgets(STDIN));
    return $a;
}

function containsBannedWords($content) {
    global $bannedWords;

    $lowerContent = strtolower($content);
    return array_reduce($bannedWords, function ($carry, $word) use ($lowerContent) {
        return $carry || strpos($lowerContent, strtolower($word)) !== false;
    }, false);
}

function banUser($uid) {
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
        echo strtoupper($messages). ' BANNED CHAT USER UID: '.$uid . PHP_EOL . PHP_EOL;
    } else {
        echo 'Error banning user.' . PHP_EOL;
    }
}

function komenLive($message) {
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
            echo " ( ".$responseData['data']['message_id']." )" . PHP_EOL . "MESSAGE BOT: $message". PHP_EOL. PHP_EOL;
        }
    }
}

function checkMessage() {
    global $chatroomId, $deviceId, $cookies, $processedMessages, $bannedUsers, $sessionId, $user, $lastBotMessageID, $userLastResponseTime;

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
        

        // Print extracted data for new messages
        if (!empty($newMessages)) {
            $date = date('d/m H:i:s', $timestamp);
            echo "===| NEW MESSAGE |==\n";
            echo "TIME: " . $date . "\n";
            echo "UID: " . $message['uid'] . "\n";
            echo "NAMA: " . $message['display_name'] . " ( " . $message['nickname'] . " )\n";
            echo "MESSAGE: " . $message['content'] . "\n";
            echo "STATUS: ";

            $user = $message['uid'];

        
            // Add the processed message to the array
            $processedMessages[] = $message['content'];

            // Check for banned words
            if (containsBannedWords($message['content'])) {
                echo 'DITEMUKAN KATA-KATA YANG DIFILTER!!' . PHP_EOL;

                if (!in_array($message['uid'], $bannedUsers)) {
                    banUser($message['uid'], $cookies, $sessionId);
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


            $keywordsToResponses = [
                'stock' => 'Semua barang ready stock ya kak, silakan diorder kakak.',
                'diskon' => 'untuk vocher diskon / gratis ongkir silahkan ditonton saja livenya ya kak, nnti muncul popup vochernya tinggal diclaim saja kakak.',
                'stok' => 'Semua barang ready stock ya kak, silakan diorder kakak.',
                'spill' => 'Maaf kak ga bisa spill semua ya kak, Bisa langsung cek Ulasan dan Gambar, silakan diorder kakak.',
                'perkenalkan' => 'Maaf kak ga bisa spill semua ya kak, Bisa langsung cek Ulasan dan Gambar, silakan diorder kakak.',
                'introduce' => 'Maaf kak ga bisa spill semua ya kak, Bisa langsung cek Ulasan dan Gambar, silakan diorder kakak.',
                'eta' => 'Maaf kak ga bisa spill semua ya kak, Bisa langsung cek Ulasan dan Gambar, silakan diorder kakak.',
                'bahan' => 'Kami menggunakan bahan RAYON ya kak, silakan diorder kakak.',
                'warna' => 'Warna Random dan Bisa dipilih tergantung Etalase ya kak, silakan diorder kakak.',
                'bb' => 'Semua Daster di sini adalah All Size ya kak, silakan diorder kakak.',
                'harga' => 'Harga sesuai yang ada di Etalase ya kak, silakan diorder kakak.',
                'rekaman' => 'Kita live langsung kak buka rekaman, jika komentar kakak belum dibaca mungkin ketiban ya kak oleh yg lain, jadi ditunggu saja kak.',
                'dibaca' => 'Kita baca kok kak satu-satu komentarnya, jika komentar kakak belum dibaca mungkin ketiban ya kak oleh yg lain, jadi ditunggu saja kak.',
            ];

            $foundKeyword = false;
            foreach ($keywordsToResponses as $keyword => $response) {
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




getSessionId();

while (true) {
    $startTime = microtime(true); // Waktu awal eksekusi
     // Check if the user is the seller
     if ($sellerId === $user) {
        // Continue to the next iteration if the user is the seller
        continue;
    }
    checkMessage();
    
    $endTime = microtime(true); // Waktu setelah eksekusi checkMessage
    $elapsedTime = $endTime - $startTime; // Waktu yang diperlukan untuk eksekusi checkMessage
    // Jika waktu yang diperlukan kurang dari 5 detik, tunggu selama (5 - elapsedTime) detik
    if ($elapsedTime < 5) {
        sleep(5 - $elapsedTime);
    } else {
        // Jika waktu yang diperlukan lebih dari atau sama dengan 5 detik, tetap tidur selama 3 detik
        sleep(3);
    }
}
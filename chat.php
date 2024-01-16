<?php
date_default_timezone_set('Asia/Jakarta');

//UBAH BAGIAN SINI SAJA!!
$cookiesFilePath = 'cookieS.txt';
$banwordFilePath = 'bannedText.txt';
//BAGIAN BAWAH JANGAN DIUTAK ATIK YA BANG!!

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
function getSessionId() {
    global $cookies, $sessionId, $chatroomId, $deviceId;

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

function checkMessage() {
    global $chatroomId, $deviceId, $cookies, $processedMessages, $bannedUsers, $sessionId;

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

        // Print extracted data for new messages
        if (!empty($newMessages)) {
            $date = date('d/m H:i:s', $timestamp);
            echo "===| NEW MESSAGE |==\n";
            foreach ($newMessages as $message) {
                echo "TIME: " . $date . "\n";
                echo "UID: " . $message['uid'] . "\n";
                echo "NAMA: " . $message['display_name'] . "\n";
                echo "MESSAGE: " . $message['content'] . "\n";
                echo "STATUS: ";

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
                    echo 'TIDAK DITEMUKAN KATA-KATA YANG DIFILTER!!'. PHP_EOL . PHP_EOL;
                }
            }
        }
    }
}

getSessionId();

while (true) {
    $startTime = microtime(true); // Waktu awal eksekusi

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

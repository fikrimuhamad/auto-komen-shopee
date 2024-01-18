<?php
date_default_timezone_set('Asia/Jakarta');
echo "----------- [ MASUKKAN SESSIONID LIVE ] -----------\n";
$sessionLive =  input("");

inputLagi:
echo "----------- [ MENU ] -----------\n";
echo "SILAHKAN PILIH MENU YANG ANDA INGINKAN\n\n";
echo "1. BOT AUTO KOMEN + AUTO GET USERSIG + AUTO BANNED FILTER KATA-KATA\n";
echo "2. GET KOMEN + AUTO BANNED FILTER KATA-KATA\n";
echo "3. RANDOM PIN PRODUK SETIAP 1 MENIT\n";

$menuSelect =  input("TENTUKAN PILIHAN ANDA ??\n");
if ($menuSelect == 1) {
    echo "\nKATA-KATA PADA FILE KEYWORD YANG TERSEDIA\n";
    $keywordData = include "keyword.php";

    // Menampilkan semua data pada $keywordData
    foreach ($keywordData as $keyword => $response) {
        echo "Keyword: $keyword, Response: $response\n";
    }
    echo "\n\nPESAN :\n";
    echo "EDIT KATA-KATA DIATAS PADA FILE keyword.php\n";

    $cookiesFilePath = 'cookie.txt';
    $banwordFilePath = 'bannedText.txt';

    $cookies = readCookiesFromFile($cookiesFilePath);
    $bannedWords = readBannedWordsFromFile($banwordFilePath);

    if (!$cookies) {
        error_log('Cookies not available. Please check your cookies.txt file.');
        exit(1);
    }

    $sessionId = null;
    $chatroomId = null;
    $deviceId = null;

    $bannedUsers = [];
    $processedMessages = [];
    $lastBotMessageID = [];

    getData();
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
} elseif ($menuSelect == 2) {
    $cookiesFilePath = 'cookie.txt';
    $banwordFilePath = 'bannedText.txt';


    $cookies = readCookiesFromFile($cookiesFilePath);
    $bannedWords = readBannedWordsFromFile($banwordFilePath);

    if (!$cookies) {
        error_log('Cookies not available. Please check your cookies.txt file.');
        exit(1);
    }


    $sessionId = null;
    $chatroomId = null;
    $deviceId = null;

    $bannedUsers = [];
    $processedMessages = [];
    $lastBotMessageID = [];

    getData();
    getSessionId();

    while (true) {
        $startTime = microtime(true); // Waktu awal eksekusi

        GetMessage();

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
} elseif ($menuSelect == 3) {
    // Fungsi untuk membaca cookies dari file
    $cookiesFilePath = 'cookie.txt';
    $cookies = readCookiesFromFile($cookiesFilePath);
    if (!$cookies) {
        error_log('Cookies not available. Please check your cookies.txt file.');
        exit(1);
    }
    getData();
    getSessionId();

    while (true) {
        showItem();
    }
} else {
    echo "[ GAGAL!! ] PILIHAN TIDAK DITEMUKAN!!\n";
    goto inputLagi;
}

function input($text)
{
    echo $text . "  => : ";
    $select = trim(fgets(STDIN));
    return $select;
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

function readBannedWordsFromFile($filePath)
{
    try {
        $bannedWords = file_get_contents($filePath);
        $bannedWords = explode("\n", $bannedWords);
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

//get fake data live
function getData()
{
    global $cookies, $sessionId, $deviceId, $sessionLive;

    $sessionUrl = "https://live.shopee.co.id/webapi/v1/session/$sessionLive/preview?uuid=sd" . $sessionLive . "sd&ver=2";

    $options = [
        'http' => [
            'header' => 'Cookie: ' . $cookies,
            'referer' => "https://live.shopee.co.id/pc/preview?session=$sessionLive",
        ],
    ];

    $context = stream_context_create($options);

    $sessionData = file_get_contents($sessionUrl, false, $context);

    // Decode JSON string into an array
    $sessionData = json_decode($sessionData, true);

    if ($sessionData) {
        if ($sessionData['err_code'] === 0 && $sessionData['data'] && $sessionData['data']['session']) {

            $sessionId = $sessionData['data']['session']['session_id'];
            $deviceId = $sessionData['data']['session']['device_id'];
            // Return the session ID for further use
        } else {
            echo 'Error getting session ID: ' . $sessionData['err_msg'] . PHP_EOL;
        }
    } else {
        echo 'Error decoding JSON data.' . PHP_EOL;
    }
}

//get data live
function getSessionId()
{
    global $cookies, $sessionId, $chatroomId, $deviceId, $sellerId, $usersig;

    $sessionIdData = "https://live.shopee.co.id/webapi/v1/session/$sessionId/preview?uuid=$deviceId&ver=2";

    $options = [
        'http' => [
            'header' => 'Cookie: ' . $cookies,
            'referer' => "https://live.shopee.co.id/pc/preview?session=$sessionId",
        ],
    ];

    $context = stream_context_create($options);

    $sessionIdData = file_get_contents($sessionIdData, false, $context);

    // Decode JSON string into an array
    $sessionIdData = json_decode($sessionIdData, true);

    if ($sessionIdData) {
        if ($sessionIdData['err_code'] === 0 && $sessionIdData['data'] && $sessionIdData['data']['session']) {
            $titleLIVE = $sessionIdData['data']['session']['title'];
            $sellerId = $sessionIdData['data']['session']['uid'];
            $usernameId = $sessionIdData['data']['session']['username'];
            $sessionId = $sessionIdData['data']['session']['session_id'];
            $chatroomId = $sessionIdData['data']['session']['chatroom_id'];
            $deviceId = $sessionIdData['data']['session']['device_id'];
            $usersig = $sessionIdData['data']['usersig'];

            echo PHP_EOL . '===| SESSION INFO |===' . PHP_EOL;
            echo 'TITLE LIVE: ' . $titleLIVE . PHP_EOL;
            echo 'SELLER NAME: ' . $usernameId . PHP_EOL;
            echo 'SELLER ID: ' . $sellerId . PHP_EOL;

            echo PHP_EOL . '===| LIVE INFO |===' . PHP_EOL;
            echo 'UUID / DEVICEID LIVE: ' . $deviceId . PHP_EOL;
            echo 'CHATROOM LIVE: ' . $chatroomId . PHP_EOL;
            echo 'SESSION LIVE: ' . $sessionId . PHP_EOL;
            echo 'USERSIG LIVE: ' . $usersig . PHP_EOL . PHP_EOL;

            getData();

            // Return the session ID for further use
        } else {
            echo 'Error getting session ID: ' . $sessionIdData['err_msg'] . PHP_EOL;
        }
    } else {
        echo 'Error decoding JSON data.' . PHP_EOL;
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

//ban user
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
            echo " ( " . $responseData['data']['message_id'] . " )" . PHP_EOL . "MESSAGE BOT: $message" . PHP_EOL . PHP_EOL;
        }
    }
}

//auto komen + ban filter kata-kata
function checkMessage()
{
    global $chatroomId, $deviceId, $cookies, $processedMessages, $bannedUsers, $sessionId, $user, $lastBotMessageID, $userLastResponseTime, $sellerId;

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
            echo "===| NEW MESSAGE |===\n";
            echo "TIME: " . $date . "\n";
            echo "UID: " . $message['uid'] . "\n";
            echo "NAMA: " . $message['display_name'] . " ( " . $message['nickname'] . " )\n";
            echo "MESSAGE: " . $message['content'] . "\n";
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
                echo "===| NEW MESSAGE |===\n";
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
            $produkItem = "ID: " . $NoProduk['item_id'] . "\nNAMA: " . $NoProduk['name'];

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
    global $cookies, $sessionId, $DataItem, $acakNomorProduk, $produkItem, $totalProduk, $jedaSleep;
    // Panggil fungsi getItemData() terlebih dahulu

    $sessionIdData = "https://live.shopee.co.id/webapi/v1/session/$sessionId/show";

    $data = [
        'item' => $DataItem, // Ubah $DataItem menjadi array
    ];

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n" .
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
            echo "SET PIN ETALASE NO " . $acakNomorProduk + 1 . "\n$produkItem\n";
            echo "STATUS PIN PRODUK: ";
            sleep(60);
            echo strtoupper($statusPinProduk) . " MENAMPILKAN ETALASE NO " . $acakNomorProduk + 1 . "!!\n\n";

            // Return the session ID for further use
        } else {
            echo 'Error getting session ID: ' . $sessionIdData['err_msg'] . PHP_EOL;
        }
    } else {
        echo 'Error decoding JSON data.' . PHP_EOL;
    }
}

<?php
date_default_timezone_set('Asia/Jakarta');

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


echo "SEDANG MENGAMBIL DATA LIVE..." . PHP_EOL;
echo "LOGIN WITH KEY: $key" . PHP_EOL;

// INI JUGA
getRMTP();

function getData()
{
    global $cookies, $key, $sessionId, $deviceId, $sellerId, $chatroomId, $shareurl, $usersig, $usernameId;
    $getSession = api("https://api-shopee.mas.mba/index1.php?key=$key&cookies=" . urlencode($cookies));
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
        echo "ERROR GET DATA!! MSG: " . (strtoupper($sessionData['err_msg'] ?? 'Unknown Error'));
        exit(1);
    }
}


function getRMTP()
{
    getData();
    global $cookies, $key, $sessionId;
    $dataRMTP = api("https://api-shopee.mas.mba/rmtp.php?key=$key&sessionid=$sessionId&cookies=" . urlencode($cookies));
    $RMTPdata = json_decode($dataRMTP, true);
    if ($RMTPdata === null && json_last_error() !== JSON_ERROR_NONE) {
        echo $dataRMTP;
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
        echo 'ERROR GET DATA!! MSG : ' . strtoupper($RMTPdata['err_msg']) . PHP_EOL;
        exit(1);
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

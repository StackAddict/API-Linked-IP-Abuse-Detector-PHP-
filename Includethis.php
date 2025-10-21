<?php
function getClientIp() {
    $ipKeys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            if ($key === 'HTTP_X_FORWARDED_FOR') {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            } elseif (filter_var($_SERVER[$key], FILTER_VALIDATE_IP)) {
                return $_SERVER[$key];
            }
        }
    }
    
    return '0.0.0.0';
}
function isIpAbusive($ipAddress, $apiKey = 'API KEY HERE') {   #API Key goes here. Dont forget !
    if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
        return ['error' => true, 'message' => 'Invalid IP address format']; 
    }
    $url = 'https://api.abuseipdb.com/api/v2/check';
    $params = [
        'ipAddress' => $ipAddress,
        'maxAgeInDays' => '90',
        'verbose' => true
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Key: ' . $apiKey,
        'Accept: application/json'
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($response === false || $httpCode !== 200) {
        return ['error' => true, 'message' => 'API request failed', 'http_code' => $httpCode];
    }
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => true, 'message' => 'Invalid API response'];
    }
    if (isset($data['data']['abuseConfidenceScore']) && $data['data']['abuseConfidenceScore'] > 60) {
        return [
            'error' => false,
            'isAbusive' => true,
            'confidenceScore' => $data['data']['abuseConfidenceScore'],
            'details' => $data['data']
        ];
    }

    return [
        'error' => false,
        'isAbusive' => false,
        'confidenceScore' => $data['data']['abuseConfidenceScore'] ?? 0,
        'details' => $data['data'] ?? []
    ];
}
$clientIp = getClientIp();
if (!isset($_SESSION['ip_check_result']) || $_SESSION['ip_check_ip'] !== $clientIp) {
    $result = isIpAbusive($clientIp);
    $_SESSION['ip_check_result'] = $result;
    $_SESSION['ip_check_ip'] = $clientIp;
} else {
    $result = $_SESSION['ip_check_result'];
}
if ($result['error']) {
    echo 'Error: ' . $result['message'];
} else {
    if ($result['isAbusive']) {
        header('Location: Honeypot/Banning Script Here'); #PLACE HOLDER <--- Change to whatever you want
            exit;
    } else {
    }
}
?>

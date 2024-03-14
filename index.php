<?php

function send_telegram_message($domains, $telegram_bot_token, $telegram_chat_id) {
    $message = '';
    foreach ($domains as $domain) {
        if ($domain['expires'] == NULL) {
            $message .= "<b>Attention❗❗❗</b> https://{$domain['name']} is blocked!!!\n";
        } else {
            $message .= "<b>Attention❗❗❗</b> https://{$domain['name']} expires {$domain['expires']}!!!\n";
        }
    }
    file_get_contents("https://api.telegram.org/bot{$telegram_bot_token}/sendMessage?chat_id={$telegram_chat_id}&text={$message}&parse_mode=HTML");
}

function get_domains($namecheap_api_key, $namecheap_username, $client_ip, $telegram_bot_token, $telegram_chat_id, $page=1) {
    $domains = [];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.namecheap.com/xml.response?ApiUser={$namecheap_username}&ApiKey={$namecheap_api_key}&UserName={$namecheap_username}&Command=namecheap.domains.getList&ClientIp={$client_ip}&ListType=ALL&PageSize=100&page={$page}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $xml = simplexml_load_string($response);
    $json = json_encode($xml);
    $array = json_decode($json, TRUE);
    
    if($array["@attributes"]["Status"] == "OK") {
        foreach($array["CommandResponse"]["DomainGetListResult"]["Domain"] as $domain) {
            if($domain["@attributes"]["IsLocked"] == "true") {
                $blocked = json_decode(file_get_contents("blocked.txt"), true);
                if(!in_array($domain["@attributes"]["Name"], $blocked)) {
                    $blocked[] = $domain["@attributes"]["Name"];
                    file_put_contents("blocked.txt", json_encode($blocked));
                    $domains[] = ["name" => $domain["@attributes"]["Name"], "expires" => NULL];
                }
            }
            if(strtotime($domain["@attributes"]["Expires"]) - strtotime("now") <= 259200 && $domain["@attributes"]["IsExpired"] == "false") {
                $domains[] = ["name" => $domain["@attributes"]["Name"], "expires" => $domain["@attributes"]["Expires"]];
            }
        }
        if(intval($array["CommandResponse"]["Paging"]["TotalItems"] / 100) > $array["CommandResponse"]["Paging"]["CurrentPage"]) {
            return array_merge($domains, get_domains($namecheap_api_key, $namecheap_username, $client_ip, $telegram_bot_token, $telegram_chat_id, $page + 1));
        }
    }
    return $domains;
}

// Get domains and send telegram message
$domains = get_domains("api_token", "username", "client_ip", "telegram_token", "telegram_chat_id");
if (!empty($domains)) {
    send_telegram_message($domains, "telegram_token", "telegram_chat_id");
}
?>

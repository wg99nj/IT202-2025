<?php
// UCID: wg99 | Date: 2025-07-21
// Flexible CountryWise API fetcher

require_once(__DIR__ . "/load_api_keys.php"); 

function fetch_countrywise_data($country = "all", $fields = "name") {
    global $API_KEYS; 
    $apiKey = $API_KEYS["COUNTRYWISE_API_KEY"] ?? null;

    if (!$apiKey) {
        return ["error" => "API key is missing. Please set COUNTRYWISE_API_KEY in your .env or Heroku config vars."];
    }

    $url = "https://countrywise.p.rapidapi.com/?country=" . urlencode($country) . "&fields=" . urlencode($fields);
    $headers = [
        "x-rapidapi-host: countrywise.p.rapidapi.com",
        "x-rapidapi-key: " . $apiKey
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($err) {
        return ["error" => $err];
    } elseif ($httpStatus !== 200) {
        return ["error" => "API call failed with status code $httpStatus"];
    } else {
        return json_decode($response, true);
    }
}
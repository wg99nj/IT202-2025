<?php
require(__DIR__ . "/../../partials/nav.php");

// UCID: wg99 | Date: 2025-07-14
// This code fetches country data from the CountryWise API using a key stored in .env or Heroku config vars.
// It demonstrates how to securely call a RapidAPI endpoint and display the result for a given country.

$result = [];
$apiKey = getenv('COUNTRYWISE_API_KEY'); // Or use your .env parser

if (isset($_GET["country"])) {
    $country = $_GET["country"];
    $url = "https://countrywise.p.rapidapi.com/?country=" . urlencode($country) . "&fields=name";
    $headers = [
        "x-rapidapi-host: countrywise.p.rapidapi.com",
        "x-rapidapi-key: " . $apiKey // API key is never shown in output
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        $result = ["error" => $err];
    } else {
        $result = json_decode($response, true);
    }
}
?>
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
<div class="container" style="max-width:600px;margin:auto;padding:2em;">
    <h2>CountryWise API Test</h2>
    <form method="get" action="">
        <label for="country">Enter Country Name:</label>
        <input type="text" id="country" name="country" value="<?php echo isset($_GET['country']) ? htmlspecialchars($_GET['country']) : ''; ?>" required>
        <button type="submit">Fetch Country Data</button>
    </form>
    <hr>
    <h3>Result:</h3>
    <pre style="background:#f4f4f4;padding:1em;border-radius:5px;">
<?php
if (!empty($result)) {
    echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT));
} else {
    echo "No data yet. Submit a country name above.";
}
?>
    </pre>
</div>
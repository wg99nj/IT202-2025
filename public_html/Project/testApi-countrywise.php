<?php
require(__DIR__ . "/../../partials/nav.php");

// UCID: wg99 | Date: 2025-07-14

$result = [];
$apiKey = getenv('COUNTRYWISE_API_KEY');

if (!$apiKey) {
    $result = ["error" => "API key is missing. Please set COUNTRYWISE_API_KEY in your environment."];
} elseif (isset($_GET["country"])) {
    $country = $_GET["country"];
    $url = "https://countrywise.p.rapidapi.com/?country=" . urlencode($country) . "&fields=name";

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
        $result = ["error" => $err];
    } elseif ($httpStatus !== 200) {
        $result = ["error" => "API call failed with status code $httpStatus"];
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

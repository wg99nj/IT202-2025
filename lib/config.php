<?php

$ini = @parse_ini_file(".env");

if ($ini && isset($ini["JAWSDB_URL"])) {
    // Load from .env file (local development)
    $url = $ini["JAWSDB_URL"];
    $db_url = parse_url($url);
} else {
    // Load from Heroku environment variable
    $url = getenv("JAWSDB_URL");
    $db_url = parse_url($url);
}

// If parse_url fails (sometimes due to special characters), fallback with regex
if (!$db_url || count($db_url) === 0) {
    $matches = [];
    $pattern = "/mysql:\/\/(.*?):(.*?)@(.*?):(\d+)\/(.*)/i";
    preg_match($pattern, $url, $matches);
    if (count($matches) === 6) {
        $db_url = [
            "user" => $matches[1],
            "pass" => $matches[2],
            "host" => $matches[3],
            "port" => $matches[4],
            "path" => "/" . $matches[5]
        ];
    }
}

if (!$db_url || count($db_url) === 0) {
    error_log("
    Failed to load environment variables.
    If this is localhost ensure the .env file is created, in the proper location, has content, and is saved.
    If this is deployed ensure your platform's environment/config variables are set.
        On Heroku that'd be under the app's Settings -> Reveal Config Vars
    ");
    throw new Exception("Config parsing error, check the logs for details.");
} else {
    $dbhost     = $db_url["host"] ?? "localhost";
    $dbuser     = $db_url["user"] ?? "root";
    $dbpass     = $db_url["pass"] ?? "";
    $dbdatabase = isset($db_url["path"]) ? ltrim($db_url["path"], "/") : "";
}
?>

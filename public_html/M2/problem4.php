<?php

require_once "base.php";

$ucid = "wg99"; // <-- set your ucid

// Don't edit the arrays below, they are used to test your code
$array1 = ["hello world!", "php programming", "special@#$%^&characters", "numbers 123 456", "mIxEd CaSe InPut!"];
$array2 = ["hello world", "php programming", "this is a title case test", "capitalize every word", "mixEd CASE input"];
$array3 = ["  hello   world  ", "php    programming  ", "  extra    spaces  between   words   ",
    "      leading and trailing spaces      ", "multiple      spaces"];
$array4 = ["hello world", "php programming", "short", "a", "even"];

function transformText($arr, $arrayNumber) {
    printArrayInfoBasic($arr, $arrayNumber);

    foreach ($arr as $index => $text) {
        // Step 1: Remove non-alphanumeric characters except spaces
        $text = preg_replace("/[^a-zA-Z0-9 ]/", "", $text);

        // Step 2: Trim and reduce multiple spaces to one
        $text = preg_replace("/\s+/", " ", trim($text));

        // Step 3: Convert to Title Case
        $placeholderForModifiedPhrase = ucwords(strtolower($text));

        // Step 4: Get middle 3 characters (not first/last)
        $cleaned = str_replace(" ", "", $placeholderForModifiedPhrase); // no spaces for middle char calc
        $length = strlen($cleaned);
        if ($length < 3) {
            $placeholderForMiddleCharacters = "Not enough characters";
        } else {
            $middle = intdiv($length, 2);
            $start = max(1, $middle - 1); // avoid first char
            $placeholderForMiddleCharacters = substr($cleaned, $start, 3);
        }

        echo "<div>";
        printStringTransformations($index, $placeholderForModifiedPhrase, $placeholderForMiddleCharacters);
        echo "</div>";
    }

    echo "<br>______________________________________<br>";
}

// Run the problem
printHeader($ucid, 4);
transformText($array1, 1);
transformText($array2, 2);
transformText($array3, 3);
transformText($array4, 4);
printFooter($ucid, 4);
?>
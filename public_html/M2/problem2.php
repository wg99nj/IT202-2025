<?php

require_once "base.php";

$ucid = "wg99"; // <-- set your UCID

// Don't edit the arrays below, they are used to test your code
$array1 = [0.1, 0.2, 0.3, 0.4, 0.5, 0.6];
$array2 = [1.0000001, 1.0000002, 1.0000003, 1.0000004, 1.0000005];
$array3 = [1.0 / 3.0, 2.0 / 3.0, 4.0 / 3.0, 8.0 / 3.0, 8.0 / 3.0];
$array4 = [1e16, 1.0, -1e16, 2.0, -2.0, 1e-16];
$array5 = [M_PI, M_E, sqrt(2), sqrt(3), sqrt(5), log(2), log10(3)];

function sumValues($arr, $arrayNumber)
{
    // Only make edits between the designated "Start" and "End" comments
    printArrayInfoDouble($arr, $arrayNumber);

    // Challenge 1: Sum all the values of the passed in array and assign to `total`
    // Challenge 2: Have the sum be represented as a number with exactly 2 decimal places, assign to `modifiedTotal`
    // Example: 0.1 would be shown as 0.10, 1 would be shown as 1.00, etc

    // Start Solution Edits
    // UCID: wg99
    // Date: 2025-06-08
    // Step 1: Loop through the array and calculate the total sum of all values
    // Step 2: Assign the sum to the variable $total
    // Step 3: Format the total to exactly 2 decimal places using number_format or sprintf
    // Step 4: Store the formatted value into $modifiedTotal

    $total = 0;
    foreach ($arr as $num) {
        $total += $num;
    }

    $modifiedTotal = number_format($total, 2);
    // End Solution Edits

    echo "<p>Total Raw Value: {$total}</p>";
    echo "<p>Total Modified Value: {$modifiedTotal}</p>";
    echo "______________________________________<br>";
}

// Run the problem
printHeader($ucid, 2);
sumValues($array1, 1);
sumValues($array2, 2);
sumValues($array3, 3);
sumValues($array4, 4);
sumValues($array5, 5);
printFooter($ucid, 2);
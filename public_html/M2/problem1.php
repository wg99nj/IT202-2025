<?php

require_once "base.php";

$ucid = "wg99"; // UCID: wg99

// Don't edit the arrays below, they are used to test your code
$array1 = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
$array2 = [9, 8, 7, 6, 5, 4, 3, 2, 1, 0];
$array3 = [0, 0, 1, 1, 2, 2, 3, 3, 4, 4, 5, 5, 6, 6, 7, 7, 8, 8, 9, 9];
$array4 = [9, 9, 8, 8, 7, 7, 6, 6, 5, 5, 4, 4, 3, 3, 2, 2, 1, 1, 0, 0];

function printOdds($arr, $arrayNumber)
{
    printArrayInfo($arr, $arrayNumber);

    // UCID: wg99 | Date: 2025-06-08
    // PLAN:
    // 1. Loop through the array or use array_filter to isolate odd numbers.
    // 2. Check if a number is odd using ($num % 2 != 0)
    // 3. Collect the odd numbers into a new array.
    // 4. Use implode() to output them as a single comma-separated line.

    echo "Output Array: ";
    // Start Solution Edits

    $odds = array_filter($arr, fn($num) => $num % 2 !== 0);
    echo implode(", ", $odds);

    // End Solution Edits
    echo "<br>______________________________________<br>";
}

// Run the problem
printHeader($ucid, 1);
printOdds($array1, 1);
printOdds($array2, 2);
printOdds($array3, 3);
printOdds($array4, 4);
printFooter($ucid, 1);

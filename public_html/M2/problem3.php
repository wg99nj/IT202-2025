<?php

require_once "base.php";

$ucid = "wg99"; // <-- set your UCID

// Don't edit the arrays below, they are used to test your code
$array1 = [42, -17, 89, -256, 1024, -4096, 50000, -123456];
$array2 = [3.14159265358979, -2.718281828459, 1.61803398875, -0.5772156649, 0.0000001, -1000000.0];
$array3 = [1.1, -2.2, 3.3, -4.4, 5.5, -6.6, 7.7, -8.8];
$array4 = ["123", "-456", "789.01", "-234.56", "0.00001", "-99999999"];
$array5 = [-1, 1, 2.0, -2.0, "3", "-3.0"];

function bePositive($arr, $arrayNumber)
{
    // Only make edits between the designated "Start" and "End" comments
    printArrayInfoMixed($arr, $arrayNumber);

    // Challenge 1: Make each value positive
    // Challenge 2: Convert the values back to their original data type and assign it to the proper slot of the `output` array
    // UCID: wg99 | Date: 2025-06-08
    // Plan:
    // Step 1: Loop through each value in the array
    // Step 2: Get the original type of the value
    // Step 3: Convert the value to its positive version (e.g., use abs())
    // Step 4: Convert the value back to the original type (int, float, string)
    // Step 5: Save it in the output array

    $output = array_fill(0, count($arr), null); // Initialize output array
    // Start Solution Edits

    foreach ($arr as $i => $value) {
        $originalType = gettype($value);
        $positive = abs($value);

        switch ($originalType) {
            case "integer":
                $output[$i] = (int)$positive;
                break;
            case "double":
                $output[$i] = (float)$positive;
                break;
            case "string":
                $output[$i] = (string)$positive;
                break;
            default:
                $output[$i] = null;
        }
    }

    // End Solution Edits
    echo "<span>Output: </span>";
    printOutputWithType($output);
    echo "<br>______________________________________<br>";
}

// Run the problem
printHeader($ucid, 3);
bePositive($array1, 1);
bePositive($array2, 2);
bePositive($array3, 3);
bePositive($array4, 4);
bePositive($array5, 5);
printFooter($ucid, 3);
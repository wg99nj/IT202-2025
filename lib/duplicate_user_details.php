<?php
function users_check_duplicate($errorInfo)
{
    if (property_exists($errorInfo, 'errorInfo')) {
        // extract errorInfo in case a PDOException is passed
        $errorInfo = $errorInfo->errorInfo;
    }
    if (!is_array($errorInfo) || count($errorInfo) < 3) {
        flash("Unknown error occurred", "danger");
        error_log("Error interpreting PDOException message: " . var_export($errorInfo, true));
        return;
    }
    if ($errorInfo[1] === 1062) {
        // Updated regex to match lowercase table and column names
        preg_match("/users\.(\w+)/i", $errorInfo[2], $matches);
        if (isset($matches[1])) {
            flash("The chosen " . $matches[1] . " is not available.", "warning");
        } else {
            flash("Unknown error occurred", "danger");
            error_log("Error interpreting PDOException message: " . var_export($errorInfo, true));
        }
    } else {
        flash("Unhandled error occurred", "danger");
        error_log("Error updating email/username: " . var_export($errorInfo, true));
    }
}
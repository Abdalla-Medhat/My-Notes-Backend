<?php

// Sanitize user input
function requestFilter($request){
    return htmlspecialchars(strip_tags($_POST[$request]));
} 

// Handle image upload to the server
function uploadImg($request){
    // Return null if no file was uploaded or an upload error occurred
    if (!isset($_FILES[$request]) || $_FILES[$request]['error'] === UPLOAD_ERR_NO_FILE){
        return null;
    }

    // Define 1 MB in bytes
    define("MB", 1048576);
    global $errormsg;

    // Generate a unique file name to avoid overwriting existing files
    $imageName = uniqid() . "_" . $_FILES[$request]['name'];
    $imageSize = $_FILES[$request]['size']; // Retrieve file size
    $imageTemp = $_FILES[$request]['tmp_name']; // Retrieve temporary file path
    $imageError = $_FILES[$request]['error']; // Retrieve upload error code
    $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');

    // Extract file extension
    $tranlated = explode('.', $imageName);
    $extension = strtolower(end($tranlated));

    // Check potential upload errors
    if ($imageError === UPLOAD_ERR_INI_SIZE) {
        $errormsg[] = 'File size exceeds the server limit';
    } elseif ($imageError === UPLOAD_ERR_FORM_SIZE) {
        $errormsg[] = 'File size exceeds the form limit';
    } elseif ($imageError !== UPLOAD_ERR_OK) {
        $errormsg[] = 'Unknown upload error (Code ' . $imageError . ')';
    }

    // Ensure file size does not exceed 2 MB
    if($imageSize > 2 * MB){
        $errormsg[] = 'Image size exceeds 2 MB';
    }

    // Validate file extension
    if (!in_array($extension, $allowedTypes) && !empty($imageName)) {
        $errormsg[] = 'Invalid image type';
    }

    // If no errors found, upload file
    if(empty($errormsg)){
        move_uploaded_file($imageTemp, "../images/".$imageName);
        return $imageName;
    } else {
        // Return error list if upload failed
        return array("status" => "failed", "error" => $errormsg);
    }
}

// Function to delete an image from the server
function deleteImage($dir, $imgName){
    // If file exists, delete it
    if(file_exists($dir . "/" . $imgName )){
        unlink($dir . "/" . $imgName);
        return true;
    }
    return false;
}

// Display image (updated version)
function displayImage($imageName) {
    // Clean image name for security using <basename function>
    $imageName = basename($imageName);
    $imagePath = __DIR__ . '/images/' . $imageName;

    // If image exists, display it
    if(file_exists($imagePath) && is_file($imagePath)){
        // Detect content type based on file extension
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        $contentTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];

        $contentType = $contentTypes[$extension] ?? 'image/jpeg';
        header('Content-Type: ' . $contentType); // Set correct header so browser displays the image
        header('Cache-Control: max-age=3600');   // Cache image for 1 hour

        readfile($imagePath);
        return true;
    } else {
        // Image not found → return 404
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Image not found']);
        return false;
    }
}

// Authentication function
function checkAuthenticate(){
    $apiKey = $_SERVER['HTTP_API_KEY'] ?? '';
    $secretKey = "example";

    if($apiKey === $secretKey){
        return;
    } else {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status'=>'error','message'=>'Access denied - Invalid API Key']);
        exit;
    }
}

// Image display handler (without separate image.php)
if (isset($_GET['img'])) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: *');
    displayImage($_GET['img']);
    exit;
}

?>

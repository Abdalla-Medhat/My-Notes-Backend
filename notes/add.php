<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../connection.php';


//request data
$note_title = requestFilter('note_title');
$note_content = requestFilter('note_content');
$user_note = requestFilter('user_note');
$imgName = uploadImg('image');

// تحقق أولاً إذا كانت $imgName مصفوفة وتحوي status failed
if(is_array($imgName) && isset($imgName["status"]) && $imgName["status"] == 'failed') {
    $errors = $imgName["error"];
    if ($errors == ['Image size exceeds 2 MB']){
        echo json_encode(array("status" => "failed","error" => "Image size exceeds 2 MB"));
        exit();
    }elseif($errors == ["Invalid image type"]){
        echo json_encode(array("status" => "failed", "error" => "Invalid image type"));
        exit();
    }
    else{ echo json_encode(array("status" => "failed","error" => $errors));
        exit();
    }
   
}

if($imgName){$stmt = $con->prepare("INSERT INTO `notes` (`note_title`,`note_content`,`user_note`, `note_img`)
VALUES(?,?,?,?)");
$stmt->execute([$note_title,$note_content,$user_note,$imgName]);

}else{
    $stmt = $con->prepare("INSERT INTO `notes` (`note_title`,`note_content`,`user_note`)
VALUES(?,?,?)");
$stmt->execute([$note_title,$note_content,$user_note]);
}

//Check if data was inserted successfully (more than 1) else return failed
$status = $stmt->rowCount();
if($status > 0){
    echo json_encode(array("status" => "success")); 
}else{
    echo json_encode(array("status" => "failed"));
}

?>
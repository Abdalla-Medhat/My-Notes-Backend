<?php

// دالة تنقية البيانات من المستخدم
function requestFilter($request){
    return htmlspecialchars(strip_tags($_POST[$request]));
} 

// دالة رفع الصورة إلى السيرفر
function uploadImg($request){
    // إذا لم يتم رفع ملف أو فيه خطأ في الرفع، ارجع null
    if (!isset($_FILES[$request]) || $_FILES[$request]['error'] === UPLOAD_ERR_NO_FILE){
        return null;
    }
    
    // تعريف حجم 1 ميجابايت
    define("MB", 1048576);
    global $errormsg;
    
    // إنشاء اسم فريد للصورة علشان نتجنب التكرار
    $imageName = uniqid() . "_" . $_FILES[$request]['name'];
    $imageSize = $_FILES[$request]['size'];
    $imageTemp = $_FILES[$request]['tmp_name'];
    $imageError = $_FILES[$request]['error'];
    $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
    
    // استخراج امتداد الصورة
    $tranlated = explode('.', $imageName);
    $extension = end($tranlated);
    
    // فحص الأخطاء المحتملة في الرفع
    if ($imageError === UPLOAD_ERR_INI_SIZE) {
        $errormsg[] = 'حجم الملف أكبر من المسموح في السيرفر';
    } elseif ($imageError === UPLOAD_ERR_FORM_SIZE) {
        $errormsg[] = 'حجم الملف أكبر من المسموح في النموذج';
    } elseif ($imageError !== UPLOAD_ERR_OK) {
        $errormsg[] = 'خطأ غير معروف في الرفع (كود ' . $imageError . ')';
    }
    
    // التحقق من أن حجم الصورة لا يتعدى 2 ميجابايت
    if($imageSize > 2 * MB){
        $errormsg[] = 'حجم الصورة أكبر من 2 ميجابايت';
    }
    
    // التحقق من نوع الصورة المسموح به
    if (!in_array($extension, $allowedTypes) && !empty($imageName)) {
        $errormsg[] = 'نوع الصورة غير مسموح به';
    }
    
    // إذا لا توجد أخطاء، قم برفع الصورة
    if(empty($errormsg)){
        move_uploaded_file($imageTemp, "../images/".$imageName);
        return $imageName;
    } else {
        // إذا كانت هناك أخطاء، ارجع مصفوفة بالأخطاء
        return array("status" => "failed", "error" => $errormsg);
    }
}

// دالة حذف الصورة من السيرفر
function deleteImage($dir, $imgName){
    // إذا الملف موجود، قم بحذفه
    if(file_exists($dir . "/" . $imgName )){
        unlink($dir . "/" . $imgName);
        return true;
    }
    return false;
}

// دالة عرض الصورة - الجديدة
function displayImage($imageName) {
    // تنظيف اسم الصورة للحماية
    $imageName = basename($imageName);
$imagePath = __DIR__ . '/images/' . $imageName; // صح    
    // إذا الصورة موجودة، اعرضها
    if(file_exists($imagePath) && is_file($imagePath)){
        // تحديد نوع المحتوى بناءً على امتداد الصورة
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        $contentTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg', 
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        $contentType = $contentTypes[$extension] ?? 'image/jpeg';
        header('Content-Type: ' . $contentType); //يحدد هيدر الاستجابة علشان المتصفح يعرف يعرض الصورة.
        header('Cache-Control: max-age=3600'); //يخبر المتصفح أو البروكسي إن الصورة قابلة للتخزين مؤقتًا لمدة 3600 ثانية (ساعة)
        
        readfile($imagePath);
        return true;
    } else {
        // إذا الصورة مش موجودة، ارجع خطأ 404
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Image not found']);
        return false;
    }
}

// دالة المصادقة
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

// إذا كان الطلب مباشر لعرض صورة (بدون الحاجة لملف image.php منفصل)
if (isset($_GET['img'])) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: *');
    displayImage($_GET['img']);
    exit;
}

?>
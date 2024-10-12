<?php 
session_start();
include('server.php');

$errors = array();

if (isset($_POST['reg_user'])) {
    $username = cleanInput($_POST['username']);
    $email = cleanInput($_POST['email']);
    $password_1 = cleanInput($_POST['password_1']);
    $password_2 = cleanInput($_POST['password_2']);
    $phone = cleanInput($_POST['phone']);
    $firstname = cleanInput($_POST['firstname']);
    $lastname = cleanInput($_POST['lastname']);

    // ตรวจสอบความถูกต้องของข้อมูล
    if (empty($username)) {
        array_push($errors, "กรุณากรอกชื่อผู้ใช้");
    }
    if (empty($email)) {
        array_push($errors, "กรุณากรอกอีเมล");
    }
    if (empty($password_1)) {
        array_push($errors, "กรุณากรอกรหัสผ่าน");
    }
    if ($password_1 != $password_2) {
        array_push($errors, "รหัสผ่านไม่ตรงกัน");
    }
    if (empty($phone)) {
        array_push($errors, "กรุณากรอกเบอร์โทรศัพท์");
    }
    if (empty($firstname)) {
        array_push($errors, "กรุณากรอกชื่อจริง");
    }
    if (empty($lastname)) {
        array_push($errors, "กรุณากรอกนามสกุล");
    }

    // จัดการรูปโปรไฟล์
    $profile_image = 'http://26.120.114.83/test/ATJ/uploads/profile_images/default.png'; // ค่าเริ่มต้น
    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $uploaded_image = uploadProfileImage($_FILES['profile_image']);
        if($uploaded_image !== false) {
            $profile_image = $uploaded_image;
        } else {
            array_push($errors, "เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ");
        }
    }

    // ตรวจสอบว่ามีผู้ใช้หรืออีเมลนี้ในระบบแล้วหรือไม่
    $user = userExists($username, $email);

    if ($user) {
        if ($user['username'] === $username) {
            array_push($errors, "ชื่อผู้ใช้นี้มีอยู่แล้ว");
        }
        if ($user['email'] === $email) {
            array_push($errors, "อีเมลนี้มีอยู่แล้ว");
        }
    }

    // ถ้าไม่มีข้อผิดพลาด บันทึกข้อมูลลงฐานข้อมูล
    if (count($errors) == 0) {
        if (addUser($username, $email, $password_1, $firstname, $lastname, $phone, $profile_image)) {
            $_SESSION['username'] = $username;
            $_SESSION['success'] = "คุณได้ลงทะเบียนเรียบร้อยแล้ว";
            header('location: login.php');
            exit();
        } else {
            array_push($errors, "เกิดข้อผิดพลาดในการลงทะเบียน กรุณาลองใหม่อีกครั้ง");
        }
    }

    if (count($errors) > 0) {
        $_SESSION['errors'] = $errors;
        header('location: register.php');
        exit();
    }
}
?>
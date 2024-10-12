<?php
session_start();

// ตรวจสอบว่ามีการล็อกอินอยู่หรือไม่
if (!isset($_SESSION['user_id'])) {
    // ถ้าไม่มีการล็อกอิน ให้ redirect ไปยังหน้า welcome
    header("Location: welcome.php");
    exit();
}

// ถ้ามีการล็อกอิน ดำเนินการล็อกเอาท์
// ล้างข้อมูลทั้งหมดใน session
$_SESSION = array();

// ทำลาย session
session_destroy();

// ป้องกันการแคชหน้าเว็บ
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// ส่งผู้ใช้กลับไปยังหน้า welcome
echo '<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, "welcome.php");
    }
    window.location.href = "welcome.php";
</script>';
exit();
?>

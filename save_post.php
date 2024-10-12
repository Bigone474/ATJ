<?php
session_start();
include('server.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $image_url = isset($_POST['image']) ? $_POST['image'] : null;

    // บันทึกข้อมูลลงในฐานข้อมูล
    $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, category, subcategory, image_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $title, $content, $category, $subcategory, $image_url);

    if ($stmt->execute()) {
        $post_id = $stmt->insert_id;
        echo json_encode(['success' => true, 'post_id' => $post_id]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>

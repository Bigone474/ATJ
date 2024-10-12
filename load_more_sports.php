<?php
session_start();
require_once 'server.php';

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$subcategory = isset($_GET['subcategory']) ? $_GET['subcategory'] : null;

function getLatestSportsPosts($pdo, $limit = 3, $offset = 0, $subcategory = null) {
    $sql = "SELECT p.*, u.username 
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.category = 'sports'";
    
    if ($subcategory) {
        $sql .= " AND p.subcategory = :subcategory";
    }
    
    $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    
    // ผูกค่าพารามิเตอร์
    if ($subcategory) {
        $stmt->bindParam(':subcategory', $subcategory, PDO::PARAM_STR);
    }
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$latest_posts = getLatestSportsPosts($pdo, 3, $offset, $subcategory);

foreach ($latest_posts as $post):
?>
    <div class="col-md-4 mb-4">
        <div class="card">
            <?php if (!empty($post['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
            <?php else: ?>
                <img src="https://via.placeholder.com/400x200" class="card-img-top" alt="Placeholder">
            <?php endif; ?>
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                <p class="card-text"><?php echo substr(htmlspecialchars($post['content']), 0, 100) . '...'; ?></p>
                <a href="view_post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">อ่านเพิ่มเติม</a>
            </div>
        </div>
    </div>
<?php endforeach; ?>

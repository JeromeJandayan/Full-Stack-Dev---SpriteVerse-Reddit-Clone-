<?php
session_start();
header('Content-Type: application/json');

// Prevent HTML warnings that break JSON
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config.php'; // Must define $pdo

// Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "User not logged in"
    ]);
    exit;
}

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['community_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing community_id"
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$community_id = intval($data['community_id']);

try {
    // Prepare DELETE query with PDO
    $stmt = $pdo->prepare("
        DELETE FROM community_members
        WHERE user_id = :user_id AND community_id = :community_id
    ");

    $stmt->execute([
        ':user_id' => $user_id,
        ':community_id' => $community_id
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Left community successfully"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>

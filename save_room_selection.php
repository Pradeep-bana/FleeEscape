<?php
session_start();
include("admin/db.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selection'])) {
    $selection = trim($_POST['selection']);
    $sessionId = session_id();

    try {
        // Check latest cart entry for this session
        $stmt = $pdo->prepare("SELECT id FROM tbl_carts WHERE session_id = :sid ORDER BY id DESC LIMIT 1");
        $stmt->execute([':sid' => $sessionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $cartId = $row['id'];
            $update = $pdo->prepare("UPDATE tbl_carts SET escape_selection = :sel WHERE id = :id");
            $update->execute([
                ':sel' => $selection,
                ':id'  => $cartId
            ]);

            echo json_encode(['status' => 'success']);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No cart entry found']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
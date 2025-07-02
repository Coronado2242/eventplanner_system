<?php
session_start();
$conn = new mysqli("localhost", "root", "", "eventplanner");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['receipt_file'], $_POST['proposal_id'])) {
    $proposal_id = intval($_POST['proposal_id']);
    $file = $_FILES['receipt_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../proposal/uploads/';
        $filename = uniqid('receipt_') . '_' . basename($file['name']);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Save filename in database
            $stmt = $conn->prepare("UPDATE sooproposal SET receipt_file = ? WHERE id = ?");
            $stmt->bind_param("si", $filename, $proposal_id);
            $stmt->execute();
            echo "Success";
            exit();
        }
    }
    echo "Error uploading file.";
}
?>

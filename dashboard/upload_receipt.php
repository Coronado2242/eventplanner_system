<?php
session_start();
$conn = new mysqli("localhost", "root", "", "eventplanner");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['receipt_file'], $_POST['proposal_id'])) {
    $proposal_id = intval($_POST['proposal_id']);
    $file = $_FILES['receipt_file'];

    // Determine department
    $stmt = $conn->prepare("SELECT department FROM sooproposal WHERE id = ?");
    $stmt->bind_param("i", $proposal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $department = $row['department'] ?? '';

    switch ($department) {
        case 'CCS':
            $return_page = 'ccssoo_dashboard.php';
            break;
        case 'CTE':
            $return_page = 'ctesoo_dashboard.php';
            break;
        default:
            $return_page = 'dashboard.php';
    }

    if ($file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../proposal/uploads/';
        $filename = uniqid('receipt_') . '_' . basename($file['name']);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $stmt = $conn->prepare("
                UPDATE sooproposal
                SET receipt_file = ?, financialstatus = 'Submitted', level = 'Financial Auditor'
                WHERE id = ?
            ");
            $stmt->bind_param("si", $filename, $proposal_id);
            $stmt->execute();

            header("Location: $return_page?upload=success");
            exit();
        }
    }

    header("Location: $return_page?upload=error");
    exit();
}
?>

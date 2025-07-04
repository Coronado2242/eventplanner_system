<?php
$conn = new mysqli("localhost", "root", "", "eventplanner");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_FILES['logo'])) {
    $filename = $_FILES['logo']['name'];
    $filepath = 'uploads/' . $filename;
    $date_uploaded = date('Y-m-d H:i:s');

    // Move uploaded file
    if (move_uploaded_file($_FILES['logo']['tmp_name'], $filepath)) {
        // Check if a logo already exists
        $check = $conn->query("SELECT * FROM site_logo LIMIT 1");
        if ($check->num_rows > 0) {
            // Delete old file
            $old = $check->fetch_assoc();
            if (file_exists($old['filepath'])) {
                unlink($old['filepath']);
            }
            // Update the row
            $stmt = $conn->prepare("UPDATE site_logo SET filepath=?, filename=?, uploaded_by=?, date_uploaded=? WHERE id=?");
            $stmt->bind_param("ssssi", $filepath, $filename, $uploaded_by, $date_uploaded, $old['id']);
        } else {
            // Insert new row
            $stmt = $conn->prepare("INSERT INTO site_logo (filepath, filename, uploaded_by, date_uploaded) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $filepath, $filename, $uploaded_by, $date_uploaded);
        }

        if ($stmt->execute()) {
            echo "Logo updated successfully.";
        } else {
            echo "Database error: " . $stmt->error;
        }
    } else {
        echo "Error uploading file.";
    }
} else {
    echo "No file uploaded.";
}
?>

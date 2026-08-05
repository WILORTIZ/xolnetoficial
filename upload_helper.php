<?php
// Password-protected upload helper
$password = "xcolnet123";
if (!isset($_POST['pw']) || $_POST['pw'] !== $password) {
    header('HTTP/1.1 401 Unauthorized');
    echo '<form method="POST" style="margin: 50px; text-align: center; font-family: sans-serif;">';
    echo '<h2>Xcolnet Secure Upload Gate</h2>';
    echo 'Password: <input type="password" name="pw" style="padding: 5px; margin: 10px;"><br>';
    echo '<input type="submit" value="Acceder" style="padding: 5px 15px;">';
    echo '</form>';
    exit;
}

if (isset($_FILES['file'])) {
    $target = isset($_POST['target']) && $_POST['target'] !== '' ? $_POST['target'] : basename($_FILES['file']['name']);
    
    // Ensure parent directories exist
    $dir = dirname($target);
    if ($dir !== '.' && $dir !== '/' && !is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        echo "SUCCESS:" . $target;
    } else {
        echo "ERROR: Failed to move uploaded file.";
    }
    exit;
}
?>
<form method="POST" enctype="multipart/form-data" style="margin: 50px; font-family: sans-serif;">
  <h2>Upload File to Server</h2>
  <input type="hidden" name="pw" value="xcolnet123">
  File: <input type="file" name="file" required><br><br>
  Target Path (e.g. .well-known/acme-challenge/test.txt): <input type="text" name="target" style="width: 300px;"><br><br>
  <input type="submit" value="Upload File">
</form>

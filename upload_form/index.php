<?php
$uploadDir   = __DIR__ . '/uploads/';
$maxSize     = 50 * 1024 * 1024;
$allowedExts = [
    'jpg', 'jpeg', 'png', 'gif',   
    'xls', 'xlsx',                
    'doc', 'docx'                 
];

$message = '';
$success = false;

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_FILES) && empty($_POST) && !empty($_SERVER['CONTENT_LENGTH'])) {
        $message = 'File is too large. Maximum allowed size is 50MB.';
    }
    elseif (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        $message = 'Please choose a file to upload.';
    }
    elseif ($_FILES['file']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['file']['error'] === UPLOAD_ERR_FORM_SIZE) {
        $message = 'File is too large. Maximum allowed size is 50MB.';
    }
    elseif ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $message = 'Upload failed. Please try again.';
    }
    else {
        $file     = $_FILES['file'];
        $origName = basename($file['name']);
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if ($file['size'] > $maxSize) {
            $message = 'File is too large. Maximum allowed size is 50MB.';
        }
        elseif (!in_array($ext, $allowedExts, true)) {
            $message = 'Invalid file type. Only Image (JPG, PNG, GIF), Excel (XLS, XLSX) and Word (DOC, DOCX) files are allowed.';
        }
        elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true) && @getimagesize($file['tmp_name']) === false) {
            $message = 'The selected file is not a valid image.';
        }
        else {
            $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
            $newName  = $safeName . '_' . uniqid() . '.' . $ext;

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
                $success = true;
                $message = 'File "' . htmlspecialchars($origName) . '" uploaded successfully!';
            } else {
                $message = 'Could not save the file. Check folder permissions.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Upload a File</h1>
        <p class="hint">Allowed: Image (JPG, PNG, GIF), Excel (XLS, XLSX), Word (DOC, DOCX)<br>Maximum size: 50MB</p>

        <?php if ($message !== ''): ?>
            <div class="message <?= $success ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data" id="uploadForm">
            <input type="hidden" name="MAX_FILE_SIZE" value="<?= $maxSize ?>">
            <input type="file" name="file" id="file"
                   accept=".jpg,.jpeg,.png,.gif,.xls,.xlsx,.doc,.docx" required>
            <button type="submit">Upload</button>
        </form>
    </div>

</body>
</html>

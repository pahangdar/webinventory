<?php
$dir = 'uploads/images/';
if (!file_exists($dir)) {
    if (mkdir($dir, 0777, true)) {
        echo 'Directory created successfully';
    } else {
        echo 'Failed to create directory. Check permissions.';
    }
} else {
    echo 'Directory already exists.';
}
?>

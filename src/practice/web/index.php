<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>cape list</title>
</head>
<body>
<?php
foreach (scandir(__DIR__) as $id => $file) {
    if (isset(pathinfo($file)["extension"]) and pathinfo($file)["extension"] !== "png") continue;
    echo "<img src='$file'>";
}
?>
</body>
</html>
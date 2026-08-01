<?php

$categoryFile = __DIR__ . "/../app/data/Categories.json";
$uploadDirectory = __DIR__ . "/../uploads/products";
$backupFile = __DIR__ . "/../app/data/Categories.before-upload-migration.json";

if (!file_exists($categoryFile)) {
    fwrite(STDERR, "Category file was not found.\n");
    exit(1);
}

$json = file_get_contents($categoryFile);
$categories = json_decode($json, true);
if (!is_array($categories)) {
    fwrite(STDERR, "Category JSON is invalid.\n");
    exit(1);
}

if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true)) {
    fwrite(STDERR, "Upload directory could not be created.\n");
    exit(1);
}

if (!file_exists($backupFile)) copy($categoryFile, $backupFile);

$migrated = 0;
$mimeExtensions = array("jpeg" => "jpg", "png" => "png", "webp" => "webp", "gif" => "gif");

foreach ($categories as &$category) {
    $image = isset($category["image"]) ? $category["image"] : "";
    if (!preg_match('#^data:image/(jpeg|png|webp|gif);base64,(.+)$#s', $image, $matches)) continue;

    $binary = base64_decode($matches[2], true);
    if ($binary === false) continue;

    $filename = "category-" . $category["id"] . "-" . bin2hex(random_bytes(8)) . "." . $mimeExtensions[$matches[1]];
    file_put_contents($uploadDirectory . "/" . $filename, $binary, LOCK_EX);
    $category["image"] = "/phpStoreSite/uploads/products/" . $filename;
    $migrated++;
}
unset($category);

file_put_contents($categoryFile, json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
echo "Migrated {$migrated} image(s).\n";

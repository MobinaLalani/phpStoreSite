<?php

require_once __DIR__ . "/../app/Controllers/CategoryController.php";
require_once __DIR__ . "/../app/Controllers/UploadController.php";

$method = $_SERVER["REQUEST_METHOD"];

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// حذف مسیر پروژه از URL لوکال
$basePath = rtrim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])), "/");
if ($basePath !== "" && $basePath !== "." && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}
$uri = preg_replace('#^/index\.php#', '', $uri);
$uri = '/' . trim($uri, '/');

$controller = new CategoryController();

if ($method === "POST" && $uri === "/upload") {
    (new UploadController())->store();
    exit;
}

if ($method === "GET" && $uri === "/categories") {

    $controller->index();

    exit;
}

if ($method === "GET" && preg_match('#^/categories/(\d+)$#', $uri, $matches)) {
    $controller->show((int) $matches[1]);
    exit;
}

if ($method === "POST" && $uri === "/categories") {
    $controller->store();
    exit;
}

if (($method === "PUT" || $method === "PATCH") && preg_match('#^/categories/(\d+)$#', $uri, $matches)) {
    $controller->update((int) $matches[1]);
    exit;
}

if ($method === "DELETE" && preg_match('#^/categories/(\d+)$#', $uri, $matches)) {
    $controller->destroy((int) $matches[1]);
    exit;
}

http_response_code(404);

echo json_encode([
    "message" => "Route not found"
]);

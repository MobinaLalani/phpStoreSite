<?php

require_once __DIR__ . "/../app/Controllers/CategoryController.php";

$method = $_SERVER["REQUEST_METHOD"];

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// حذف مسیر پروژه از URL لوکال
$uri = str_replace("/phpStoreSite", "", $uri);

$controller = new CategoryController();

if ($method == "GET" && $uri == "/categories") {

    $controller->index();

    exit;
}

http_response_code(404);

echo json_encode([
    "message" => "Route not found"
]);
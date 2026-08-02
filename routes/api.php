<?php

require_once __DIR__ . "/../app/Controllers/CategoryController.php";
require_once __DIR__ . "/../app/Controllers/UploadController.php";
require_once __DIR__ . "/../app/Controllers/ProductController.php";
require_once __DIR__ . "/../app/Controllers/AuthController.php";
require_once __DIR__ . "/../app/Helpers/Auth.php";
require_once __DIR__ . "/../app/Controllers/SettingsController.php";
require_once __DIR__ . "/../app/Controllers/InquiryController.php";

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
$productController = new ProductController();
$authController = new AuthController();
$settingsController = new SettingsController();
$inquiryController = new InquiryController();

if ($method === "POST" && $uri === "/auth/login") { $authController->login(); exit; }
if ($method === "GET" && $uri === "/auth/me") { $authController->me(); exit; }
if ($method === "PUT" && $uri === "/auth/password") { $authController->changePassword(); exit; }
if ($method === "POST" && $uri === "/auth/logout-all") { $authController->logoutAll(); exit; }
if ($method === "GET" && $uri === "/admin/security/activity") { $authController->activity(); exit; }

if ($method === "GET" && $uri === "/settings/public") { $settingsController->publicIndex(); exit; }
if ($method === "GET" && $uri === "/admin/settings") { Auth::requireUser(); $settingsController->index(); exit; }
if ($method === "PUT" && $uri === "/admin/settings") { Auth::requireUser(); $settingsController->update(); exit; }

if ($method === "POST" && $uri === "/inquiries") { $inquiryController->store(); exit; }
if ($method === "GET" && $uri === "/admin/inquiries") { Auth::requireUser(); $inquiryController->index(); exit; }
if ($method === "PATCH" && preg_match('#^/admin/inquiries/(\d+)$#',$uri,$matches)) { Auth::requireUser(); $inquiryController->update((int)$matches[1]); exit; }
if ($method === "DELETE" && preg_match('#^/admin/inquiries/(\d+)$#',$uri,$matches)) { Auth::requireUser(); $inquiryController->destroy((int)$matches[1]); exit; }

if ($method === "POST" && $uri === "/upload") {
    Auth::requireUser();
    (new UploadController())->store();
    exit;
}

if ($method === "GET" && $uri === "/products") {
    $productController->index();
    exit;
}

if ($method === "GET" && preg_match('#^/products/(\d+)$#', $uri, $matches)) {
    $productController->show((int) $matches[1]);
    exit;
}

if ($method === "POST" && $uri === "/products") {
    Auth::requireUser();
    $productController->store();
    exit;
}

if (($method === "PUT" || $method === "PATCH") && preg_match('#^/products/(\d+)$#', $uri, $matches)) {
    Auth::requireUser();
    $productController->update((int) $matches[1]);
    exit;
}

if ($method === "DELETE" && preg_match('#^/products/(\d+)$#', $uri, $matches)) {
    Auth::requireUser();
    $productController->destroy((int) $matches[1]);
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
    Auth::requireUser();
    $controller->store();
    exit;
}

if (($method === "PUT" || $method === "PATCH") && preg_match('#^/categories/(\d+)$#', $uri, $matches)) {
    Auth::requireUser();
    $controller->update((int) $matches[1]);
    exit;
}

if ($method === "DELETE" && preg_match('#^/categories/(\d+)$#', $uri, $matches)) {
    Auth::requireUser();
    $controller->destroy((int) $matches[1]);
    exit;
}

http_response_code(404);

echo json_encode([
    "message" => "Route not found"
]);

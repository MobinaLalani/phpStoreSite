<?php
require_once __DIR__ . "/../Repositories/SettingsRepository.php";
class SettingsController
{
    private $repository;
    public function __construct() { $this->repository = new SettingsRepository(); header("Content-Type: application/json; charset=UTF-8"); }
    public function publicIndex() { $this->respond($this->repository->getPublic()); }
    public function index() { $this->respond($this->repository->getAll()); }
    public function update() { $body = json_decode(file_get_contents("php://input"), true); if (!is_array($body)) { $this->respond(array("message"=>"Invalid JSON body"),400); return; } try { $this->respond($this->repository->update($body)); } catch (Exception $e) { $this->respond(array("message"=>"خطا در ذخیره تنظیمات"),500); } }
    private function respond($data,$status=200) { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); }
}

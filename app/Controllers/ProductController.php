<?php

require_once __DIR__ . "/../Repositories/ProductRepository.php";

class ProductController
{
    private $repository;
    private $fields = array("title", "slug", "shortDescription", "description", "thumbnail", "images", "price", "oldPrice", "discount", "rating", "reviewCount", "stock", "sku", "brand", "categoryId", "tags", "colors", "specifications", "status", "isFeatured");

    public function __construct()
    {
        $this->repository = new ProductRepository();
        header("Content-Type: application/json; charset=UTF-8");
    }

    public function index()
    {
        try { $this->respond($this->repository->getAll()); }
        catch (Exception $e) { $this->respond(array("message" => "خطا در دریافت محصولات"), 500); }
    }

    public function show($id)
    {
        $item = $this->repository->getById($id);
        if (!$item) { $this->respond(array("message" => "Product not found"), 404); return; }
        $this->respond($item);
    }

    public function store()
    {
        try {
            $body = $this->filter($this->readBody());
            $errors = $this->validate($body, true);
            if (!empty($errors)) { $this->respond(array("message" => "Validation failed", "errors" => $errors), 422); return; }
            $this->respond($this->repository->create($body), 201);
        } catch (InvalidArgumentException $e) { $this->respond(array("message" => $e->getMessage()), 400); }
        catch (Exception $e) { $this->respond(array("message" => "خطا در ایجاد محصول"), 500); }
    }

    public function update($id)
    {
        try {
            $body = $this->filter($this->readBody());
            $errors = $this->validate($body, false);
            if (empty($body)) $errors["body"] = "At least one field is required";
            if (!empty($errors)) { $this->respond(array("message" => "Validation failed", "errors" => $errors), 422); return; }
            $item = $this->repository->update($id, $body);
            if (!$item) { $this->respond(array("message" => "Product not found"), 404); return; }
            $this->respond($item);
        } catch (InvalidArgumentException $e) { $this->respond(array("message" => $e->getMessage()), 400); }
        catch (Exception $e) { $this->respond(array("message" => "خطا در بروزرسانی محصول"), 500); }
    }

    public function destroy($id)
    {
        try {
            if (!$this->repository->delete($id)) { $this->respond(array("message" => "Product not found"), 404); return; }
            http_response_code(204);
        } catch (Exception $e) { $this->respond(array("message" => "خطا در حذف محصول"), 500); }
    }

    private function readBody()
    {
        $body = json_decode(file_get_contents("php://input"), true);
        if (!is_array($body) || json_last_error() !== JSON_ERROR_NONE) throw new InvalidArgumentException("Request body must be valid JSON");
        return $body;
    }

    private function filter($body) { return array_intersect_key($body, array_flip($this->fields)); }

    private function validate($body, $creating)
    {
        $errors = array();
        $requiredStrings = array("title", "slug", "shortDescription", "description", "thumbnail", "sku", "brand");
        foreach ($requiredStrings as $field) {
            if (($creating || array_key_exists($field, $body)) && (!isset($body[$field]) || !is_string($body[$field]) || trim($body[$field]) === "")) $errors[$field] = $field . " is required";
        }
        foreach (array("price", "stock", "categoryId") as $field) {
            if (($creating || array_key_exists($field, $body)) && (!isset($body[$field]) || !is_numeric($body[$field]) || (float) $body[$field] < 0)) $errors[$field] = $field . " must be a positive number";
        }
        foreach (array("oldPrice", "discount", "rating", "reviewCount") as $field) {
            if (array_key_exists($field, $body) && $body[$field] !== null && !is_numeric($body[$field])) $errors[$field] = $field . " must be numeric";
        }
        foreach (array("images", "tags", "colors", "specifications") as $field) {
            if (array_key_exists($field, $body) && !is_array($body[$field])) $errors[$field] = $field . " must be an array";
        }
        if (array_key_exists("status", $body) && !in_array($body["status"], array("active", "draft", "archived"), true)) $errors["status"] = "Invalid status";
        return $errors;
    }

    private function respond($data, $status = 200)
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

<?php

require_once __DIR__ . "/../Repositories/CategoryRepository.php";

class CategoryController
{
    private $repository;

    public function __construct()
    {
        $this->repository = new CategoryRepository();

        header("Content-Type: application/json; charset=UTF-8");
    }

    public function index()
    {
        try {

            $includeProducts =
                isset($_GET["includeProducts"]) &&
                $_GET["includeProducts"] === "true";

            $categories = $includeProducts
                ? $this->repository->getAllWithProducts()
                : $this->repository->getAll();

            echo json_encode($categories, JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode(array(
                "message" => "خطا در دریافت دسته‌بندی‌ها"
            ));
        }
    }

    public function show($id)
    {
        $category = $this->repository->getById($id);

        if (!$category) {
            $this->respond(array("message" => "Category not found"), 404);
            return;
        }

        $this->respond($category);
    }

    public function store()
    {
        try {

            $body = $this->readJsonBody();
            $errors = $this->validate($body, true);

            if (!empty($errors)) {
                $this->respond(array("message" => "Validation failed", "errors" => $errors), 422);
                return;
            }

            $category = $this->repository->create(array(
                "title" => $body["title"],
                "slug" => $body["slug"],
                "image" => isset($body["image"]) ? $body["image"] : "",
                "description" => isset($body["description"]) ? $body["description"] : ""
            ));

            $this->respond($category, 201);

        } catch (InvalidArgumentException $e) {
            $this->respond(array("message" => $e->getMessage()), 400);
        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode(array(
                "message" => "خطا در ایجاد دسته‌بندی"
            ));
        }
    }

    public function update($id)
    {
        try {

            $body = $this->readJsonBody();
            $allowedFields = array("title", "slug", "image", "description");
            $body = array_intersect_key($body, array_flip($allowedFields));
            $errors = $this->validate($body, false);

            if (empty($body)) {
                $errors["body"] = "At least one field is required";
            }

            if (!empty($errors)) {
                $this->respond(array("message" => "Validation failed", "errors" => $errors), 422);
                return;
            }

            $category = $this->repository->update($id, $body);

            if (!$category) {

                http_response_code(404);

                echo json_encode(array(
                    "message" => "Category not found"
                ));

                return;
            }

            $this->respond($category);

        } catch (InvalidArgumentException $e) {
            $this->respond(array("message" => $e->getMessage()), 400);
        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode(array(
                "message" => "خطا در بروزرسانی دسته‌بندی"
            ));
        }
    }

    public function destroy($id)
    {
        try {

            $deleted = $this->repository->delete($id);

            if (!$deleted) {

                http_response_code(404);

                echo json_encode(array(
                    "message" => "Category not found"
                ));

                return;
            }

            http_response_code(204);

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode(array(
                "message" => "خطا در حذف دسته‌بندی"
            ));
        }
    }

    private function readJsonBody()
    {
        $body = json_decode(file_get_contents("php://input"), true);

        if (!is_array($body) || json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Request body must be valid JSON");
        }

        return $body;
    }

    private function validate($body, $creating)
    {
        $errors = array();

        foreach (array("title", "slug") as $field) {
            if (($creating || array_key_exists($field, $body))
                && (!isset($body[$field]) || !is_string($body[$field]) || trim($body[$field]) === "")) {
                $errors[$field] = ucfirst($field) . " is required";
            }
        }

        foreach (array("image", "description") as $field) {
            if (array_key_exists($field, $body) && !is_string($body[$field])) {
                $errors[$field] = ucfirst($field) . " must be a string";
            }
        }

        return $errors;
    }

    private function respond($data, $status = 200)
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}

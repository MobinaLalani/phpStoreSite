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

    public function store()
    {
        try {

            $body = json_decode(file_get_contents("php://input"), true);

            $category = $this->repository->create(array(
                "title" => $body["title"],
                "slug" => $body["slug"],
                "image" => $body["image"],
                "description" => $body["description"]
            ));

            http_response_code(201);

            echo json_encode($category, JSON_UNESCAPED_UNICODE);

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

            $body = json_decode(file_get_contents("php://input"), true);

            $category = $this->repository->update($id, $body);

            if (!$category) {

                http_response_code(404);

                echo json_encode(array(
                    "message" => "Category not found"
                ));

                return;
            }

            echo json_encode($category, JSON_UNESCAPED_UNICODE);

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

            echo json_encode(array(
                "success" => true
            ));

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode(array(
                "message" => "خطا در حذف دسته‌بندی"
            ));
        }
    }
}
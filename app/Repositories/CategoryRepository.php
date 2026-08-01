<?php

class CategoryRepository
{
    private $categoryFile;
    private $productFile;

    public function __construct()
    {
        $this->categoryFile = __DIR__ . "/../data/categories.json";
        $this->productFile = __DIR__ . "/../data/products.json";
    }

    public function getAll()
    {
        if (!file_exists($this->categoryFile)) {
            return [];
        }

        $json = file_get_contents($this->categoryFile);

        return json_decode($json, true);
    }

    public function getById($id)
    {
        $categories = $this->getAll();

        foreach ($categories as $category) {
            if ($category["id"] == $id) {
                return $category;
            }
        }

        return null;
    }

    public function create($data)
    {
        $categories = $this->getAll();

        $newId = empty($categories)
            ? 1
            : max(array_column($categories, "id")) + 1;

        $newCategory = array_merge(
            array(
                "id" => $newId
            ),
            $data
        );

        $categories[] = $newCategory;

        file_put_contents(
            $this->categoryFile,
            json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return $newCategory;
    }

    public function update($id, $data)
    {
        $categories = $this->getAll();

        foreach ($categories as $index => $category) {

            if ($category["id"] == $id) {

                $categories[$index] = array_merge($category, $data);

                file_put_contents(
                    $this->categoryFile,
                    json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );

                return $categories[$index];
            }
        }

        return null;
    }

    public function delete($id)
    {
        $categories = $this->getAll();

        $filtered = array_filter($categories, function ($category) use ($id) {
            return $category["id"] != $id;
        });

        if (count($filtered) == count($categories)) {
            return false;
        }

        file_put_contents(
            $this->categoryFile,
            json_encode(array_values($filtered), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return true;
    }

    public function getAllWithProducts()
    {
        $categories = $this->getAll();

        if (!file_exists($this->productFile)) {
            return $categories;
        }

        $products = json_decode(
            file_get_contents($this->productFile),
            true
        );

        $result = array();

        foreach ($categories as $category) {

            $categoryProducts = array_filter($products, function ($product) use ($category) {
                return $product["categoryId"] == $category["id"];
            });

            $category["products"] = array_values($categoryProducts);

            $result[] = $category;
        }

        return $result;
    }
}
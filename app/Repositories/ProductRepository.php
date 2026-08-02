<?php

class ProductRepository
{
    private $file;

    public function __construct()
    {
        $this->file = __DIR__ . "/../data/products.json";
    }

    public function getAll()
    {
        if (!file_exists($this->file)) return array();
        $items = json_decode(file_get_contents($this->file), true);
        return is_array($items) ? $items : array();
    }

    public function getById($id)
    {
        foreach ($this->getAll() as $item) {
            if ((int) $item["id"] === (int) $id) return $item;
        }
        return null;
    }

    public function create($data)
    {
        $items = $this->getAll();
        $now = gmdate("c");
        $item = array_merge(array(
            "id" => empty($items) ? 1 : max(array_column($items, "id")) + 1,
            "rating" => 0,
            "reviewCount" => 0,
            "tags" => array(),
            "colors" => array(),
            "specifications" => array(),
            "status" => "active",
            "isFeatured" => false,
            "createdAt" => $now,
            "updatedAt" => $now
        ), $data);
        $items[] = $item;
        $this->write($items);
        return $item;
    }

    public function update($id, $data)
    {
        $items = $this->getAll();
        foreach ($items as $index => $item) {
            if ((int) $item["id"] === (int) $id) {
                unset($data["id"], $data["createdAt"]);
                $data["updatedAt"] = gmdate("c");
                $items[$index] = array_merge($item, $data);
                $this->write($items);
                return $items[$index];
            }
        }
        return null;
    }

    public function delete($id)
    {
        $items = $this->getAll();
        $filtered = array_values(array_filter($items, function ($item) use ($id) {
            return (int) $item["id"] !== (int) $id;
        }));
        if (count($filtered) === count($items)) return false;
        $this->write($filtered);
        return true;
    }

    private function write($items)
    {
        $json = json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($this->file, $json, LOCK_EX) === false) {
            throw new RuntimeException("Products could not be saved");
        }
    }
}

<?php

class UploadController
{
    public function store()
    {
        header("Content-Type: application/json; charset=UTF-8");

        if (!isset($_FILES["file"]) || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
            $this->respond(array("message" => "No valid file was uploaded"), 422);
            return;
        }

        $file = $_FILES["file"];

        if ($file["error"] !== UPLOAD_ERR_OK) {
            $this->respond(array("message" => "Upload failed", "code" => $file["error"]), 422);
            return;
        }

        if ($file["size"] > 5 * 1024 * 1024) {
            $this->respond(array("message" => "Image must be smaller than 5 MB"), 422);
            return;
        }

        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file["tmp_name"]);
        $extensions = array(
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/webp" => "webp",
            "image/gif" => "gif"
        );

        if (!isset($extensions[$mimeType])) {
            $this->respond(array("message" => "Only JPG, PNG, WEBP and GIF images are allowed"), 422);
            return;
        }

        $uploadDirectory = __DIR__ . "/../../uploads/products";
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true)) {
            $this->respond(array("message" => "Upload directory could not be created"), 500);
            return;
        }

        $filename = bin2hex(random_bytes(16)) . "." . $extensions[$mimeType];
        $destination = $uploadDirectory . "/" . $filename;

        if (!move_uploaded_file($file["tmp_name"], $destination)) {
            $this->respond(array("message" => "Uploaded file could not be saved"), 500);
            return;
        }

        $basePath = rtrim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])), "/");
        $url = ($basePath === "." ? "" : $basePath) . "/uploads/products/" . $filename;

        $this->respond(array("url" => $url), 201);
    }

    private function respond($data, $status)
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}

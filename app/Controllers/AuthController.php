<?php

require_once __DIR__ . "/../Helpers/Auth.php";

class AuthController
{
    public function login()
    {
        header("Content-Type: application/json; charset=UTF-8");
        $body = json_decode(file_get_contents("php://input"), true);
        if (!is_array($body)) { $this->respond(array("message" => "درخواست نامعتبر است."), 400); return; }
        $username = isset($body["username"]) ? $body["username"] : "";
        $password = isset($body["password"]) ? $body["password"] : "";
        if (!Auth::attempt($username, $password)) { $this->respond(array("message" => "نام کاربری یا رمز عبور اشتباه است."), 401); return; }
        $config = require __DIR__ . "/../../config/auth.local.php";
        $this->respond(array("token" => Auth::issueToken($username), "tokenType" => "Bearer", "expiresIn" => $config["token_ttl"], "user" => array("username" => $username)));
    }

    public function me() { $this->respond(array("user" => Auth::requireUser())); }
    private function respond($data, $status = 200) { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); }
}

<?php

class Auth
{
    private static function config() { return require __DIR__ . "/../../config/auth.local.php"; }
    private static function encode($value) { return rtrim(strtr(base64_encode($value), '+/', '-_'), '='); }
    private static function decode($value) { $padding = strlen($value) % 4; if ($padding) $value .= str_repeat('=', 4 - $padding); return base64_decode(strtr($value, '-_', '+/'), true); }

    public static function attempt($username, $password)
    {
        $config = self::config();
        return is_string($username) && is_string($password) && hash_equals($config["username"], $username) && password_verify($password, $config["password_hash"]);
    }

    public static function issueToken($username)
    {
        $config = self::config(); $now = time();
        $header = self::encode(json_encode(array("alg" => "HS256", "typ" => "JWT")));
        $payload = self::encode(json_encode(array("sub" => $username, "iat" => $now, "exp" => $now + $config["token_ttl"])));
        $signature = self::encode(hash_hmac("sha256", $header . "." . $payload, $config["token_secret"], true));
        return $header . "." . $payload . "." . $signature;
    }

    public static function user()
    {
        $authorization = isset($_SERVER["HTTP_AUTHORIZATION"]) ? $_SERVER["HTTP_AUTHORIZATION"] : (isset($_SERVER["REDIRECT_HTTP_AUTHORIZATION"]) ? $_SERVER["REDIRECT_HTTP_AUTHORIZATION"] : "");
        if ($authorization === "" && function_exists("getallheaders")) {
            $headers = getallheaders();
            if (isset($headers["Authorization"])) $authorization = $headers["Authorization"];
            elseif (isset($headers["authorization"])) $authorization = $headers["authorization"];
        }
        if (!preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) return null;
        $parts = explode('.', $matches[1]); if (count($parts) !== 3) return null;
        $config = self::config();
        $expected = self::encode(hash_hmac("sha256", $parts[0] . "." . $parts[1], $config["token_secret"], true));
        if (!hash_equals($expected, $parts[2])) return null;
        $decoded = self::decode($parts[1]); $payload = $decoded ? json_decode($decoded, true) : null;
        if (!is_array($payload) || !isset($payload["sub"], $payload["exp"]) || (int) $payload["exp"] <= time()) return null;
        return array("username" => $payload["sub"], "expiresAt" => (int) $payload["exp"]);
    }

    public static function requireUser()
    {
        $user = self::user(); if ($user) return $user;
        http_response_code(401); header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array("message" => "برای انجام این عملیات باید وارد شوید."), JSON_UNESCAPED_UNICODE); exit;
    }
}

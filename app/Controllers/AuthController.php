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
        if (!Auth::attempt($username, $password)) { $this->logActivity($username,false); $this->respond(array("message" => "نام کاربری یا رمز عبور اشتباه است."), 401); return; }
        $this->logActivity($username,true);
        $config = require __DIR__ . "/../../config/auth.local.php";
        $this->respond(array("token" => Auth::issueToken($username), "tokenType" => "Bearer", "expiresIn" => $config["token_ttl"], "user" => array("username" => $username)));
    }

    public function me() { $this->respond(array("user" => Auth::requireUser())); }
    public function changePassword()
    {
        Auth::requireUser(); $body=json_decode(file_get_contents("php://input"),true);
        if(!is_array($body)||!isset($body["currentPassword"],$body["newPassword"])){$this->respond(array("message"=>"اطلاعات ناقص است."),422);return;}
        $config=require __DIR__ . "/../../config/auth.local.php";
        if(!password_verify($body["currentPassword"],$config["password_hash"])){$this->respond(array("message"=>"رمز فعلی اشتباه است."),422);return;}
        if(!is_string($body["newPassword"])||strlen($body["newPassword"])<10){$this->respond(array("message"=>"رمز جدید باید حداقل ۱۰ کاراکتر باشد."),422);return;}
        $config["password_hash"]=password_hash($body["newPassword"],PASSWORD_BCRYPT);$config["token_version"]=(isset($config["token_version"])?(int)$config["token_version"]:1)+1;
        $this->writeConfig($config);$this->respond(array("success"=>true,"message"=>"رمز تغییر کرد؛ دوباره وارد شوید."));
    }
    public function logoutAll(){Auth::requireUser();$config=require __DIR__ . "/../../config/auth.local.php";$config["token_version"]=(isset($config["token_version"])?(int)$config["token_version"]:1)+1;$this->writeConfig($config);$this->respond(array("success"=>true));}
    public function activity(){Auth::requireUser();$file=__DIR__."/../data/auth_activity.json";$data=file_exists($file)?json_decode(file_get_contents($file),true):array();$this->respond(is_array($data)?array_slice(array_reverse($data),0,20):array());}
    private function logActivity($username,$success){$file=__DIR__."/../data/auth_activity.json";$items=file_exists($file)?json_decode(file_get_contents($file),true):array();if(!is_array($items))$items=array();$items[]=array("username"=>$username,"success"=>$success,"ip"=>isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:"unknown","createdAt"=>gmdate("c"));if(count($items)>200)$items=array_slice($items,-200);file_put_contents($file,json_encode($items,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),LOCK_EX);}
    private function writeConfig($config){$content="<?php\n\nreturn ".var_export($config,true).";\n";if(file_put_contents(__DIR__."/../../config/auth.local.php",$content,LOCK_EX)===false)throw new RuntimeException("Auth config could not be saved");}
    private function respond($data, $status = 200) { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); }
}

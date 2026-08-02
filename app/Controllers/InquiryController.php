<?php
require_once __DIR__ . "/../Repositories/InquiryRepository.php";
class InquiryController
{
    private $repository;
    public function __construct(){ $this->repository=new InquiryRepository();header("Content-Type: application/json; charset=UTF-8"); }
    public function index(){ $this->respond($this->repository->getAll()); }
    public function store(){ $body=$this->body();if(!$body)return;$errors=array();foreach(array("name","mobile","productTitle")as$field){if(!isset($body[$field])||!is_string($body[$field])||trim($body[$field])==="")$errors[$field]=$field." is required";}if(!empty($errors)){$this->respond(array("message"=>"Validation failed","errors"=>$errors),422);return;}$allowed=array("name","mobile","productId","productTitle","quantity","description","preferredContact");$this->respond($this->repository->create(array_intersect_key($body,array_flip($allowed))),201); }
    public function update($id){$body=$this->body();if(!$body)return;$allowed=array("status","adminNote");$data=array_intersect_key($body,array_flip($allowed));if(isset($data["status"])&&!in_array($data["status"],array("new","contacted","quoted","completed","cancelled"),true)){$this->respond(array("message"=>"Invalid status"),422);return;}$item=$this->repository->update($id,$data);$item?$this->respond($item):$this->respond(array("message"=>"Inquiry not found"),404);}
    public function destroy($id){if(!$this->repository->delete($id)){$this->respond(array("message"=>"Inquiry not found"),404);return;}http_response_code(204);}
    private function body(){$body=json_decode(file_get_contents("php://input"),true);if(!is_array($body)){$this->respond(array("message"=>"Invalid JSON body"),400);return null;}return $body;}
    private function respond($data,$status=200){http_response_code($status);echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}
}

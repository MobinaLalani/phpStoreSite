<?php
class InquiryRepository
{
    private $file;
    public function __construct() { $this->file = __DIR__ . "/../data/inquiries.json"; }
    public function getAll() { $data = json_decode(file_get_contents($this->file), true); if (!is_array($data)) return array(); usort($data,function($a,$b){return strcmp($b["createdAt"],$a["createdAt"]);}); return $data; }
    public function create($data) { $items=$this->getAll(); $now=gmdate("c"); $item=array_merge(array("id"=>empty($items)?1:max(array_column($items,"id"))+1,"status"=>"new","adminNote"=>"","createdAt"=>$now,"updatedAt"=>$now),$data); $items[]=$item; $this->write($items); return $item; }
    public function update($id,$data) { $items=$this->getAll(); foreach($items as $index=>$item){if((int)$item["id"]===(int)$id){$items[$index]=array_merge($item,$data,array("updatedAt"=>gmdate("c")));$this->write($items);return $items[$index];}} return null; }
    public function delete($id) { $items=$this->getAll();$filtered=array_values(array_filter($items,function($item)use($id){return(int)$item["id"]!==(int)$id;}));if(count($items)===count($filtered))return false;$this->write($filtered);return true; }
    private function write($data) { if(file_put_contents($this->file,json_encode(array_values($data),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),LOCK_EX)===false)throw new RuntimeException("Inquiries could not be saved"); }
}

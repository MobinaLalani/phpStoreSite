<?php
class SettingsRepository
{
    private $file;
    public function __construct() { $this->file = __DIR__ . "/../data/settings.json"; }
    public function getAll() { $data = json_decode(file_get_contents($this->file), true); return is_array($data) ? $data : array(); }
    public function getPublic() { $all = $this->getAll(); return array_intersect_key($all, array_flip(array("store", "inquiry", "social", "appearance", "seo"))); }
    public function update($data) { $merged = $this->merge($this->getAll(), $data); $this->write($merged); return $merged; }
    private function merge($base, $patch) { foreach ($patch as $key => $value) { if (isset($base[$key]) && is_array($base[$key]) && is_array($value)) $base[$key] = $this->merge($base[$key], $value); elseif (array_key_exists($key, $base)) $base[$key] = $value; } return $base; }
    private function write($data) { if (file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX) === false) throw new RuntimeException("Settings could not be saved"); }
}

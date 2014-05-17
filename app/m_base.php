<?php
require_once P_PATH.'/app/lib/source.php';

class m_base {
    static $i = NULL;
    static $database = NULL;
    static $source = NULL;
    
    static $title = array();
    
    public static function merge($name, $data, $path){
        $t = new Template(P_PATH.'/app/tpl/'.$path);
        $t->data = $data;
        $content = $t->render();
        
        if($name){
            require_once P_PATH."/app/{$name}.php";
            $m = new $name;
            return $m->render($content);
        }else{
            return $content;
        }
    }
    
    public function render($content=''){}
}
?>

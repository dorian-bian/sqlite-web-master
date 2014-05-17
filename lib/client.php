<?php
class Client {
    public $has_errors = FALSE;
    public $errors = array();
    
    function get($key, $opts){
        if(is_string($opts)) parse_str($opts, $opts);
       
        $value = isset($_GET[$key]) ? trim($_GET[$key]) : NULL;
        
        if($value!==NULL && $this->check($value, $opts)){
            return $value;
        }else{
            return isset($opts['default']) ? $opts['default'] : NULL;
        }
    }
    
    function post($key, $opts, $msg=''){
        if(is_string($opts)) parse_str($opts, $opts);
        
        $value = isset($_POST[$key]) ? $_POST[$key] : NULL;
        $level = isset($opts['level']) ? (int)$opts['level'] : 0;
        if($level > 0 && $value===NULL) return array();
        
        if($value!==NULL && $this->check($value, $opts, $level)){
            return $value;
        }else{
            if(isset($opts['default'])){
                return $opts['default'];
            }else{
                $this->set_error($key, $msg);
                return NULL;
            }
        }
    }
    
    function set_error($key, $msg=''){
        $this->has_errors = TRUE;
        $this->errors[$key] = array('key'=>$key, 'content'=>$msg);
    }
    
    function check($val, $opts, $level=0){
        if($level > 0){
            foreach($val as $sub){
                if(!$this->check($sub, $opts, $level-1)) return false;
            }
            return true;
        }else{
            switch($opts['t']){
            case 'int':
                if(isset($opts['min']) && (int)$val<(int)$opts['min']) return false;
                if(isset($opts['max']) && (int)$val>(int)$opts['max']) return false;
                break;
            case 'str':
                if(extension_loaded('mbstring')){
                    if(isset($opts['min']) && mb_strlen($val,'UTF-8')<(int)$opts['min']) return false;
                    if(isset($opts['max']) && mb_strlen($val,'UTF-8')>(int)$opts['max']) return false;
                }else{
                    if(isset($opts['min']) && strlen($val)<(int)$opts['min']) return false;
                    if(isset($opts['max']) && strlen($val)>(int)$opts['max']) return false;
                }
                break;
            case 'var':
                if(!preg_match('/^[a-zA-Z_][0-9a-zA-Z_]*$/', $val)) return false;
                break;
            case 'email':
                if(!preg_match('/^(\w+\.)*\w+@(\w+\.)+[A-Za-z]+$/', $val)) return false;
                break;
            default:
                return false;
                break;
            }
            return true;
        }
    }
}
?>

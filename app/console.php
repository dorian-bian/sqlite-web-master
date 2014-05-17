<?php
require_once P_PATH.'/app/lib/user.php';
require_once P_PATH.'/app/lib/database.php';
require_once P_PATH.'/app/m_base.php';

class Console {
    public $modules  = array(
        'pass' => 'm_pass',
        'login' => 'm_login',
        
        'global' => 'm_system',
        'system' => 'm_system',
        'pragma' => 'm_pragma',
        'runsql' => 'm_runsql',
        'export' => 'm_export',
        'import' => 'm_import',
        'mirror' => 'm_mirror',
        'manage' => 'm_manage',
        'constraint' => 'm_constraint',
        
        'index-list' => 'm_index_list',
        'trigger-list' => 'm_trigger_list',
        'table-list' => 'm_table_list',
        'table-item' => 'm_table_item',
        'view-list' => 'm_view_list',
        'view-item' => 'm_view_item',
        'content-list' => 'm_content_list',
        'content-item' => 'm_content_item'
    );
    
    public $errors = array();
    public $alerts = array();
    
    public $result = '';
    
    static $debug = TRUE;
    
    public function __construct(){
        ini_set('html_errors', 0);
        set_error_handler(array($this, 'error_handler'));
        set_exception_handler(array($this,'exception_handler'));
        register_shutdown_function(array($this,'shutdown_handler'));
    }
    
    public static function instance(){
        static $console = NULL;
        if($console===NULL) $console = new Console();
        return $console;
    }
    
    public static function check(){
        $fail = FALSE;
        if(!version_compare(PHP_VERSION, '5.3.3') < 0){
            $fail = 'The version of PHP must be newer than 5.3.3.'; 
        }
        
        $exts = array('sqlite3', 'session', 'json', 'ctype');
        foreach($exts as $item){
            if(!extension_loaded($item)){
                $fail = $item.' extension must be loaded.';
                break;
            }
        }
        
        if($fail!==FALSE){
            throw new Exception($fail);
        }
    }
    
    public function run(){
        $this->check();
        
        if(!isset($_SESSION)) session_start();
        #---------------------------------------------------------------
        Database::$cfgs = database_cfgs();
        
        $database = isset($_GET['database']) ? $_GET['database'] : NULL;
        if(!$database || !isset(Database::$cfgs[$database])) $database = key(Database::$cfgs);
        Database::instance($database, TRUE);
        
        Template::$site['database'] = $database;
        Template::$site['args'] += array('database' => $database);
        
        m_base::$i = isset($_GET['i']) ? $_GET['i']: 'global';
        m_base::$database = $database;
        m_base::$source = isset($_GET['source']) && trim($_GET['source']) ? source_item($_GET['source']) : NULL;
        #---------------------------------------------------------------
        
        $modules = $this->modules;
        
        $i = isset($_GET['i']) ? $_GET['i']: 'global';
        
        if($i=='fresh') return;
        
        $is_entry = ($i=='login' || $i=='pass');
        if($is_entry || user_check()){
            if(!$is_entry && SEC_PATH){
                Template::$site['args'] += array('sec-token' => $_SESSION['sec-token']);
            }
            require_once  P_PATH.'/app/'.$modules[$i].'.php';
            $m = new $modules[$i];
            $this->result = $m->render();
        }else{
            $q = array();
            foreach($_GET as $key=>$val) $q['rel-'.$key] = $val;
            $q['i'] = 'login';
            
            header('Location: index.php?'.http_build_query($q));
        }
    }
    
    public function alert(){
        foreach(func_get_args() as $data){
            ob_start();
            var_dump($data);
            $dump = ob_get_clean();
            $this->alerts[] = array('type'=>gettype($data), 'dump'=>$dump);
        }
    }
    
    public function error_handler($code, $info, $file, $line){
        throw new ErrorException($info, $code, 0, $file, $line); 
    }
    
    public function exception_handler($e){
        $error = array(
            'info' => get_class($e).': '.$e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'data' => isset($e->data) ? $e->data : '',
            'foot' => array()
        );
        
        foreach($e->getTrace() as $item){
            if(isset($item['file'])){
                $file = file($item['file']);
                $error['foot'][] = array(
                    'file' => $item['file'],
                    'line' => $item['line'], 
                    'code' => $file[$item['line']-1]
                );
            }
        }
        
        $this->errors[] = $error;
    }
    
    public function shutdown_handler(){
        ob_get_clean();
        
        $error = error_get_last();
        if(empty($this->errors) && $error){
            if(is_file($error['file'])){
                $file = file($error['file']);
                $this->errors[] = array(
                    'info' => $error['message'],
                    'file' => $error['file'],
                    'line' => $error['line'],
                    'foot' => array(
                        array(
                            'file' => $error['file'],
                            'line' => $error['line'],
                            'code' => $file[$error['line']-1]
                        )
                    )
                );
            }else{
                $this->errors[] = array(
                    'info' => $error['message'],
                    'file' => $error['file'],
                    'line' => $error['line'],
                    'foot' => array()
                );
            }
        }
        
        if($this->errors || $this->alerts){
            $this->render();
        }else{
            echo $this->result;
        }
    }
    
    public function render(){
        $t = new Template(P_PATH.'/app/tpl/console.tpl');
        $t->data['alerts'] = $this->alerts;
        $t->data['errors'] = $this->errors;
        $t->data['debug'] = self::$debug;
        echo $t->render();
    }
}

function alert(){
    $alert = array(Console::instance(), 'alert');
    call_user_func_array($alert, func_get_args());
}
?>

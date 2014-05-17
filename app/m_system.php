<?php
require_once P_PATH.'/app/lib/database.php';
require_once P_PATH.'/app/lib/pragma.php';

class m_system extends m_base {
    public function render($content=''){
        self::$title[] = 'System';
        
        $is_success = FALSE;
        $error = NULL;
        $trace = NULL;
        
        $is_download = FALSE;
        if(IS_POST){
            $names = isset($_POST['names']) ? (array)$_POST['names'] : array();
            $action = isset($_POST['action']) ? $_POST['action'] : NULL;
            
            try{
                switch($action){
                case 'vacuum':
                    foreach($names as $name){
                        if($name) database_vacuum($name);
                    }
                    break;
                case 'create':
                    $group = $_POST['group'];
                    if(isset($_POST['base'][1])){
                        database_create($group, $_POST['base'][1]);
                    }
                    break;
                case 'update':
                    $group = $_POST['group'];
                    if(isset($_POST['base'][0]) && isset($_POST['base'][1])){
                        database_rename($group, $_POST['base'][0], $_POST['base'][1]);
                    }
                    break;
                case 'remove':
                    database_remove($names[0]);
                    break;
                case 'download':
                    $path = database_path($names[0]);
                    alert($path);
                    $name = basename($path);
                    header("Content-Disposition: attachment; filename=\"$name\"");
                    header("Content-Length:".filesize($path));
                    header('Content-type: application/octet-stream');
                    header('Content-Transfer-Encoding: binary'); 
                    readfile($path);
                    $is_download = TRUE;
                    break;
                }
                $is_success = TRUE;
            }catch(Exception $e){
                $error = $e->getMessage();
                $trace = $e->getTraceAsString();
            }
        }
        
        if(!$is_download){
            $databases = array();
            foreach($GLOBALS['_DATABASES'] as $name=>$item){
                $path = $item['path'];
                if(!file_exists($path)) touch($path);
                $databases[] = array(
                    'name'=>$name, 
                    'path'=>$path, 
                    'size'=>$this->human_filesize(filesize($path)),
                    'free'=>$this->human_filesize(disk_free_space(dirname($path)))
                );
            }
            
            $data = array();
            $data['is_success'] = $is_success;
            $data['error'] = $error;
            $data['trace'] = $trace;
            
            $data['php_os'] = php_uname('s');
            $data['php_arch'] = php_uname('m');
            $data['php_version'] = phpversion();
            $data['php_sapi_name'] = php_sapi_name();
            $data['physical_path'] = P_PATH;
            
            if(extension_loaded('posix')){
                $user = posix_getpwuid(posix_geteuid());
                $group = posix_getpwuid(posix_getegid());
                $data['user'] = $user['name'];
                $data['uid'] = posix_geteuid();
                $data['gid'] = posix_getegid();
                $data['group'] = $group['name'];
            }
            
            $data['memory_limit'] = ini_get('memory_limit');
            $data['post_max_size'] = ini_get('post_max_size');
            $data['upload_max_filesize'] = ini_get('upload_max_filesize');
            
            $data['sqlite_module_version'] = phpversion('sqlite3');
            $data['sqlite_library_version'] = Database::version();
            $data['sqlite_compile_options'] = pragma_list('compile_options');
            $data['sqlite_collation_list']  = pragma_list('collation_list');
            
            $data['databases'] = $databases;
            $data['db_groups'] = database_groups();
            
            return self::merge('m_global', $data, 'system.tpl');
        }
    }
    
    public function human_filesize($bytes, $decimals = 2) {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $sz[(int)$factor];
    }
}
?>

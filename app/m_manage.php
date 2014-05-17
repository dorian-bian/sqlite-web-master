<?php
require_once P_PATH.'/app/lib/table.php';
require_once P_PATH.'/app/lib/content.php';

require_once P_PATH.'/app/lib/trigger.php';
require_once P_PATH.'/app/lib/pragma.php';

class m_manage extends m_base {
    public function render($content=''){
        self::$title[] = 'Manage';
        
        $info = table_base(self::$source['name']);
        
        $databases = database_list();
        $db_groups = database_groups();
        
        $is_success = FALSE;
        $error = NULL;
        $trace = NULL;
        
        $jump = NULL;
        if(IS_POST){
            try{
                $action = isset($_POST['action']) ? $_POST['action'] : NULL;
                switch($action){
                case 'reindex':
                    table_reindex(self::$source['name']);
                    break;
                case 'empty':
                    table_empty(self::$source['name']);
                    break;
                case 'rename':
                    table_rename(self::$source['name'], $_POST['option']['name']);
                    $_GET['source'] = $_POST['option']['name'];
                    $url = $_SERVER['PHP_SELF'].'?'. http_build_query($_GET);
                    header('Location:'.$url, TRUE, 301);
                    return '';
                case 'copy':
                    table_copy(self::$source['name'], $_POST['option']);
                    break;
                case 'populate':
                    content_populate(self::$source['name'], $_POST['option']);
                    break;
                }
                $is_success = TRUE;
            }catch(Exception $e){
                $error = $e->getMessage();
                $trace = $e->getTraceAsString();
            }
        }
        
        if(isset($_GET['get-columns'])){
            $columns = table_columns($_GET['get-columns']);
            $text = array();
            foreach($columns as $col){
                $text[] = '<option>'.htmlspecialchars($col['name'], ENT_COMPAT, 'UTF-8').'</option>';
            }
            return implode('', $text);
        }else{
            $data = array();
            $data['is_success'] = $is_success;
            $data['error'] = $error;
            $data['trace'] = $trace;
            
            $data['source_name'] = self::$source['name'];
            
            $data['dbname'] = self::$database;
            $data['databases'] = $databases;
            $data['db_groups'] = $db_groups;
            
            $data['code'] = table_code(self::$source['name']); 
            $data['cols'] = table_columns(self::$source['name']);
            $data['tables'] = table_list();
            $data['post'] = print_r($_POST, TRUE);
            $data['itemcount'] = content_count(self::$source['name'], NULL,NULL);
            $data['datetime'] = gmdate('Y-m-d H:i:s');
            
            return self::merge('m_source', $data, 'manage.tpl');
        }
    }
}
?>

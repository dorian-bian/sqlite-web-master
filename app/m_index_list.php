<?php
require_once P_PATH.'/app/lib/table.php';
require_once P_PATH.'/app/lib/index.php';
require_once P_PATH.'/app/lib/pragma.php';

class m_index_list extends m_base {
    public function render($content=''){
        self::$title[] = 'Index List';
        
        $is_success = FALSE;
        $error = NULL;
        $trace = NULL;
        
        if(IS_POST){
            $names = isset($_POST['names']) ? $_POST['names'] : array();
            $action = isset($_POST['action']) ? $_POST['action'] : NULL;
            $info = $_POST['info'];
            try{
                switch($action){
                case 'append':
                    index_create($info);
                    break;
                case 'update':
                    index_migrate($_POST['oldn'], $info);
                    break;
                case 'delete':
                    index_drop($info['name']);
                    break;
                case 'batch-delete':
                    index_drop($names);
                    break;
                }
                $name = $info['name'];
                $is_success = TRUE;
            }catch(Exception $e){
                $error = $e->getMessage();
                $trace = $e->getTraceAsString();
            }
        }
        
        $data = array();
        $data['is_success'] = $is_success;
        $data['error'] = $error;
        $data['trace'] = $trace;
        
        $data['table'] = self::$source['name'];
        $data['columns'] = table_columns(self::$source['name']);
        $data['collations'] = pragma_list('collation_list');
        
        $data['is_editable'] = self::$source['name']!='sqlite_master';
        
        $data['table_indices'] = table_indices(self::$source['name']);
        $data['index_indices'] = index_list(self::$source['name']);
        
        $data['collation'] = 'BINARY';
        $data['item-blank'] = array(
            'name' => NULL, 
            'unique' => FALSE, 
            'table' => self::$source['name'],
            'cols' => array(
                array('i'=> 0, 'name'=>'', 'order'=>'ASC', 'collation'=>'BINARY')
            )
        );
        
        return self::merge('m_schema', $data, 'index-list.tpl');
    }
}
?>

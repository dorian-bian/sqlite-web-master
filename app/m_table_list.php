<?php
require_once P_PATH.'/app/lib/table.php';
require_once P_PATH.'/app/lib/trigger.php';
require_once P_PATH.'/app/lib/index.php';

class m_table_list extends m_base {
    public function render($content=''){
        self::$title[] = 'Table List';
        
        $is_success = FALSE;
        $error = NULL;
        $trace = NULL;
        
        if(IS_POST){
            $names = isset($_POST['names']) ? $_POST['names'] : array();
            $action = isset($_POST['action']) ? $_POST['action'] : NULL;
            
            try{
                switch($action){
                case 'drop':
                    table_drop($names);
                    break;
                case 'empty':
                    table_empty($names);
                    break;
                case 'move':
                    table_move($names, $_POST['option']);
                    break;
                }
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
        
        $data['tables'] = table_list('name',TRUE, FALSE);
        
        return self::merge('m_global', $data, 'table-list.tpl');
    }
}
?>

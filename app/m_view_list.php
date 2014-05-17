<?php
require_once P_PATH.'/app/lib/view.php';

class m_view_list extends m_base {
    public function render($content=''){
        self::$title[] = 'View List';
        
        $is_success = FALSE;
        $error = NULL;
        $trace = NULL;
        
        if(IS_POST){
            $names = isset($_POST['names']) ? $_POST['names'] : array();
            $action = isset($_POST['action']) ? $_POST['action'] : NULL;
            
            try{
                switch($action){
                case 'drop':
                    view_drop($names);
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
        
        $data['views'] = view_list('name, sql', TRUE, TRUE);
        
        return self::merge('m_global', $data, 'view-list.tpl');
    }
}
?>

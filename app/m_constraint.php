<?php
require_once P_PATH.'/app/lib/table.php';
require_once P_PATH.'/app/lib/trigger.php';
require_once P_PATH.'/app/lib/pragma.php';

class m_constraint extends m_base {
    
    public function render($content=''){
        self::$title[] = 'Constraint';
        
        $resolutions = array(
            'ABORT' => array('name'=>'ABORT'), 
            'FAIL' => array('name'=>'FAIL'), 
            'IGNORE' => array('name'=>'IGNORE'),
            'ROLLBACK' => array('name'=>'ROLLBACK'), 
            'REPLACE' => array('name'=>'REPLACE')
        );
        $collations = pragma_list('collation_list');
        
        $orders = array(
            'ASC' => array('name' => 'ASC'),
            'DESC' => array('name' => 'DESC')
        );
        $actions = array(
            'NO ACTION' => array('name'=>'NO ACTION'),
            'RESTRICT' => array('name' => 'RESTRICT'),
            'SET NULL' => array('name' => 'SET NULL'),
            'SET DEFAULT' => array('name' => 'SET DEFAULT'),
            'CASCADE' => array('name' => 'CASCADE')
        );
        
        $is_success = FALSE;
        $error = NULL;
        $trace = NULL;
        
        if(IS_POST){
            try{
                $c = new Client();
                $action = $c->post('action','t=str');
                $type = $c->post('type', 't=str');
                $rel = $c->post('rel', 't=str');
                $i = $c->post('i', 't=str');
                $item = $_POST['item'];
                table_update_cons(self::$source['name'], $action, 
                    $type, $rel, $i, $item);
                
                $is_success = TRUE;
            }catch(Exception $e){
                $error = $e->getMessage();
                $trace = $e->getTraceAsString();
            }
        }
        
        $cons = table_cons(self::$source['name']);
        
        $empty = TRUE;
        foreach($cons as $con){
            if(!empty($con)) {
                $empty = FALSE;
                break;
            }
        }
        if(isset($_GET['get-columns'])){
            $columns = table_columns($_GET['get-columns']);
            $text = array();
            foreach($columns as $col){
                $text[] = '<li>'.htmlspecialchars($col['name'], ENT_COMPAT, 'UTF-8').'</li>';
            }
            return implode('', $text);
        }else{
            $data = array();
            $data['is_success'] = $is_success;
            $data['error'] = $error;
            $data['trace'] = $trace;
            
            $data['is_editable'] = self::$source['name']!='sqlite_master';
            $data['source'] = self::$source['name'];
            
            $data['cons'] = $cons;
            $data['resolutions'] = $resolutions;
            $data['collations'] = $collations;
            $data['update_actions'] = $actions;
            $data['delete_actions'] = $actions;
            
            $data['cons_c'] = array(
                'enabled' => TRUE,
                'expr' => ''
            );
            $data['cons_f'] = array(
                'name' => NULL,
                'cols' => array(),
                
                'refer' => array(
                    'name' => NULL,
                    'cols' => array()),
                    
                'match' => NULL,
                'on_update' => 'NO ACTION',
                'on_delete' => 'NO ACTION',
                
                'deferred' => FALSE
            );
            $data['cons_u'] = array(
                'cols' => array(
                    array('i'=>0, 'name'=>'', 'order'=>'ASC', 'collation'=>'BINARY')
                ),
                'on_conflict' => 'ABORT'
            );
            
            $data['tables'] = table_list('name', FALSE, FALSE);
            $data['columns'] = table_columns(self::$source['name']);
            $data['empty'] = $empty;
            
            return self::merge('m_schema', $data, 'constraint.tpl');
        }
    }
}
?>

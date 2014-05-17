<?php
require_once P_PATH.'/app/lib/trigger.php';
require_once P_PATH.'/app/lib/source.php';
require_once P_PATH.'/app/lib/table.php';

class m_trigger_list extends m_base {
    public function render($content=''){
        self::$title[] = 'Trigger List';
        
        $moments = array(
            array('name'=>'BEFORE'),
            array('name'=>'AFTER'),
            array('name'=>'INSTEAD OF')
        );
        
        $events = array(
            array('name'=>'DELETE'),
            array('name'=>'INSERT'),
            array('name'=>'UPDATE')
        );
        
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
                    trigger_create($info);
                    break;
                case 'update':
                    trigger_migrate($_POST['oldn'], $info);
                    break;
                case 'delete':
                    trigger_drop($info['name']);
                    break;
                case 'batch-delete':
                    trigger_drop($names);
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
        
        $data['is_editable'] = self::$source['name']!='sqlite_master';
        $data['source'] = self::$source['name'];
        $data['triggers'] = trigger_list(self::$source['name']);
        $data['trigger-blank'] = array(
            'name' => NULL,
            'moment' => 'BEFORE',
            'event' => NULL,
            'cols' => array(),
            'each' => FALSE,
            'when' => NULL,
            'action' => NULL,
        );
        $data['moments'] = $moments;
        $data['events'] = $events;
        $data['columns'] = self::$source ? table_columns(self::$source['name']) : array();
        
        $name = self::$source ? 'm_schema' : 'm_source';
        return self::merge($name, $data, 'trigger-list.tpl');
    }
}
?>

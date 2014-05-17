<?php
require_once P_PATH.'/app/lib/trigger.php';
require_once P_PATH.'/app/lib/table.php';
require_once P_PATH.'/app/lib/pragma.php';

class m_table_item extends m_base {
    public function render($content=''){
        self::$title[] = 'Table Structure';
        
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
        
        $is_success = FALSE;
        $error = NULL;
        $trace = NULL;
        
        if(IS_POST){
            $names = isset($_POST['names']) ? $_POST['names'] : array();
            $action = isset($_POST['action']) ? $_POST['action'] : NULL;
            
            $c = new Client();
            try{
                if(self::$source){
                    table_update_base(self::$source['name'], $_POST['info']);
                    if(self::$source['name']!= $_POST['info']['name']){
                        $_GET['source'] = $_POST['info']['name'];
                        $url = $_SERVER['PHP_SELF'].'?'. http_build_query($_GET);
                        header('Location:'.$url, TRUE, 301);
                        return '';
                    }
                }else{
                    table_create($_POST['info']);
                }
                $is_success = TRUE;
            }catch(Exception $e){
                $error = $e->getMessage();
                $trace = $e->getTraceAsString();
            }
        }
        
        if(!self::$source && $error===NULL){
            $info['types'] = table_types('NORMAL');
            $info['cols'] = array_fill(0, 3, table_blank_column());
            $info['name'] = '';
            $info['type'] = 'NORMAL';
            $source = '';
        }elseif($error){
            $info = $_POST['info'];
            $source = $info['name'];
            $info['types'] = table_types($info['type']);
        }else{
            $info = table_base(self::$source['name']);
            $info['types'] = table_types($info['type']);
            $source = self::$source['name'];
        }
        
        $data = array();
        $data['is_success'] = $is_success;
        $data['error'] = $error;
        $data['trace'] = $trace;
        
        $data['is_editable'] = self::$source['name']!='sqlite_master';
        $data['source'] = $source;
        $data['info'] = array($info);
        $data['orders'] = $orders;
        $data['collations'] = $collations;
        $data['resolutions'] = $resolutions;
        $data['item-blank'] = table_blank_column();
        
        $name = self::$source ? 'm_schema' : 'm_source';
        return self::merge($name, $data, 'table-item.tpl');
    }
}
?>

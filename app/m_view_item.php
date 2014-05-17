<?php
require_once P_PATH.'/app/lib/view.php';

class m_view_item extends m_base {
    public function render($content=''){
        self::$title[] = 'View Item';
        
        $name = '';
        $select = '';
        
        $is_success = FALSE;
        $error = NULL;
        $trace = NULL;
        
        if(IS_POST){
            $c = new Client();
            $name = $c->post('name', 't=str&min=1');
            $select = $c->post('select', 't=str');
            if(!$c->has_errors){
                try{
                    if(self::$source){
                        view_update(self::$source['name'], $name, $select);
                    }else{
                        view_addnew($name, $select);
                    }
                    
                    $columns = view_columns($name);
                    $is_success = TRUE;
                }catch(Exception $e){
                    $error = $e->getMessage();
                    $trace = $e->getTraceAsString();
                    $columns = view_columns(self::$source['name']);
                }
            }
        }else{
            if(self::$source){
                $item = view_item(self::$source['name']);
                $name = $item['name'];
                $select = $item['select'];
                $columns = view_columns(self::$source['name']);
            }else{
                $columns = array();
            }
        }
        
        $data = array();
        $data['is_success'] = $is_success;
        $data['error'] = $error;
        $data['trace'] = $trace;
        
        $data['is_edit'] = self::$source!=NULL;
        $data['columns'] = $columns;
        $data['name'] = $name;
        $data['select'] = $select;
        
        
        $name = self::$source ? 'm_schema' : 'm_source';
        return self::merge($name, $data, 'view-item.tpl');
    }
}
?>

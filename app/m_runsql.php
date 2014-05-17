<?php
require_once P_PATH.'/app/lib/content.php';

class m_runsql extends m_base {
    public function render($content=''){
        self::$title[] = 'Run SQL';
        
        $statement = NULL;
        $content_data = NULL;
        
        $is_success = FALSE;
        $error = NULL;
        $trace = NULL;
        
        $affected = 0;
        if(IS_POST){
            $statement = $_POST['statement'];
            try{
                $content_data = content_query($statement);
                $affected  = content_affected(); 
                $is_success = TRUE;
            }catch(Exception $e){
                $error = $e->getMessage();
                $trace = $e->getTraceAsString();
            }
        }
        
        require_once P_PATH.'/etc/snippets.php';
        if(self::$source){
            $items = array();
            foreach(table_columns(self::$source['name']) as $item){
                $items[] = array(
                    'title' => $item['name'],
                    'value' => "[{$item['name']}],"
                );
            }
            $_SNIPPETS[] = array(
                'title' => '@columns',
                'value' => $items
            );
        }
        
        $data = array();
        $data['is_success'] = $is_success;
        $data['error'] = $error;
        $data['trace'] = $trace;
        
        $data['result'] = $error ? $error : 'success';
        $data['statement'] = $statement;
        $data['content_data'] = $content_data;
        $data['columns'] = content_columns($content_data);
        $data['affected'] = $affected;
        $data['snippets'] = $_SNIPPETS;
        
        return self::$source ? 
            self::merge('m_source', $data, 'runsql.tpl') :
            self::merge('m_global', $data, 'runsql.tpl');
    }
}
?>

<?php
require_once P_PATH.'/app/lib/table.php';
require_once P_PATH.'/app/lib/trigger.php';
require_once P_PATH.'/app/lib/export.php';

class m_export extends m_base {
    public function render($content=''){
        self::$title[] = 'Export';
        
        $export_content = NULL;
        $c = new Client();
        $type = $c->get('type', 't=str&default=sql');
        $is_download = FALSE;
        $sql_mode = isset($_GET['sql-mode']) && $_GET['sql-mode'];
        $sql_code = isset($_GET['sql-code']) ? $_GET['sql-code'] : '';
        
        $is_success = FALSE;
        $error = NULL;
        $trace = NULL;
            
        if(IS_POST){
            try{
                $fpath = tempnam(P_TEMP, 'ex-');
                $encode = $_POST['output']=='text' ? 'UTF-8' : $c->post('encode', 't=str&default=utf-8');
                if($sql_mode){
                    $sql_code = $c->post('sql-code', array('t'=>'str', 'default'=>NULL));
                    $contents = array(array('code'=> $sql_code)); 
                    $schemas = array();
                }else{
                    $contents = $c->post('contents', array('t'=>'str', 'level'=>1, 'default'=>array()));
                    $schemas = $c->post('schemas', array('t'=>'str', 'level'=>1, 'default'=>array()));
                }
                $compress = $c->post('compress', 't=str&default=txt');
                $opts = isset($_POST['opts']) ? $_POST['opts'] : array();
                
                register_shutdown_function('unlink', $fpath);
                export_file($fpath, $encode, $schemas, $contents, $type, $opts, $compress);
                
                if($_POST['output']=='text'){
                    $export_content = file_get_contents($fpath);
                }else{
                    $source = self::$source ? self::$source['name']: 'database';
                    $filename = $compress=='txt' ? "$source.$type" : "$source.$type.$compress";
                    header("Content-Disposition: attachment; filename=\"$filename\"");
                    header('Content-Type: application/octet-stream');
                    header('Content-Transfer-Encoding: binary'); 
                    header("Content-Length:".filesize($fpath));
                    
                    ob_end_clean();
                    readfile($fpath);
                    $is_download = TRUE;
                }
                $is_success = TRUE;
            }catch(Exception $e){
                 $error = $e->getMessage();
                 $trace = $e->getTraceAsString();
            }
        }
        
        if(!$is_download){
            if(isset(self::$source['name'])){
                $tabs = array(
                    'sql' => array('type'=>'sql', 'title'=>'SQL'),
                    'xml' => array('type'=>'xml', 'title'=>'XML'),
                    'csv' => array('type'=>'csv', 'title'=>'CSV')
                );
            }else{
                $tabs = array(
                    'sql' => array('type'=>'sql', 'title'=>'SQL'),
                    'xml' => array('type'=>'xml', 'title'=>'XML')
                );
            }
            
            $type = isset($_GET['type']) && isset($tabs[$_GET['type']]) ? $_GET['type'] : 'sql';
            $tabs[$type]['active'] = TRUE;
            
            $data = array();
            $data['is_success'] = $is_success;
            $data['error'] = $error;
            $data['trace'] = $trace;
            
            $data['type'] = $type;
            $data['tabs'] = $tabs;
            $data['export_content'] = $export_content;
            
            require P_PATH.'/etc/encodings.php';
            $data['encodings'] = $_ENCODINGS;

            require P_PATH.'/etc/snippets.php';
            $data['snippets'] = $_SNIPPETS;
            $data['sql-mode'] = $sql_mode;
            $data['sql-code'] = $sql_code;
            
            if(isset(self::$source['name'])){
                $data['source_type'] = self::$source['type'];
                $data['source_name'] = self::$source['name'];
                $data['source'] = self::$source['name'];
                
                return self::merge('m_source', $data, 'export.tpl');
                
            }else{
                $data['tables'] = source_list('table','name,type', FALSE);
                $data['views'] = source_list('view');
                
                $data['source_type'] = '';
                $data['source_name'] = '';
                $data['source'] = NULL;
                
                return self::merge('m_global', $data, 'export.tpl');
            }
        }
    }
}
?>

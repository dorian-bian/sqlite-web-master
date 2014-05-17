<?php
require_once P_PATH.'/app/lib/import.php';

class m_import extends m_base {
    public function render($content=''){
        self::$title[] = 'Import';
        
        $c = new Client();
        
        $source = self::$source ? self::$source['name'] : NULL;
        
        $is_success = FALSE;
        $error = NULL;
        $trace = NULL;
        
        if(IS_POST){
            $type = $c->get('type', 't=str&default=sql');
            $opts = isset($_POST['opts']) ? $_POST['opts'] : array();
            $mode = isset($_POST['mode']) ? $_POST['mode'] : 'file';
            $encode = $c->post('encode', 't=str&default=utf-8');
            try{
                if($mode=='file' || IMPORT_STRICT_MODE){
                    if(!empty($_FILES)){
                        $path = $_FILES['file']['tmp_name'];
                        $name = $_FILES['file']['name'];
                    }else{
                        throw new Exception('No Data Import.');
                    }
                }else{
                    $path = $_POST['path'];
                    $name = basename($path);
                }
                
                import_file($name, $path, $type, $encode, $source, $opts);
                $is_success = TRUE;
            }catch(Exception $e){
                $error = $e->getMessage();
                $trace = $e->getTraceAsString();
            }
        }
        
       
        $tabs = array(
            'sql' => array('type'=>'sql', 'title'=>'SQL'),
            'xml' => array('type'=>'xml', 'title'=>'XML'),
            'csv' => array('type'=>'csv', 'title'=>'CSV')
        );

        
        if(extension_loaded('com_dotnet')){
            $tabs += array(
                'mdb' => array('type'=>'mdb', 'title'=>'MDB'),
                'xls' => array('type'=>'xls', 'title'=>'XLS')
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
        
        $data['is_strict'] = IMPORT_STRICT_MODE;
        
        $data['upload_max_filesize'] = ini_get('upload_max_filesize');
        
        require P_PATH.'/etc/encodings.php';
        $data['encodings'] = $_ENCODINGS;
        
        $data['source_name'] = self::$source ? self::$source['name'] : NULL;
        
        $name = self::$source ? 'm_source' : 'm_global';
        return self::merge($name, $data, 'import.tpl');
    }
}
?>

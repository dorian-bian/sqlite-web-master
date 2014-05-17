<?php
require_once P_PATH.'/app/lib/content.php';

class m_content_item extends m_base {
    
    public function render($content=''){
        self::$title[] = 'Content Item';
        
        $rowid = isset($_GET['rowid']) ? $_GET['rowid'] : NULL;
        
        if(isset($_GET['col-name'])){
            $field = $_GET['col-name'];
            $data = content_value(self::$source['name'], $rowid, $field);
            switch($_GET['col-type']){
            case 'bin':
                header("Content-Type: application/octet-stream");
                header("Content-Disposition: attachment; filename=\"$field\"");
                header("Content-Length: " .strlen($data));
                break;
            case 'hex':
                $data = bin2hex($data);
                header("Content-Type: text/plain");
                header("Content-Length: " .strlen($data));
                break;
            case 'img':
                header("Content-Length: " .strlen($data));
                break;
            }
            ob_get_clean();
            return $data;
        }else{
            $is_success = FALSE;
            $error = NULL;
            $trace = NULL;
            
            $table = self::$source['name'];
            
            if(IS_POST){
                try {
                    $files = isset($_FILES['files']) ? $_FILES['files'] : array();
                    content_save($table, $rowid, $_POST['info'], $files);
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
            
            $data['table'] = $table;
            $data['is_update'] = $rowid!==NULL;
            $data['types'] = content_types();
            
            $data['cols'] = $rowid ? content_item($table, $rowid) : content_default($table);
            $data['return'] = isset($_GET['return']) ? urldecode($_GET['return']) : '';
            
            return self::merge('m_source', $data, 'content-item.tpl');
        }
    }
}
?>

<?php
require_once P_PATH.'/app/lib/pragma.php';

class m_pragma extends m_base {
    public $pragmas = array(
        array(
            'title'=> 'Versions', 
            'items'=> array(
                array(
                    'title' => 'Schema Version',
                    'name' => 'schema_version',
                    'type' => 'int',
                    'field' => 'input.int'
                ),
                array(
                    'title' => 'User Version',
                    'name' => 'user_version',
                    'type' => 'int',
                    'field' => 'input.int'
                )
            )
        ),
        array(
            'title'=> 'Pages', 
            'items'=> array(
                array(
                    'title' => 'Page Count',
                    'name' => 'page_count',
                    'type' => 'int',
                    'field' => 'text',
                ),
                array(
                    'title' => 'Freelist Count',
                    'name' => 'freelist_count',
                    'type' => 'int',
                    'field' => 'text',
                ),
                array(
                    'title' => 'Page Size(bytes)',
                    'name' => 'page_size',
                    'type' => 'int',
                    'field' => 'input.int'
                ),
                array(
                    'title' => 'Max Page Count',
                    'name' => 'max_page_count',
                    'type' => 'int',
                    'field' => 'input.int'
                )
            )
        ),
        array(
            'title'=> 'Journals', 
            'items'=> array(
                array(
                    'title' => 'Journal Mode',
                    'name' => 'journal_mode',
                    'type' => 'str',
                    'field' => 'select',
                    'options' => array(
                        array('title'=>'DELETE', 'value'=>'delete'),
                        array('title'=>'TRUNCATE', 'value'=>'truncate'),
                        array('title'=>'PERSIST', 'value'=>'persist'),
                        array('title'=>'MEMORY', 'value'=>'memory'),
                        array('title'=>'WAL', 'value'=>'wal'),
                        array('title'=>'OFF', 'value'=>'off')
                    )
                )
                ,
                array(
                    'title' => 'Journal Size Limit',
                    'name' => 'journal_size_limit',
                    'type' => 'int',
                    'field' => 'input.int'
                )
            )
        ),
        array(
            'title'=> 'Others', 
            'items'=> array(
                array(
                    'title' => 'Auto Vacuum',
                    'name' => 'auto_vacuum',
                    'type' => 'int',
                    'field' => 'select',
                    'options' => array(
                        array('title'=>'NONE', 'value'=>'0'),
                        array('title'=>'FULL', 'value'=>'1'),
                        array('title'=>'INCREMENTAL', 'value'=>'2')
                    )
                ),
                array(
                    'title' => 'Automatic Indexing',
                    'name' => 'automatic_index',
                    'type' => 'bool',
                    'field' => 'input.check'
                ),
                array(
                    'title' => 'Cache Size',
                    'name' => 'cache_size',
                    'type' => 'int',
                    'field' => 'input.int'
                ),
                array(
                    'title' => 'Encoding',
                    'name' => 'encoding',
                    'type' => 'str',
                    'field' => 'select',
                    'options' => array(
                        array('title'=>'UTF-8', 'value'=>'utf-8'),
                        array('title'=>'UTF-16', 'value'=>'utf-16'),
                        array('title'=>'UTF-16le', 'value'=>'utf-16le'),
                        array('title'=>'UTF-16be', 'value'=>'utf-16be')
                    )
                ),
                array(
                    'title' => 'Foreign Keys',
                    'name' => 'foreign_keys',
                    'type' => 'int',
                    'field' => 'input.check'
                ),
                array(
                    'title' => 'Fullsync flag',
                    'name' => 'fullfsync',
                    'type' => 'int',
                    'field' => 'input.check'
                ),
                array(
                    'title' => 'Legacy File Format',
                    'name' => 'legacy_file_format',
                    'type' => 'int',
                    'field' => 'input.check'
                ),
                array(
                    'title' => 'Locking Mode',
                    'name' => 'locking_mode',
                    'type' => 'str',
                    'field' => 'select',
                    'options' => array(
                        array('title'=>'NORMAL', 'value'=>'normal'),
                        array('title'=>'EXCLUSIVE', 'value'=>'exclusive')
                    )
                ),
                array(
                    'title' => 'Read Uncommitted',
                    'name' => 'read_uncommitted',
                    'type' => 'int',
                    'field' => 'input.check'
                ),
                array(
                    'title' => 'Recursive Triggers',
                    'name' => 'recursive_triggers',
                    'type' => 'int',
                    'field' => 'input.check'
                ),
                array(
                    'title' => 'Reverse Unordered Selects',
                    'name' => 'reverse_unordered_selects',
                    'type' => 'int',
                    'field' => 'input.check'
                ),
                array(
                    'title' => 'Secure Delete',
                    'name' => 'secure_delete',
                    'type' => 'int',
                    'field' => 'input.check'
                ),
                array(
                    'title' => 'Size of write-ahead log',
                    'name' => 'wal_autocheckpoint',
                    'type' => 'int',
                    'field' => 'input.int'
                ),
                array(
                    'title' => 'Synchronous',
                    'name' => 'synchronous',
                    'type' => 'int',
                    'field' => 'input.int',
                    'options' => array(
                        array('title'=>'OFF', 'value'=>'0'),
                        array('title'=>'NORMAL', 'value'=>'1'),
                        array('title'=>'FULL', 'value'=>'2')
                    )
                ),
                array(
                    'title' => 'Temporary Store',
                    'name' => 'temp_store',
                    'type' => 'int',
                    'field' => 'select',
                    'options' => array(
                        array('title'=>'DEFAULT', 'value'=>'0'),
                        array('title'=>'FILE', 'value'=>'1'),
                        array('title'=>'MEMORY', 'value'=>'2')
                    )
                )
            )
        )
    );
    
    public function render($content=''){
        self::$title[] = 'Pragma';
        
        $is_success = FALSE;
        $error = NULL;
        $trace = NULL;
        
        if(IS_POST){
            try{
                $this->save_data();
                $is_success = TRUE;
            }catch(Exception $e){
                $error = $e->getMessage();
                $trace = $e->getTraceAsString();
            }
        }
        $this->load_data();
        
        $data = array();
        $data['is_success'] = $is_success;
        $data['error'] = $error;
        $data['trace'] = $trace;
        
        $data['groups'] = $this->pragmas;
        
        return self::merge('m_global', $data, 'pragma.tpl');
    }
    
    public function load_data(){
        foreach($this->pragmas as &$group){
            foreach($group['items'] as &$item){
                $item['value'] = pragma_get($item['name']);
                switch($item['field']){
                case 'select':
                    foreach($item['options'] as &$option){
                        $option['active'] = $option['value'] == strtolower($item['value']);
                    }
                    break;
                case 'input.check':
                    $item['active'] = $item['value'] != 0;
                    break;
                }
            }
        }
    }
    
    public function save_data(){
        foreach($this->pragmas as &$group){
            foreach($group['items'] as &$item){
                $item['value'] = pragma_get($item['name']);
                if($item['field']!='text'){
                    $value = isset($_POST[$item['name']]) ? $_POST[$item['name']] : '';
                    if($item['type']=='int') $value = (int)$value;
                    pragma_set($item['name'], $value);
                }
            }
        }
    }
}
?>

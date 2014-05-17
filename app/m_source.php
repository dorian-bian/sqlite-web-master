<?php
require_once P_PATH.'/app/lib/trigger.php';
require_once P_PATH.'/app/lib/index.php';

class m_source extends m_base {
    public function render($content=''){
        self::$title[] = 'Source Item';
        
        $source_type = NULL;
        $source_name = NULL;
        
        if(is_null(self::$source)){
            if(self::$i == 'table-item'){
                $source_type = 'table';
                $tabs = array(
                    'table-list' => array('i'=>'table-list', 'title'=>'Tables'),
                    'table-item' => array('i'=>'table-item', 'title'=>'Structure')
                );
            }else{
                $source_type = 'view';
                $tabs = array(
                    'view-list' => array('i'=>'view-list', 'title'=>'Views'),
                    'view-item' => array('i'=>'view-item', 'title'=>'Structure')
                );
            }
        }else{
            $source_name = self::$source['name'];
            $source_type = self::$source['type'];
            $t_count = trigger_count($source_name);
            if($source_type=='table'){
                $tabs = array(
                    'content-list' => array('i'=>'content-list', 'title'=>'Browse'),
                    'table-item' => array('i'=>'table-item', 'title'=>'Schema'),
                    'export' => array('i'=>'export', 'title'=>'Export'),
                    'import' => array('i'=>'import', 'title'=>'Import'),
                    'runsql' => array('i'=>'runsql', 'title'=>'RunSQL'),
                    'manage' => array('i'=>'manage', 'title'=>'Manage')
                );
            }else{
                $tabs = array(
                    'content-list' => array('i'=>'content-list', 'title'=>'Browse'),
                    'view-item' => array('i'=>'view-item', 'title'=>'Schema'),
                    'export' => array('i'=>'export', 'title'=>'Export'),
                    'import' => array('i'=>'import', 'title'=>'Import'),
                    'runsql' => array('i'=>'runsql', 'title'=>'RunSQL')
                );
            }
        }
        
        if(isset($tabs[self::$i])){
            $tabs[self::$i]['active'] = isset($tabs[self::$i]);
        }elseif(in_array(self::$i, array('trigger-list', 'index-list', 'constraint'))){
            $tabs[$source_type.'-item']['active'] = TRUE;
        }
        
        $data = array(
            'source_name' => $source_name,
            'source_type' => $source_type,
            'tabs' => $tabs,
            'content' => $content
        );
        
        return self::merge('m_main', $data, 'source.tpl');
    }
}
?>

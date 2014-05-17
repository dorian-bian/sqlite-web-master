<?php
class m_global extends m_base {
    public function render($content=''){
        self::$title[] = 'Global';
        
        $tabs = array(
            'system' => array('i'=>'system', 'title'=>'System'),
            'pragma' => array('i'=>'pragma', 'title'=>'Pragma'),
            'table-list' => array('i'=>'table-list', 'title' => 'Tables'),
            'view-list' => array('i'=>'view-list', 'title' => 'Views'),
            'export' => array('i'=>'export', 'title'=>'Export'),
            'import' => array('i'=>'import', 'title'=>'Import'),
            'runsql' => array('i'=>'runsql', 'title'=>'RunSQL')
        );
        
        $sub = isset($tabs[self::$i]) && isset($tabs[self::$i]) ? self::$i : 'system';
        $tabs[$sub]['active'] = TRUE;
        
        $data = array();
        $data['tabs'] = $tabs;
        $data['content'] = $content;
        return self::merge('m_main', $data, 'global.tpl');
    }
}
?>

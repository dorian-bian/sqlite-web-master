<?php
require_once P_PATH.'/app/lib/import.php';

class m_schema extends m_base {
    public function render($content=''){
        self::$title[] = 'Schema';
        
        $source_name = self::$source['name'];
        
        $tabs = self::$source['type']=='table' ?
            array(
                'table-item' => array('i' =>'table-item', 'title'=>'Structure'),
                'constraint' => array('i' =>'constraint', 'title'=>'Constraint'),
                'index-list' => array('i' =>'index-list', 'title'=>'Indices'),
                'trigger-list' => array('i' =>'trigger-list', 'title'=>'Triggers')
            ) :
            array(
                'view-item' => array('i' =>'view-item', 'title'=>'Structure'),
                'trigger-list' => array('i' =>'trigger-list', 'title'=>'Triggers')
            );
        
        if(isset($tabs[self::$i])){
            $tabs[self::$i]['active'] = TRUE;
        }else{
            if(self::$source['type']=='table'){
                $tabs['table-item']['active'] = TRUE;
            }elseif(self::$source['type']=='view'){
                $tabs['view-item']['active'] = TRUE;
            }
        }
        
        $data = array(
            'tabs' => $tabs,
            'source_name' => $source_name,
            'content' => $content
        );
        return self::merge('m_source', $data, 'schema.tpl');
    }
}
?>

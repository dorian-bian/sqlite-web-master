<?php
require_once P_PATH.'/app/lib/source.php';
require_once P_PATH.'/app/lib/view.php';

class m_main extends m_base {
    public $menu = array(
        'global' => array('i'=>'global', 'title'=>'Global', 'icon'=>'icon-home', 'subs'=>array()),
        'table-list' => array('i'=>'table-list','title'=>'Tables (%d)', 'icon'=>'icon-folder-open', 'subs'=>array()),
        'view-list' => array('i'=>'view-list', 'title'=>'Views (%d)', 'icon'=>'icon-folder-close', 'subs'=>array())
    );
    
    public function render($content=''){
        self::$title[] = 'Sqlite Web Master';
        
        $databases = database_list();
        $db_groups = database_groups();
        
        $node = isset($this->menu[self::$i]) ? self::$i : 'global';
        
        if(!self::$source){
            $this->menu[$node]['active'] = ' class="active"';
        }
        
        if(self::$i=='view-list' || self::$source && self::$source['type']=='view'){
            $this->menu['view-list']['icon'] = 'icon-folder-open';
            $this->menu['table-list']['icon'] = 'icon-folder-close';
        }
        
        $this->prepare_subs('table-list');
        $this->prepare_subs('view-list');
        
        $data = array(
            'title' => implode(' - ', array_reverse(self::$title)),
            'dbname' => self::$database,
            'databases' => $databases,
            'db_groups' => $db_groups,
            'menu' => $this->menu,
            'content' => $content,
            'consume_time' => sprintf('%.3fs', microtime(TRUE) - START_TIME)
        );
        return self::merge(NULL, $data, 'main.tpl');
    }
    
    public function prepare_subs($name, $title_len=15){
        $items = $name=='table-list' ? source_list('table') : source_list('view');
        $icon = 'icon-file';
        
        $node = 'content-list';
        
        if($name=='table-list' && self::$i=='table-item') $node = 'table-item';
        elseif($name=='view-list' && self::$i=='view-item') $node = 'view-item';
        
        $this->menu[$name]['title'] = sprintf($this->menu[$name]['title'], count($items));
        foreach($items as &$item){
            $item['active'] = self::$source && self::$source['name']==$item['name'];
            $item['i'] = $node;
            $item['title'] = $item['name']; 
            $item['icon'] = $icon;
        }
        
        $this->menu[$name]['subs'] = $items;
    }
}
?>

<?php
function source_list($type, $cols='name, type', $with_sqlite=TRUE){
    $where = $with_sqlite ? 'type=:type' : 'type=:type AND name NOT LIKE "sqlite_%"';
    
    $items = Database::instance()->select_list('sqlite_master', $cols,
        $where, 'name COLLATE NOCASE ASC', array('type'=>$type));
    
    if($with_sqlite && $type=='table'){
        array_unshift($items, array('name'=>'sqlite_master', 'type'=>'table'));
    }
    return $items;
}

function source_type($name){
    if($name=='sqlite_master'){
        return 'table';
    }else{
        return Database::instance()->select_value('sqlite_master', 'type',
            'name=:name', array('name'=>$name));
    }
}

function source_item($name){
    if($name=='sqlite_master'){
        return array('name'=>'sqlite_master', 'type'=>'table');
    }else{
        return Database::instance()->select_item('sqlite_master', 'name, type', 
            'name=:name AND type IN("view", "table")', array('name'=>$name));
    }
}

function source_exists($name){
    return Database::instance()->exists('sqlite_master', 
        'name=:name AND type IN ("view", "table")', array('name'=>$name));
}
?>

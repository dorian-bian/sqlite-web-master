<?php
require_once P_PATH.'/app/lib/table.php';
require_once P_PATH.'/app/lib/sql/viewparser.php';

function view_list($cols='name', $with_count=FALSE){
    $database = Database::instance();
    $items = $database->select_list('sqlite_master', $cols, 
        'type=:type','name COLLATE NOCASE ASC', array('type'=>'view'));
    foreach($items as &$item){
        $name = $database->quote($item['name']);
        if($with_count){
            if($database->exists('sqlite_master', 'name=:name', array('name'=>$item['name']))){
                $item['rows'] = $database->select_value($name, 'COUNT(*)');
            }else{
                $item['rows'] = 'NaN';
            }
        }
        
        if(isset($item['sql'])){
            $parser = new ViewParser($item['sql']);
            $item['code'] = $parser->data['statement'];
        }
    }
    
    return $items;
}

function view_columns($name){
    return table_columns($name);
}

function view_item($name){
    $database = Database::instance();
    $item = Database::instance()->select_item('sqlite_master', '*', 
        'type=:type AND name=:name', array('type'=>'view', 'name'=>$name));
    if(isset($item['sql'])){
        $parser = new ViewParser($item['sql']);
        $item['select'] =  $parser->data['statement'];
    }
    return $item;
}

function view_drop($names){
    $database = Database::instance();
    foreach((array)$names as $name){
        $database->execute('DROP VIEW IF EXISTS '.$database->quote($name));
    }
}

function view_create($data){
    $parser = new ViewParser();
    $cmd = $parser->build($data);
    Database::instance()->execute($cmd);
}

function view_addnew($name, $select){
    $database = Database::instance();
    $parser = new ViewParser();
    $data = array(
        'name' => $name,
        'statement' => $select
    );
    $sql = $parser->build($data);
    Database::instance()->execute($sql);
}

function view_update($old_name, $new_name, $select){
    // $triggers = trigger_save($name);
    
    view_drop($old_name);
    view_addnew($new_name, $select);
    // trigger_save($name, $triggers);
}
?>

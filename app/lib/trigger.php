<?php
require_once P_PATH.'/app/lib/sql/triggerparser.php';

function trigger_list($table_name=NULL, $cols = 'name, sql, tbl_name'){
    if($table_name){
        $items = Database::instance()->select_list('sqlite_master', $cols,
            'type=:type AND tbl_name=:tbl_name','name', array('type'=>'trigger', 'tbl_name'=>$table_name));
    }else{
        $items = Database::instance()->select_list('sqlite_master', $cols, 'type="trigger"');
    }
    
    foreach($items as &$item){
        $parser = new TriggerParser($item['sql']);
        $item += $parser->data;
    }
    return $items;
}

function trigger_info($name){
    $sql = Database::instance()->select_value('sqlite_master', 'sql', 'name=:name',array('name'=>$name));
    $parser = new TriggerParser($sql);
    return $parser->data;
}

function trigger_count($table_name){
    return Database::instance()->select_value('sqlite_master', 'COUNT(*)', 
        'type=:type AND tbl_name=:tbl_name', array('type'=>'trigger', 'tbl_name'=>$table_name));
}

function trigger_drop($names){
    $database = Database::instance();
    foreach((array)$names as $name){
        $database->execute('DROP TRIGGER IF EXISTS '.$database->quote($name));
    }
}

function trigger_create($data){
    $parser = new TriggerParser(NULL);
    $cmd = $parser->build($data);
    Database::instance()->execute($cmd);
}

function trigger_migrate($name, $data){
    $database = Database::instance();
    try {
        $database->begin('EXCLUSIVE');
        $database->execute('DROP TRIGGER IF EXISTS '.$database->quote($name));
        $parser = new TriggerParser(NULL);
        $cmd = $parser->build($data);
        $database->execute($cmd);
        $database->commit();
    }catch(Exception $e){
        if($e->getCode()==10001){
            $database->rollback();
        }
        throw $e;
    }
}
?>

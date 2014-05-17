<?php
require_once P_PATH.'/app/lib/sql/indexparser.php';

function index_exists($name){
    return Database::instance()->exists('sqlite_master', 'type=:type AND name=:name',
        array('type'=>'index', 'name'=>$name));
}

function index_list($table){
    $database = Database::instance();
    $items = array();
    $indices = $database->select_list('sqlite_master', 'sql',
        'tbl_name=:name AND type=:type AND sql IS NOT NULL', 'name', array('name'=>$table, 'type'=>'index'));
    foreach($indices as $index){
        $parser = new IndexParser($index['sql']);
        $item = $parser->data;
        $item['type'] = 'INDEX';
        $items[] = $item;
    }
    
    return $items;
}

function index_count($table){
    return Database::instance()->select_value('sqlite_master', 'COUNT(*)', 
        'type=:type AND tbl_name=:tbl_name', array('type'=>'table','tbl_name'=>$table));
}

function index_info($name){
    $sql = Database::instance()->select_value('sqlite_master', 'sql', 'name=:name',array('name'=>$name));
    $parser = new IndexParser($sql);
    return $parser->data;
}

function index_migrate($name, $data){
    $database = Database::instance();
    try {
        $database->begin('EXCLUSIVE');
        $database->execute('DROP INDEX IF EXISTS '.$database->quote($name));
        $parser = new IndexParser(NULL);
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

function index_create($data){
    $parser = new IndexParser(NULL);
    $cmd = $parser->build($data);
    Database::instance()->execute($cmd);
}

function index_drop($names){
    $database = Database::instance();
    foreach((array)$names as $name){
        $database->execute('DROP INDEX IF EXISTS '.$database->quote($name));
    }
}
?>

<?php
require_once P_PATH.'/app/lib/index.php';
require_once P_PATH.'/app/lib/sql/tableparser.php';

require_once P_PATH.'/lib/lorem.php';

function table_list($cols='name', $with_count=FALSE, $with_sqlite=TRUE){
    $database = Database::instance();
    $where = $with_sqlite ? 'type=:type' : 'type=:type AND name NOT LIKE "sqlite_%"';
    $items = $database->select_list('sqlite_master', $cols, 
        $where,'name COLLATE NOCASE ASC', array('type'=>'table'));
    if($with_sqlite) array_unshift($items, array('name'=>'sqlite_master', 'type'=>'table'));
    foreach($items as &$item){
        if($with_count) $item['rows'] = $database->select_value($database->quote($item['name']), 'COUNT(*)');
        if(isset($item['sql'])) $item['code'] = array_pop(explode(' AS ', $item['sql'], 2));
    }
    return $items;
}

function table_count($database_path=NULL){
    $database = Database::instance($database_path);
    return $database->select_value('sqlite_master', 'COUNT(*)', 'type=:type', array('type'=>'table'));
}

function table_types($current=NULL){
    $database = Database::instance();
    $items = $database->execute('PRAGMA compile_options');
    
    $types = array(
        'NORMAL' => array('title'=> 'NORMAL', 'value'=>'NORMAL', 'active'=>'NORMAL'==$current)
    );
    
    foreach($items as $item){
        $option = trim(strtoupper($item['compile_option']));
        switch($option){
        case 'ENABLE_FTS3':
        case 'ENABLE_FTS4':
        case 'ENABLE_RTREE':
            $name = substr($option, 7);
            $types[$name] = array('title'=> $name, 'value'=>$name, 'active'=>$name==$current); 
            break;
        }
    }
    
    if(!isset($types[$current])){
        $types[$current] = array('title'=> $current, 'value'=> $current, 'active'=>TRUE);
    }
    return $types;
}

function table_meta($name){
    $database = Database::instance();
    $cols = $database->execute('PRAGMA table_info('.$database->quote($name).')');
    
    $meta = array();
    foreach($cols as $i=>$col){
        $meta[$col['name']] = table_column_type($col['type']);
    }
    
    return $meta;
}

function table_code($name){
    if($name=='sql_master'){
        $code = "CREATE TABLE sqlite_master (\n".
            "  type text,\n".
            "  name text,\n".
            "  tbl_name text,\n".
            "  rootpage integer,\n".
            "  sql text\n".
            ")";
        return $code;
    }else{
        $items = Database::instance()->select_list('sqlite_master', 'sql', 
            'tbl_name=:name', NULL, array('name'=>$name));
        $codes = array();
        
        foreach($items as $item){
            $codes[] = $item['sql'];
        }
        return implode(";\n", $codes);
    }
}

function table_columns($name){
    $database = Database::instance();
    $columns = array();
    $items = $database->execute('PRAGMA table_info('.$database->quote($name).')');
    foreach($items as $i=>$item){
        $columns[$item['name']] = array(
            'i'    => $i,
            'name' => $item['name'],
            'type' => $item['type'],
            'pkey' => $item['pk']==1,
            'dflt' => $item['dflt_value'],
            'nnul' => $item['notnull']
        );
    }
    return $columns;
}

function table_info($name){
    if($name=='sqlite_master'){
        $sql = "CREATE TABLE sqlite_master (\n".
            "  type text,\n".
            "  name text,\n".
            "  tbl_name text,\n".
            "  rootpage integer,\n".
            "  sql text\n".
            ")";
    }else{
        $sql = Database::instance()->select_value('sqlite_master', 'sql', 
            'type=:type AND name=:name', array('type'=>'table','name'=>$name));
    }
    $parser = new TableParser($sql);
    
    
    if(in_array($parser->data['type'],array('NORMAL','RTREE', 'FTS3', 'FTS4'))){
        return $parser->data;
    }else{ # fallback --------------------------------------------------
       
        $database = Database::instance();
        $old_cols = $database->execute('PRAGMA table_info('.$database->quote($name).')');
        $new_cols = array();
        
        foreach($old_cols as $i=>$col){
            $new_cols[$col['name']] = array(
                'i' => $i,
                'name' => $col['name'],
                'type' => $col['type'],
                
                'default' => $col['dflt_value'],
                'collation' => 'BINARY',
                
                'cons' => array(
                    'n' => array(
                        'enabled' => $col['notnull'],
                        'on_conflict'=> 'ABORT'),
                        
                    'p' => array(
                        'enabled' => $col['pk'],
                        'order' => 'ASC',
                        'autoincr' => FALSE,
                        'on_conflict' => 'ABORT'),
                    'u' => array(
                        'enabled' => FALSE,
                        'on_conflict' => 'ABORT'),
                    'f' => array(),
                    'c' => array()
                )
            );
        }
        if(count($parser->data['cols'])!=count($new_cols)){
            $parser->data['cols'] = $new_cols;
        }
        return $parser->data;
    }
}

function table_base($name){
    $info = table_info($name);
    if(isset($info['cons']['p'][0])){
        $p = $info['cons']['p'][0];
        foreach($p['cols'] as $col){
            $info['cols'][$col['name']]['cons']['p'] = array(
                'enabled' => TRUE,
                'order' => $col['order'],
                'collation' => $col['collation'],
                'autoincr' => $p['autoincr'],
                'on_conflict' => $p['on_conflict']
            );
        }
    }
    return $info;
}

function table_blank_column(){
    return array(
        'i' => '*',
        'name' => NULL,
        'type' => NULL,
        
        'default' => NULL,
        'collation' => 'BINARY',
        
        'cons' => array(
            'n' => array(
                'enabled' => FALSE,
                'on_conflict'=> 'ABORT'),
                
            'p' => array(
                'enabled' => FALSE,
                'order' => 'ASC',
                'autoincr' => FALSE,
                'on_conflict' => 'ABORT'),
            'u' => array(
                'enabled' => FALSE,
                'on_conflict' => 'ABORT'),
            'f' => array(),
            'c' => array()
        )
    );
}

function table_cons($name){
    $data = table_info($name);
    $cons = array('p'=> array(), 'u'=>array(), 'c'=>array(), 'f'=> array());
    
    foreach($data['cols'] as $col){
        if($col['cons']['p']['enabled']){
            $item = array(
                'rel' => $col['name'],
                'cols' => array(
                    array(
                        'i' => 0,
                        'name' => $col['name'],
                        'order' => $col['cons']['p']['order'],
                        'collation' => $col['collation'])),
                'autoincr' => $col['cons']['p']['autoincr'],
                'on_conflict' => $col['cons']['p']['on_conflict']);
            $cons['p'][] = $item;
        }elseif($col['cons']['u']['enabled']){
            $item = array(
                'rel' => $col['name'],
                'cols' => array(
                    array(
                        'i' => 0,
                        'name' => $col['name'], 
                        'order' => 'ASC', 
                        'collation' => $col['collation'])),
                'on_conflict' => $col['cons']['u']['on_conflict']);
            $cons['u'][] = $item;
        }
        
        foreach($col['cons']['f'] as $item){
            $item['rel'] = $col['name'];
            $item['cols'] = array(array('name'=>$col['name']));
            $cons['f'][] = $item;
        }
    
        foreach($col['cons']['c'] as $item){
            $item['rel'] = $col['name'];
            $cons['c'][] = $item;
        }
        
    }
    $cons['p'] = array_merge($cons['p'], $data['cons']['p']);
    $cons['u'] = array_merge($cons['u'], $data['cons']['u']);
    $cons['c'] = array_merge($cons['c'], $data['cons']['c']);
    $cons['f'] = array_merge($cons['f'], $data['cons']['f']);
    return $cons;
}

function table_foreign_keys($table_name){
    $database = Database::instance();
    $table_name = $database->quote($table_name);
    return $database->execute('PRAGMA foreign_key_list('.$table_name.')');
}

function table_column_type($typename){
    $type = strtoupper($typename);
    if(strpos($type, 'INT')!==FALSE){
        return Database::PARAM_INTEGER;
    }else if(strpos($type, 'CHAR')!==FALSE || 
            strpos($type, 'CLOB')!==FALSE || 
            strpos($type, 'TEXT')!==FALSE){
        return Database::PARAM_TEXT;
    }else if(strpos($type, 'REAL')!==FALSE || 
            strpos($type, 'FLOA')!==FALSE || 
            strpos($type, 'DOUB')!==FALSE){
        return Database::PARAM_FLOAT;
    }else if(strpos($type, 'BLOB')!==FALSE || $type==''){
        return Database::PARAM_BLOB;
    }else{
        return Database::PARAM_TEXT;
    }
}

function table_exists($name){
    return Database::instance()->exists('sqlite_master', 'table="table" AND name='.$database.quote($name));
}

function table_indices($name){
    $database = Database::instance();
    $items = array();
    
    $data = table_info($name);
    foreach($data['cols'] as $col){
        if($col['cons']['p']['enabled']){
            $item = array();
            $item['type'] = 'PRIMARY';
            $col = array(
                'name'=>$col['name'],
                'order'=>'ASC',
                'collation'=>$col['collation']
            );
            $item['cols'] = array($col);
            $item['unique'] = TRUE;
            array_unshift($items, $item);
        }elseif($col['cons']['u']['enabled']){
            $item = array();
            $item['type'] = 'UNIQUE';
            $col = array(
                'name'=>$col['name'],
                'order'=>'ASC',
                'collation'=>$col['collation']
            );
            $item['cols'] = array($col);
            $item['unique'] = TRUE;
            $items[] = $item;
        }
    }
    
    if(isset($data['cons']['p'][0])){
        $item = $data['cons']['p'][0];
        $item['type'] = 'PRIMARY';
        $item['unique'] = TRUE;
        array_unshift($items, $item);
    }
    
    foreach($data['cons']['u'] as $i=>$item){
        $item['type'] = 'UNIQUE';
        $item['unique'] = TRUE;
        $items[] = $item;
    }
    return $items;
}

function table_create($data){
    $parser = new TableParser();
    $cmd = $parser->build($data);
    
    Database::instance()->execute($cmd);
}

function table_migrate($name, $data, $names){
    $database = Database::instance();
    try {
        $database->begin('EXCLUSIVE');
        if($name!=$data['name']){
            table_rename($name, $data['name']);
            $name = $data['name'];
        }
        
        $old_indices = index_list($name);
        $old_triggers = trigger_list($name);
        
        $old_name = $name.'_old_'.time();
        table_rename($name, $old_name);
        table_create($data);
        
        $old_cols = array();
        $new_cols = array();
        foreach($names as $key=>$val){
            $old_cols[] = '"'.str_replace('"','""', $key).'"';
            $new_cols[] = '"'.str_replace('"','""', $val).'"';
        }
        $old_cols = implode(',', $old_cols);
        $new_cols = implode(',', $new_cols);
        $q_new_name = $database->quote($name);
        $q_old_name = $database->quote($old_name);
        $cmd = "INSERT INTO $q_new_name($new_cols) SELECT $old_cols FROM $q_old_name";
        
        $database->execute($cmd);
        $database->execute("DROP TABLE ".$q_old_name);
        
        foreach($old_indices as $item){
            $cols = $item['cols'];
            if($names){
                $cols = array();
                foreach($item['cols'] as $col){
                    if(isset($names[$col['name']])){
                        $col['name'] = $names[$col['name']];
                        $cols[] = $col;
                    }else{
                        $cols = NULL;
                    }
                }
            }
            if($cols){
                $parser = new IndexParser($item['_sql_']);
                $parser->data['cols'] = $cols;
                $cmd = $parser->build($parser->data);
                $database->execute($cmd);
            }
        }
        
        foreach($old_triggers as $item){
            $cols = $item['cols'];
            if($names){
                $cols = array();
                foreach($item['cols'] as $col){
                    if(isset($names[$col['name']])){
                        $col['name'] = $names[$col['name']];
                        $cols[] = $col;
                    }else{
                        $cols = NULL;
                    }
                }
            }
            if($cols){
                $parser = new TriggerParser($item['_sql_']);
                $parser->data['cols'] = $cols;
                $cmd = $parser->build($parser->data);
                $database->execute($cmd);
            }
        }
        
        $database->commit();
    }catch(Exception $e){
        if($e->getCode()==10001){
            $database->rollback();
        }
        throw $e;
    }
}


function table_update_base($name, $new_data){
    $old_data = table_info($name);
    $names = array();
    
    $data = array(
        'name' => $new_data['name'],
        'type' => $new_data['type'],
        'cols' => array(),
        
        'cons' => array(
            'p' => array(),
            'u' => array(),
            'c' => array(),
            'f' => array()
        )
    );
    
    $p = array(
        'enabled' => FALSE,
        'cols' => array(),
        'autoincr' => FALSE,
        'on_conflict' => 'ABORT'
    );
    
    
    foreach($new_data['cols'] as $col){
        if($col['cons']['p']['enabled']){
            $col['cons']['p']['enabled'] = FALSE;
            
            $p['enabled'] = TRUE;
            $p['autoincr'] = $col['cons']['p']['autoincr'];
            $p['on_conflict'] = $col['cons']['p']['on_conflict'];
            
            $p['cols'][] = array(
                'name' => $col['name'],
                'order' => $col['cons']['p']['order'],
                'collation' => $col['collation']
            );
        }
        
        if($col['oldn']){
            $col['cons']['f'] = $old_data['cols'][$col['oldn']]['cons']['f'];
            $col['cons']['c'] = $old_data['cols'][$col['oldn']]['cons']['c'];
            
            $names[$col['oldn']] = $col['name'];
        }else{
            $col['cons']['f'] = array();
            $col['cons']['c'] = array();
        }
        
        $data['cols'][] = $col;
    }
    
    if($p['enabled']) $data['cons']['p'] = array($p);
    
    foreach($old_data['cons']['u'] as $item){
        $save = TRUE;
        foreach($item['cols'] as &$col){
            if(isset($names[$col['name']])){
                $col['name'] = $names[$col['name']];
            }else{
                $save = FALSE;
                break;
            }
        }
        if($save) $data['cons']['u'][] = $item;
    }
    
    foreach($old_data['cons']['f'] as $item){
        $save = TRUE;
        foreach($item['cols'] as &$col){
            if(isset($names[$col['name']])){
                $col['name'] = $names[$col['name']];
            }else{
                $save = FALSE;
                break;
            }
        }
        if($save) $data['cons']['f'][] = $item;
    }
    
    $data['cons']['c'] = $old_data['cons']['c'];
    table_migrate($name, $data, $names);
}

function table_update_cons($name, $action, $type, $rel, $i, $item){
    $info = table_info($name);
    if($rel){
        $cons = &$info['cols'][$rel]['cons'];
    }else{
        $cons = &$info['cons'];
    }
    switch($action){
    case 'append':
        if(isset($item['cols']) && count($item['cols'])==1){
            switch($type){
            case 'p':
                $col =  $item['cols'][0];
                $con = array(
                    'enabled' => TRUE,
                    'order' => $col['order'],
                    'autoincr' => FALSE,
                    'collation' => $col['collation'],
                    'on_conflict' => $item['on_conflict']);
                $info['cols'][$col['name']]['cons'][$type] = $con;
                break;
            case 'u':
                $col =  $item['cols'][0];
                $con = array(
                    'enabled' => TRUE,
                    'order' => $col['order'],
                    'collation' => $col['collation'],
                    'on_conflict' => $item['on_conflict']);
                $info['cols'][$col['name']]['cons'][$type] = $con;
                break;
            case 'f':
                $col = $item['cols'][0];
                $con = $item;
                unset($con['cols']);
                $info['cols'][$col['name']]['cons'][$type][] = $con;
                break;
            }
        }else{
            $info['cons'][$type][] = $item;
        }
        break;
    case 'delete':
        if($i===''){
            unset($cons[$type]);
        }else{
            unset($cons[$type][$i]);
        }
        break;
    case 'update':
        if($rel && isset($item['cols']) && count($item['cols']) > 1){
            if($i===''){
                unset($cons[$type] );
            }else{
                unset($cons[$type][$i]);
            }
            $info['cons'][$type][] = $item;
        }else{
            if($i===''){
                $cons[$type] = $item;
            }else{
                $cons[$type][$i] = $item;
            }
        }
        break;
    }
    
    $names = array_keys($info['cols']);
    table_migrate($name, $info, array_combine($names, $names));
}

function table_rename($old_name, $new_name){
    $database = Database::instance();
    $old_name = $database->quote($old_name);
    $new_name = $database->quote($new_name);
    
    if($old_name!=$new_name && $new_name!=''){
        $cmd = "ALTER TABLE $old_name RENAME TO $new_name";
        $database->execute($cmd);
    }
}

function table_append($name, $columns){
    $database = Database::instance();
    $name = $database->quote($name);
    foreach($columns as $col){
        if($col['name']){
            $cname = $database->quote($col['name']);
            $type = $col['type'];
            $null = $col['null'] ? '' : 'NOT NULL';
            $col_list[] = "$name $type $null";
            $cmd = "ALTER TABLE $name ADD COLUMN $cname $type";
            $database->execute($cmd);
        }
    }
}

function table_drop($names){
    $database = Database::instance();
    foreach((array)$names as $name){
        if(!$name) continue;
        $database->execute('DROP TABLE IF EXISTS '.$database->quote($name));
    }
}

function table_empty($names){
    $database = Database::instance();
    foreach((array)$names as $name){
        if(!$name) continue;
        $name = $database->quote($name);
        $database->execute('DELETE FROM '.$name);
        $database->execute('REINDEX '.$name);
    }
}

function table_reindex($names){
    $database = Database::instance();
    foreach((array)$names as $name){
        if(!$name) continue;
        $name = $database->quote($name);
        $database->execute('REINDEX '.$name);
    }
}

function table_move($name, $option){
    table_copy($name, $option);
    table_drop($name);
}

function table_copy($name, $option){
    $dbname = $option['database'];
    $new_name = $option['table'];
    
    $database1 = Database::instance();
    try {
        $database2 = Database::instance($option['database']);
        $database2->begin('EXCLUSIVE');
        
        $parser = new TableParser($database1->select_value('sqlite_master','sql','name=:name', array('name'=>$name)));
        $parser->data['name'] = $new_name;
        
        $cmd = $parser->build($parser->data);
        $database2->execute($cmd);
        
        $items = $database1->select_list($database1->quote($name), '*', NULL, NULL, NULL, NULL, NULL, TRUE);
        foreach($items as $item){
            $database2->insert($database2->quote($new_name), $items->item, FALSE, $items->meta);
        }
        
        foreach(index_list($name) as $item){
            $parser = new IndexParser($item['_sql_']);
            $parser->data['table'] = $new_name;
            $parser->data['name'] = "index_{$new_name}_{$item['name']}";
            while($database2->exists('sqlite_master', 'name=:name', array('name'=>$parser->data['name']))){
                $parser->data['name'] = "index_{$new_name}_{$item['name']}_".sprintf('%04x', mt_rand(0, 0xffff));
            }
            
            $cmd = $parser->build($parser->data);
            $database2->execute($cmd);
        }
        
        foreach(trigger_list($name) as $item){
            $parser = new TriggerParser($item['_sql_']);
            $parser->data['name'] = "trigger_{$new_name}_{$item['name']}";
            
            while($database2->exists('sqlite_master', 'name=:name', array('name'=>$parser->data['name']))){
                $parser->data['name'] = "index_{$new_name}_{$item['name']}_".sprintf('%04x', mt_rand(0, 0xffff));
            }
            
            $parser->data['table'] = $new_name;
            $cmd = $parser->build($parser->data);
            $database2->execute($cmd);
        }
        
        $database2->commit();
    }catch(DataException $e){
        if($e->getCode()==10001){
            $database2->rollback();
        }
        throw $e;
    }
}
?>

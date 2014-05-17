<?php
require_once P_PATH.'/app/lib/table.php';

function content_list($table, $columns, $condition, 
        $orderby, $params, $pagesize, $pageindex){
    
    $database = Database::instance();
    $items = $database->select_list($database->quote($table), $columns, $condition, 
        $orderby, $params, $pagesize, $pageindex);
    $metas = $database->metas;
    
    $data = array();
    for($i=0, $i_max = count($items); $i<$i_max; $i++){
        $item = $items[$i];
        $meta = $metas[$i];
       
        $fields = array();
        $rowid  = NULL;
        foreach($item as $key=>$text){
            $lens = strlen($text);
            $type = $meta[$key];
            $size = 1;
            if($type==Database::PARAM_BLOB){
                $text = content_size($lens);
            }else if($type==Database::PARAM_TEXT){
                $text = content_substr($text, CONTENT_TEXT_SIZE);
                $size  = ceil(strlen($text) / 18);
                $size  = $size > 3  ? 3 : $size;
            }else if($type==Database::PARAM_NULL){
                $text = '(NULL)';
            }
            
            if($key=='rowid'){
                $rowid = $text;
            }else{
                $fields[] = array('name'=>$key, 'type'=> $type,'text'=>$text, 'size'=>$size);
            }
        }
        $data[] = array('rowid'=>$rowid ? $rowid:NULL, 'fields'=>$fields);
    }
    return $data;
}

function content_query($statement){
    $database = Database::instance();
    $items = $database->execute($statement);
    $metas = $database->metas;
    
    $data = array();
    for($i=0, $i_max = count($items); $i<$i_max; $i++){
        $item = $items[$i];
        $meta = $metas[$i];
        
        $fields = array();
        foreach($item as $key=>$text){
            $lens = strlen($text);
            $type = $meta[$key];
            $size = 1;
            if($type==Database::PARAM_BLOB){
                $text = content_size($lens);
            }else if($type==Database::PARAM_TEXT){
                $text = content_substr($text, CONTENT_TEXT_SIZE);
                $size  = ceil(strlen($text) / 18);
                $size  = $size > 3  ? 3 : $size;
            }else if($type==Database::PARAM_NULL){
                $text = '(NULL)';
            }
            
            $fields[] = array('name'=>$key,'type'=>$type, 'text'=>$text, 'size'=>$size);
        }
        $data[] = array('fields'=>$fields);
    }
    return $data;
}

function content_substr($text, $size=256){
    $database = Database::instance();
    $data = $database->execute("SELECT SUBSTR(:text, 0, $size) AS text, LENGTH(:text) AS size", array('text'=>$text));
    return $data[0]['text'].($data[0]['size'] > $size ? '...': '');
}

function content_size($bytes, $decimals = 2){
    $units = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    if(floor($bytes / 1024) > 0) {
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $units[$factor];
    }else{
        return $bytes.'B';
    }
}

function content_affected(){
    return Database::instance()->rows;
}

function content_columns($items){
    if(empty($items)){
        return array();
    }else{
        $columns = array();
        foreach($items[0]['fields'] as $item){
            $columns[] = array('name'=>$item['name'], 'type'=>content_typename($item['type']));
        }
        return $columns;
    }
}

function content_count($table, $condition, $params){
    $database = Database::instance();
    return $database->select_value($database->quote($table), 'COUNT(*)', $condition, $params);
}

function content_item($table, $rowid){
    $database = Database::instance();
    $columns = table_columns($table);
    $item = $database->select_item($database->quote($table),'*', 'rowid=:rowid', array('rowid'=>$rowid));
    $meta = $database->metas[0];
    
    $cols = array();
    foreach($item as $key=>$val){
        $cols[] = array(
            'name' => $key,
            'value' => $val,
            'type' => $meta[$key]==Database::PARAM_NULL ? table_column_type($columns[$key]['type']) : $meta[$key]
        );
    }
    return $cols;
}

function content_value($table, $rowid, $column){
    $database = Database::instance();
    return $database->select_value($database->quote($table),
        $column, 'rowid=:rowid', array('rowid'=>$rowid));
}

function content_types(){
    return array(
        array('name'=>'INTEGER', 'value'=> Database::PARAM_INTEGER ),
        array('name'=>'FLOAT', 'value'=> Database::PARAM_FLOAT ),
        array('name'=>'TEXT', 'value'=> Database::PARAM_TEXT ),
        array('name'=>'BLOB', 'value'=> Database::PARAM_BLOB ),
        array('name'=>'NULL', 'value'=> Database::PARAM_NULL ),
        
        array('name'=>'AUTO', 'value'=> 6 ),
        array('name'=>'EXPR', 'value'=> 7 )
    );
}

function content_typename($type){
    if($type===Database::PARAM_INTEGER){
        return 'INTEGER';
    }elseif($type===Database::PARAM_FLOAT){
        return 'FLOAT';
    }elseif($type===Database::PARAM_TEXT){
        return 'TEXT';
    }elseif($type===Database::PARAM_BLOB){
        return 'BLOB';
    }elseif($type===Database::PARAM_NULL){
        return 'NULL';
    }else{
        return NULL;
    }
}

function content_default($table){
    $database = Database::instance();
    $columns = table_columns($table);
    $data = array(); $item = array();
    foreach($columns as $col){
        if(isset($col['pk']['autoincr']))continue;
        $item = $col;
        $item['name'] = $col['name'];
        $item['value'] = $col['dflt']!==NULL ? $col['dflt'] : '';
        $item['type'] = table_column_type($col['type']);
        
        $consts = array('NULL', 'CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP');
        if($col['pkey'] && strtoupper($col['type'])=='INTEGER'){
            $item['type'] = 6;
            $item['value'] = 'AUTO INTEGER';
        }elseif(in_array(strtoupper($item['value']), $consts)){
            $item['type'] = 6;
            $item['value'] = strtoupper($item['value']);
        }elseif($item['type']==Database::PARAM_BLOB && !empty($item['value'])){
            $item['value'] = bin2hex($item['value']);
        }
        $data[] = $item;
    }
    return $data;
}

function content_save($table, $rowid, $info, $extra){
    $database = Database::instance();
    $item = array();
    $columns = table_columns($table);
    $meta = array();
    foreach($columns as $col){
        $name = $col['name'];
        $value = isset($info[$name]['value']) ? $info[$name]['value'] : NULL;
        switch($info[$name]['type']){
        case 'INTEGER':
            $meta[$name] = Database::PARAM_INTEGER;
            $item[$name] = (int)$value;
            break;
        case 'FLOAT':
            $meta[$name] = Database::PARAM_FLOAT;
            $item[$name] = (float)$value;
            break;
        case 'TEXT':
            $meta[$name] = Database::PARAM_TEXT;
            $item[$name] = $value;
            break;
        case 'BLOB':
            switch($info[$name]['mode']){
            case 'bin':
                if(!empty($extra['name'][$name])){
                    if($extra['error'][$name]==0){
                        $meta[$name] = Database::PARAM_BLOB;
                        $item[$name] = file_get_contents($extra['tmp_name'][$name]);
                    }else{
                        throw new Exception('Upload Error: '.$extra['error'][$name]);
                    }
                }
                break;
            case 'hex':
                $meta[$name] = Database::PARAM_BLOB;
                $item[$name] = pack('H*', $info[$name]['value']);
                break;
            case 'txt':
                $meta[$name] = Database::PARAM_BLOB;
                $item[$name] = $info[$name]['value'];
                break;
            default:
                $meta[$name] = Database::PARAM_NULL;
                $item[$name] = NULL;
                break;
            }
            break;
        case 'NULL':
            $meta[$name] = Database::PARAM_NULL;
            $item[$name] = NULL;
            break;
        case 'AUTO':
            # ignore;
            break;
        case 'EXPR':
            $meta[$name] = 0;
            $item[$name] = $value;
            break;
        }
    }
    
    if($rowid===NULL){
        $database->insert($database->quote($table), $item, NULL, $meta);
    }else{
        $meta['rowid'] = Database::PARAM_INTEGER;
        $database->update($database->quote($table), $item, 'rowid=:rowid',array('rowid'=>$rowid), $meta);
    }
}

function content_save_items($table, $rowids, $items, $metas){
    $database = Database::instance();
    $table_meta = table_meta($table);
    
    foreach($rowids as $rowid){
        $item = $items[$rowid];
        $meta = $metas[$rowid];
        
        foreach($meta as $key=>$val){
            if($val==Database::PARAM_NULL && $item[$key]) $meta[$key] = $table_meta[$key];
        }
        
        $database->update($database->quote($table), $item, 'rowid=:rowid', array('rowid'=>$rowid), $meta);
    }
}

function content_delete($table, $rowids){
    $database = Database::instance();
    foreach((array)$rowids as $rowid){
        $database->delete($database->quote($table), 'rowid=:rowid', array('rowid'=>$rowid));
    }
}

function content_populate($name, $option){
    $database = Database::instance();
    
    $meta = array();
    $database->begin();
    
    $columns = table_columns($name);
    foreach($option['cols'] as $col){
        if($col['type']!='ignore'){
            $meta[$col['name']] = table_column_type($columns[$col['name']]['type']);
        }
    }
    
    $cmd = $database->insert($database->quote($name), $meta, FALSE, $meta, TRUE);
    $sth = $database->prepare($cmd);
    
    $lorem = new Lorem();
    $blobs = array();
    
    for($i=0; $i < $option['count']; $i++){
        $item = array();
        foreach($option['cols'] as $opt){
            if(isset($opt['mode']) && $opt['mode']=='static'){
                $item[$opt['name']] = $opt['value'];
            }else{
                switch($opt['type']){
                case 'integer':
                    $item[$opt['name']] = mt_rand($opt['min'], $opt['max']);
                    break;
                case 'float':
                    $item[$opt['name']] = mt_rand($opt['min'], $opt['max']) + mt_rand(0, 100) / 10;
                    break;
                case 'title':
                    $item[$opt['name']] = rtrim(ucwords($lorem->words(mt_rand($opt['min'], $opt['max']))));
                    break;
                case 'name':
                    $item[$opt['name']] = ucwords($lorem->words(mt_rand($opt['min'], $opt['max'])));
                    break;
                case 'paragraph':
                    $item[$opt['name']] = $lorem->paragraph($opt['min'], $opt['max']);
                    break;
                case 'article':
                    $item[$opt['name']] = $lorem->article($opt['min'], $opt['max']);
                    break;
                case 'sentence':
                    $item[$opt['name']] = $lorem->sentence($opt['min'], $opt['max']);
                    break;
                case 'datetime':
                    $item[$opt['name']] = strpos($opt['format'], '%') ? 
                        strftime($opt['format'], mt_rand(0, time())) : 
                        gmdate($opt['format'], mt_rand(0, time()));
                    break;
                case 'url':
                    $item[$opt['name']] =  $lorem->format($opt['format']);
                    break;
                case 'phone':
                    $item[$opt['name']] =  $lorem->format($opt['format']);
                    break;
                case 'email':
                    $item[$opt['name']] =  $lorem->format($opt['format']);
                    break;
                case 'chars':
                    $item[$opt['name']] =  $lorem->format($opt['format']);
                    break;
                case 'uuid':
                    $item[$opt['name']] =  $lorem->format($opt['format']);
                    break;
                case 'image':
                    $name = "image-{$opt['width']}x{$opt['height']}.jpg";
                    if(!isset($blobs[$name])) $blobs[$name] = $lorem->image($opt['width'], $opt['height']);
                    $item[$opt['name']] = $blobs[$name];
                    break;
                case 'refer':
                    if(trim($opt['table']) && trim($opt['column'])){
                        $item[$opt['name']] = $database->select_value($opt['table'], $opt['column'], '1=1 ORDER BY RANDOM()');
                    }
                    break;
                case 'null':
                    $item[$opt['name']] = NULL;
                    break;
                case 'ignore':
                    break;
                default:
                    throw new ErrorException("The type({$opt['type']}) is unknown");
                }
            }
        }
        try {
            $database->execute($sth, array_values($item), Database::FETCH_ASSOC, array_values($meta)); // tricky
        }catch(DataException $e){
            continue;
        }
    }
    $database->commit();
}
?>

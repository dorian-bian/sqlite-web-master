<?php
function export_file($fpath, $encode, $schema_names, $content_names, $type, $opts=array(), $compress=FALSE){
    $database = Database::instance();
    $comp = array(
        'txt' => 'file://',
        'zip' => 'compress.zlib://',
        'bz2' => 'compress.bzip2://'
    );
    
    if(!$compress || !isset($comp[$compress])) $compress = 'txt';
    $fpath = "{$comp[$compress]}$fpath";
    
    if(!in_array($type, array('sql', 'xml', 'csv'))) $type = 'sql';
    $func = "export_$type";
    $func($fpath, $encode, $schema_names, $content_names, $opts);
}

function export_sql($fpath, $encode, $schema_names, $content_names, $opts){
    $database = Database::instance();
    $fp = @fopen($fpath, 'w');
    if($fp){
        if($encode!='UTF-8'){
            stream_filter_prepend($fp, "convert.iconv.UTF-8/$encode//TRANSLIT");
        }
        foreach($schema_names as $name){
            $items = $database->select_list('sqlite_master', 
                'name, type, sql, tbl_name as source', 
                'tbl_name=:name AND (type="table" OR type="view")', NULL, array('name'=>$name));
            
            $items = array_merge($items, $database->select_list('sqlite_master', 
                'name, type, sql, tbl_name as source', 
                'type="index" AND tbl_name=:name AND name NOT LIKE "sqlite_%"', NULL, array('name'=>$name)));
            
            foreach($items as $item){
                if(isset($opts['sql']['add_drop'])){
                    $type = strtoupper($item['type']);
                    $name = $database->quote($item['name'], Database::PARAM_TEXT, TRUE);
                    $text = "DROP $type IF EXISTS $name;\n";
                    fwrite($fp, $text);
                } 
                fwrite($fp, $item['sql'].";\n");
            }
        }
        foreach($content_names as $name){
            if(is_string($name)){
                $items = $database->select_list($database->quote($name), '*', NULL, NULL, NULL, NULL, NULL, TRUE);
            }else if(is_array($name) && isset($name['code'])){
                $items = $database->execute($name['code'], NULL, Database::FETCH_ASSOC, NULL, TRUE);
            }else{
                $items = array();
            }
            foreach($items as $item){
                $meta = $items->meta;
                $keys = array();
                $vals = array();
                foreach($item as $key=>$val){
                    $keys[] = $database->quote($key, Database::PARAM_TEXT, TRUE);
                    $vals[] = $database->quote($val, $meta[$key]);
                }
                $keys = implode(',', $keys);
                $vals = implode(',', $vals);
                $text = "INSERT INTO $name($keys)VALUES($vals);\n";
                fwrite($fp, $text);
            }
        }
        
        foreach($schema_names as $name){
           $items = $database->select_list('sqlite_master', 
                'name, type, sql, tbl_name as source', 
                'type="trigger" AND tbl_name=:name', NULL, array('name'=>$name));
            foreach($items as $item){
                if(isset($opts['sql']['add_drop'])){
                    $type = strtoupper($item['type']);
                    $name = $database->quote($item['name'], Database::PARAM_TEXT, TRUE);
                    $text = "DROP $type IF EXISTS $name;\n";
                    fwrite($fp, $text);
                } 
                fwrite($fp, $item['sql'].";\n");
            }
        }
        fclose($fp);
    }
}

function export_xml($fpath, $encode, $schema_names, $content_names, $opts){
    $database = Database::instance();
    
    $xml = new XMLWriter();
    $xml->openURI($fpath);
    $xml->setIndent(TRUE);
    $xml->startDocument('1.0', $encode);
    $xml->startElement('export');
    $xml->startElement('database');
    foreach($content_names as $name){
        if(is_string($name)){
            $items = $database->select_list($database->quote($name), '*', NULL, NULL, NULL, NULL, NULL, TRUE);
        }else if(is_array($name) && isset($name['code'])){
            $items = $database->execute($name['code'], NULL, Database::PARAM_ASSOC, NULL, TRUE);
        }else{
            $items = array();
        }
        foreach($items as $item){
            $meta = $items->meta;
            $xml->startElement('table');
            $xml->writeAttribute('name', $name);
            foreach($item as $key=>$val){
                $xml->startElement('column');
                $xml->writeAttribute('name', $key);
                $xml->writeAttribute('type', $meta[$key]);
                if($meta[$key]==Database::PARAM_BLOB) $val = 'x\''.bin2hex($val).'\'';
                $xml->text($val);
                $xml->endElement();
            }
            $xml->endElement();
        } 
    }
    $xml->endElement();
    $xml->endElement();
}

function export_csv($fpath, $encode, $schema_names, $content_names, $opts){
    $database = Database::instance();
    
    $enclosure = isset($opts['csv']['enclosure']) ? $opts['csv']['enclosure'] : '"';
    $separator = isset($opts['csv']['separator']) ? $opts['csv']['separator'] : ',';
    $escape = isset($opts['csv']['escape']) ? $opts['csv']['escape'] : "\\";
    $quote_all = isset($opts['csv']['quote_all']);
    $inc_header = isset($opts['csv']['inc_header']);
    
    $fp = @fopen($fpath, 'w');
    if($fp){
        if($encode!='UTF-8'){
            stream_filter_prepend($fp, "convert.iconv.UTF-8/$encode//TRANSLIT");
        }
        
        foreach($content_names as $name){
            if(is_string($name)){
                $items = $database->select_list($database->quote($name), '*', NULL, NULL, NULL, NULL, NULL, TRUE);
            }else if(is_array($name) && isset($name['code'])){
                $items = $database->execute($name['code'], NULL, Database::PARAM_ASSOC, NULL, TRUE);
            }else{
                $items = array();
            }
            if($inc_header){
                $vals = array();
                
                foreach((array)$items->item as $key=>$val){
                    $val = str_replace($enclosure, $escape.$enclosure, $key);
                    $vals[] = "$enclosure$val$enclosure";
                }
                fwrite($fp, implode($separator, $vals)."\n");
            }
            foreach($items as $item){
                $meta = $items->meta;
                $vals = array();
                foreach($item as $key=>$val){
                    switch($meta[$key]){
                    case Database::PARAM_INTEGER:
                    case Database::PARAM_FLOAT:
                        $vals[] = ($quote_all ? "$enclosure$val$enclosure" : $val);
                        break;
                    case Database::PARAM_BLOB:
                        $vals[] = 'x\''.bin2hex($val).'\'';
                        break;
                    case Database::PARAM_TEXT:
                        $val = str_replace($enclosure, $escape.$enclosure, $val);
                        $vals[] = "$enclosure$val$enclosure";
                        break;
                    case Database::PARAM_NULL:
                        $vals[] = ($quote_all ? "$enclosure$enclosure" : '');
                        break;
                    default:
                        $vals[] = $val;
                        break;
                    }
                }
                
                fwrite($fp, implode($separator, $vals)."\n");
            }
        }
        fclose($fp);
    }
}
?>

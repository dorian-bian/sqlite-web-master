<?php
require_once P_PATH.'/app/lib/table.php';
require_once P_PATH.'/app/lib/view.php';
require_once P_PATH.'/app/lib/index.php';

require_once P_PATH.'/app/lib/ado.php';

function import_file($name, $path, $type, $encode, $source, $opts){
    set_time_limit(0);
    
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $comp = array(
        'txt' => 'file://',
        'zip' => 'compress.zlib://',
        'bz2' => 'compress.bzip2://'
    );
    
    $compress = strtolower($ext);
    if(!isset($comp[$compress]) && strpos($compress, 'bz')!==FALSE) $compress = 'bz2';
    if(!isset($comp[$compress])) $compress = 'txt';
    $path = "{$comp[$compress]}{$path}";
    if(in_array($type, array('sql', 'csv', 'xml', 'mdb', 'xls'))){
        $func = "import_$type";
        $database = Database::instance();
        if(isset($opts['exe']['transaction'])){
            try {
                $database->begin('EXCLUSIVE');
                $func($name, $path, $source, $opts, $encode, $database);
                $database->commit();
            }catch(Exception $e){
                if($e->getCode()==10001){
                    $database->rollback();
                }
                throw $e;
            }
        }else{
            $func($file['name'], $path, $source, $opts, $encode, $database);
        }
    }
}

function import_sql($name, $path, $source, $opts, $encode, $database){
    $fp  = @fopen($path, 'r');
    if($fp){
        if($encode!='UTF-8'){
            stream_filter_prepend($fp, "convert.iconv.$encode/UTF-8//TRANSLIT");
        }
        $sql = '';
        while(!feof($fp)){
            $line  = fgets($fp);
            $text = trim($line);
            if($text!='' && strlen($text) > 1 && substr($text, 0, 2)!='--'){
                $sql .= ' '.$line;
                if(preg_match('/;(\s*$|\s*--)/',$line)){
                    $database->execute($sql);
                    $sql = '';
                }
            }else{
                $line = '';
            }
        }
        fclose($fp);
    }
}

function import_xml($name, $path, $source, $opts, $encode, $database){
    if(is_readable($path)){
        $xml = new XMLReader();
        $xml->open($path, $encode);
        
        $last = NULL;
        $name = NULL;
        $meta = NULL;
        $item = array();
        $fold = TRUE;
        
        while($xml->read()){
            switch($xml->name){
            case 'table':
                $fold = !$fold;
                if($fold){
                    $name = $xml->getAttribute('name');
                    if($name!=$last){
                        if(isset($opts['exe']['empty'])){
                            $database->delete($database->quote($name));
                        }
                        $meta = table_meta($name);
                    }
                    
                    $database->insert($database->quote($name), $item, FALSE, $meta);
                    
                    $last = $name;
                    $name = NULL;
                    $item = array();
                }
                break;
            case 'column':
                $attr = $xml->getAttribute('name');
                $text = $xml->readInnerXML();
                if(preg_match('/^[xX]\'([0-9a-fA-F]+)\'/', $text, $m)){
                    $text = pack('H*',$m[1]);
                }
                $item[$attr] = $text;
                $xml->next();
                break;
            }
        }
        $xml->close();
    }
}

function import_csv($name, $path, $source, $opts, $encode, $database){
    if(is_readable($path)){
        $fp  = @fopen($path, 'r');
        if($fp){
            if($encode!='UTF-8'){
                stream_filter_prepend($fp, "convert.iconv.$encode/UTF-8//TRANSLIT");
            }
            $meta = table_meta($source);
            
            $enclosure = isset($opts['csv']['enclosure']) ? $opts['csv']['enclosure'] : '\'';
            $separator = isset($opts['csv']['separator']) ? $opts['csv']['separator'] : ',';
            $escape = isset($opts['csv']['escape']) ? $opts['csv']['escape'] : '\\';
            
            if($opts['csv']['create']){
                $meta = NULL;
                
                $source = pathinfo($name, PATHINFO_FILENAME);
                
                $cols = array();
                $cols_quote = array();
                
                if(isset($opts['csv']['inc_header'])){
                    $info = import_csv_item($fp, $separator, $enclosure, $escape);
                    
                    foreach($info[0] as $i=>$val){
                        $cols[] = $val;
                        $cols_quote[] = $database->quote($val);
                    }
                }else{
                    
                    $info = import_csv_item($fp, $separator, $enclosure, $escape);
                    $ii =count($info[0]);
                    foreach($info[0] as $i=>$val){
                        $val = import_num2alpha($i);
                        $cols[] = $val;
                        $cols_quote[] = $database->quote($val);
                    }
                    fseek($fp, 0);
                }
                if(isset($opts['exe']['empty'])) table_drop($source);
                
                
                $cmd = 'CREATE TABLE IF NOT EXISTS '.$database->quote($source).' ('.implode(',', $cols_quote).')';
                $database->execute($cmd);
            }else{
                if($source && isset($opts['exe']['empty'])) $database->delete($database->quote($source));
                if(isset($opts['csv']['inc_header'])){
                    $info = import_csv_item($fp, $separator, $enclosure, $escape);
                    $cols = $info[0];
                }else{
                    $cols = array_keys($meta);
                }
            }
            
            $cols_number = count($cols);
            while(!feof($fp)){
                $info = import_csv_item($fp, $separator, $enclosure, $escape);
                if($info[0] && count($info[0])==$cols_number){
                    $database->insert($database->quote($source), array_combine($cols, $info[0]), FALSE, $meta); 
                }elseif(!feof($fp)){
                    throw new Exception("CSV Parsing Error. Line: {$info[1]}");
                }
            }
            
            fclose($fp);
        }
    }
}

function import_csv_item($fp, $separator = ',', $enclosure = '"', $escape = "\\"){
    $item = array();
    $text = '';
    $state = 1;
    
    $line_number = 0;
    while($state>0 && !feof($fp)){
        $line = fgets($fp);
        $line_number += 1;
        for($i=0,$ii=strlen($line); $i<$ii; $i++){
            $c = $line[$i];
            switch($state){
            case 0: #SPACE
            case 1: #START
                if($c==$enclosure){
                    $state = 3;
                }elseif($c==$separator || $c=="\n" || $c=="\r"){
                    $state = 0;
                }elseif(preg_match('/^[xX]\'([0-9a-fA-F]+)\'/m', substr($line,$i), $m)){
                    $state = 0;
                    $text = pack('H*',$m[1]);
                    $item[] = $text;
                    $text = '';
                    $i += strlen($m[0]) - 1;
                }else{
                    $state = 2;
                    $text = $c;
                }
                $t_len = 0;
                break;
            case 2: #FIELD
                if($c==$separator || $c=="\n" || $c=="\r"){
                    $state = 0;
                    $item[] = $text;
                    $text = '';
                    
                }else{
                    $text .= $c;
                    $t_len += 1;
                }
                break;
            case 3: #QUOTE
                if($c==$escape && $line[$i+1]==$enclosure){
                    $i += 1;
                    $text .= $line[$i];
                    $t_len += 1;
                }elseif($c!=$escape && $c!=$enclosure){
                    $text .= $c; 
                    $t_len += 1;
                }else{
                    $state = 0;
                    $item[] = $text;
                    $text = '';
                }
                break;
            }
        }
    }
    return $state==0 ? array($item, $line_number) : array(NULL, $line_number);
}

function import_mdb($name, $path, $source, $opts, $encode, $database){
    $user = $opts['mdb']['user'];
    $pass = $opts['mdb']['pass'];
    try{
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $name = P_TEMP.'/'.basename(tempnam(P_TEMP, 'im-'),'.tmp').'.'.$ext;
        
        $data = file_get_contents($path);
        file_put_contents($name, $data);
        $path = $name;
        
        $ado = new ADO('Microsoft.ACE.OLEDB.12.0', $path, $encode, 
            "User ID=$user;Password=$pass;");
        
        foreach($ado->table_list() as $item){
            $t_name = $item['name'];
            $data = $ado->table_item($t_name, 'puf');
            table_drop($t_name);
            table_create($data);
            
        }
        foreach($ado->table_list() as $item){
            $ado->table_data($item['name'], 'import_item_cb', $database);
            foreach($ado->index_list($item['name']) as $item){
                index_drop($item['name']);
                index_create($item);
            }
        }
        if(file_exists($path)) unlink($path);
    }catch(COM_Exception $e){
        if(file_exists($path)) unlink($path);
        
        $message = iconv($encode, 'UTF-8//IGNORE', $e->getMessage());
        $line = $e->getLine();
        $message = strip_tags($message)."(ado-line:$line)";
        throw new Exception($message, $message);
    }catch(Exception $e){
        if(file_exists($path)) unlink($path);
        throw $e;
    }
}

function import_xls($name, $path, $source, $opts, $encode, $database){
    try{
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $name = P_TEMP.'/'.basename(tempnam(P_TEMP, 'im-'),'.tmp').'.'.$ext;
        
        $data = file_get_contents($path);
        file_put_contents($name, $data);
        $path = $name;
        
        $ver = $opts['xls']['ver'];
        $hdr = isset($opts['xls']['hdr']) ? 'Yes' : 'No';
        $imex = isset($opts['xls']['imex']) ? '1': '0';
        
        $ado = new ADO('Microsoft.ACE.OLEDB.12.0;', $path, $encode, 
            "Extended Properties=\"$ver;HDR=$hdr;IMEX=$imex\"");
        
        foreach($ado->table_list() as $item){
            $t_name = $item['name'];
            $data = $ado->table_item($t_name, '');
            table_drop($t_name);
            table_create($data);
        }
        
        
        foreach($ado->table_list() as $item){
            $ado->table_data($item['name'], 'import_item_cb', $database);
            foreach($ado->index_list($item['name']) as $item){
                index_drop($item['name']);
                index_create($item);
            }
        }
        
        if(file_exists($path)) unlink($path);
    }catch(COM_Exception $e){
        if(file_exists($path)) unlink($path);
        
        $message = iconv($encode, 'UTF-8//IGNORE', $e->getMessage());
        $line = $e->getLine();
        $message = strip_tags($message)."(ado-line:$line)";
        throw new Exception($message, $message);
    }catch(Exception $e){
        if(file_exists($path)) unlink($path);
        throw $e;
    }
}

function import_item_cb($database, $table_name, $item){
    $database->insert($database->quote($table_name), $item);
}

function import_num2alpha($n){
    $t = '';
    for ($i = 1; $n >= 0 && $i < 10; $i++) {
        $t = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $t;
        $n -= pow(26, $i);
    }
    return $t;
}
?>

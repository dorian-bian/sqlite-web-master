<?php
function database_vacuum($name){
    Database::instance($name)->execute('VACUUM');
}

function database_list(){
    global $_DATABASES;
    
    $databases = array();
    foreach($_DATABASES as $name=>$item){
        $databases[] = array('name'=>$name, 'path'=>$item['path']);
    }
    
    return $databases;
}

function database_path($name){
    return Database::$cfgs[$name]['path'];
}

function database_create($group, $base){
    $base = basename($base);
    
    global $_DB_GROUPS;
    
    if(isset($_DB_GROUPS[$group])){
        $path = $_DB_GROUPS[$group]['path'];
        if(file_exists($path)){
            $file = $path.$base.$_DB_GROUPS[$group]['tail'];
            if(file_exists($file)){
                throw new ErrorException('The name has already existed.');
            }else{
                file_put_contents($file, '');
            }
        }else{
            throw new ErrorException('The directory doesn\'t existed.');
        }
    }else{
        throw new ErrorException('The group doesn\'t existed.');
    }
}

function database_rename($group, $old_base, $new_base){
    $old_base = basename($old_base);
    $new_base = basename($new_base);
    
    global $_DB_GROUPS;
    if(isset($_DB_GROUPS[$group])){
        $path = $_DB_GROUPS[$group]['path'];
        if(file_exists($path)){
            $old_file = $path.$old_base.$_DB_GROUPS[$group]['tail'];
            $new_file = $path.$new_base.$_DB_GROUPS[$group]['tail'];
            if(file_exists($old_file)){
                if(file_exists($new_file)){
                    throw new ErrorException('The target file has existed.');
                }else{
                    rename($old_file, $new_file);
                }
            }else{
                throw new ErrorException('The file doesn\'t existed.');
            }
        }else{
            throw new ErrorException('The directory doesn\'t existed.');
        }
    }else{
        throw new ErrorException('The group doesn\'t existed.');
    }
}

function database_remove($name){
    if(isset(Database::$cfgs[$name])){
        $path = Database::$cfgs[$name]['path'];
        if(file_exists($path)) unlink($path);
    }
}

function database_groups(){
    global $_DB_GROUPS;
    $groups = array();
    foreach($_DB_GROUPS as $group => $item){
        $items = array();
        foreach(glob($item['path'].'*'.$item['tail']) as $path){
            $name = $group.'/'.pathinfo($path, PATHINFO_FILENAME);
            $items[$name] = array(
                'name' => $name,
                'base' => basename($name),
                'path' => $path,
                'tail' => $item['tail'],
                'pass' => isset($item['pass']) ? $item['pass'] : NULL, 
                'exts' => isset($item['exts']) ? $item['exts'] : NULL,
                'size' => database_filesize(filesize($path)),
                'free' => database_filesize(disk_free_space(dirname($path)))
            );
        }
        
        $groups[$group] = array(
            'name' => $group,
            'subs' => $items,
            'tail' => $item['tail']
        );
    }
    return $groups;
}

function database_cfgs(){
    global $_DATABASES, $_DB_GROUPS;
    
    $cfgs = $_DATABASES;
    foreach($_DB_GROUPS as $group => $item){
        foreach(glob($item['path'].'*'.$item['tail']) as $path){
            $name = $group.'/'.pathinfo($path, PATHINFO_FILENAME);
            $cfgs[$name] = array(
                'name' => $name,
                'path' => $path,
                'pass' => isset($item['pass']) ? $item['pass'] : NULL, 
                'exts' => isset($item['exts']) ? $item['exts'] : NULL,
                'size'=> database_filesize(filesize($path)),
                'free'=> database_filesize(disk_free_space(dirname($path)))
            );
        }
    }
    return $cfgs;
}

function database_filesize($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $sz[(int)$factor];
}
?>

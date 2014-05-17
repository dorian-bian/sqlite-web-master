<?php
class Database {
    const PARAM_INTEGER = 1;
    const PARAM_FLOAT = 2;
    const PARAM_TEXT = 3;
    const PARAM_BLOB = 4;
    const PARAM_NULL = 5;
    const PARAM_EXPR = 6;
    const PARAM_AUTO = 7;
    
    const FETCH_ASSOC = 1;
    const FETCH_NUM = 2;
    const FETCH_BOTH = 3;
    
    static $cfgs = array();
    
    public $rows = 0;
    public $cols = 0;
    
    public $items = array();
    public $metas = array();
    
    public $mode = SQLITE3_ASSOC;
    
    private $dbh = NULL;
    
    public function __construct($item){ 
        $mode = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE;
        $pass = isset($item['pass']) ? $item['pass'] : NULL;
        $this->dbh = new SQLite3($item['path'], $mode, $pass);
        $this->dbh->busyTimeout(1000);
        if(isset($item['exts'])){
            foreach((array)$item['exts'] as $ext){
                $this->dbh->loadExtension($ext);
            }
        }
    }
    
    static function instance($name=NULL, $set_default=FALSE){
        static $database = NULL;
        if($name){
            $item = self::$cfgs[$name];
            if($set_default){
                $database = new Database($item);
            }else{
                return new Database($item);
            }
        }elseif($database===NULL){
            $name = key(self::$cfgs);
            $item = self::$cfgs[$name];
            $database = new Database($item);
        }
        return $database;
    }
    
    public function begin($mode='DEFERRED'){
        $cmd = "BEGIN $mode TRANSACTION";
        $success = FALSE;
        try{
            $success = $this->dbh->exec($cmd);
        }catch(Exception $e) {}
        
        if(!$success){
            throw new DataException($this->dbh->lastErrorMsg(), 10000);
        }
    }
    
    public function commit(){
        $cmd = 'COMMIT TRANSACTION';
        $success = FALSE;
        try{
            $success = $this->dbh->exec($cmd);
        }catch(Exception $e) {}
        
        if(!$success){
            throw new DataException($this->dbh->lastErrorMsg(), 10001);
        }
    }
    
    public function rollback($savepoint=NULL){
        $cmd = 'ROLLBACK TRANSACTION ';
        $success = FALSE;
        if($savepoint!=NULL) $savepoint .= "TO $savepoint";
        try{
            $success = $this->dbh->exec($cmd);
        }catch(Exception $e) {}
        if(!$success){
            throw new DataException($this->dbh->lastErrorMsg(), 10000);
        }
    }
    
    public function select_list($table, $columns, $condition=NULL, $orderby=NULL,
            $kargs=NULL, $pagesize=NULL, $pageindex=NULL, $use_iterator=FALSE, $return_cmd=FALSE){
        
        $cmd = "SELECT $columns FROM $table";
        if($condition) $cmd .= " WHERE $condition";
        if($orderby) $cmd .= " ORDER BY $orderby";
        if($pagesize) { $cmd .= " LIMIT  ".$pagesize; }
        if($pageindex) { $cmd .= " OFFSET ".$pagesize*($pageindex-1); }
        
        return $return_cmd ? $cmd : $this->execute($cmd, $kargs, SQLITE3_ASSOC, NULL, $use_iterator);
    }
    
    public function select_item($table,$columns, $condition=NULL, $kargs=NULL, $return_cmd=FALSE){
        $cmd = "SELECT $columns FROM $table";
        if($condition) $cmd .= " WHERE $condition";
        $cmd .= " LIMIT 1";
        
        if($return_cmd){
            return $cmd;
        }else{
            $items = $this->execute($cmd, $kargs);
            return count($items)==1 ? $items[0] : array();
        }
    }
    
    public function select_value($table, $columns, $condition=NULL, $kargs=NULL, $return_cmd=FALSE){
        $cmd = "SELECT $columns FROM $table";
        if($condition) $cmd .= " WHERE $condition";
        $cmd .= " LIMIT 1";
        if($return_cmd){
            return $cmd;
        }else{
            $items = $this->execute($cmd, $kargs, SQLITE3_NUM);
            return count($items)==1 ? $items[0][0]: FALSE;
        }
    }
    
    public function insert($table, $item, $force=FALSE, $meta=NULL, $return_cmd=FALSE){
        $cmd = "%s INTO $table(%s)VALUES(%s)";
        
        $verb = $force ? 'REPLACE' : 'INSERT';
        $cols = array();
        $vals = array(); 
        $coln = 0;
        foreach($item as $key=>$val){
            $cols[] = $this->quote($key);
            $vals[] = $meta && $meta[$key]===0 ? $val : ":$coln";
            $coln += 1;
        }
        
        $cmd = sprintf($cmd, $verb, implode(',', $cols), implode(',', $vals));
        if($return_cmd){
            return $cmd;
        }else{
            $items = $this->execute($cmd, array_values($item), SQLITE3_NUM, $meta ? array_values($meta) : NULL);
            return $this->dbh->lastInsertRowID();
        }
    }
    
    public function update($table, $values, $condition=NULL, $kargs=NULL, $meta=NULL, $return_cmd=FALSE){
        $cmd="UPDATE $table SET %s";
        if(!$kargs) $kargs = array();
        if(!$meta) $meta = array();
        $cols = array();
        $coln = 0;
        if(is_array($values)){
            $pads = empty($kargs) ? '' :  str_repeat('_',max(array_map('strlen', $kargs)));
            foreach($values as $key=>$val){
                $_name = $pads.$coln;
                if($meta && $meta[$key]===0){
                     $cols[$_name]= $this->quote($key).'='.$val;
                }else{
                    $cols[$_name]= $this->quote($key).'=:'.$_name;
                }
                $kargs[$_name] = $val;
                if(isset($meta[$key])) $meta[$_name] = $meta[$key]; 
                $coln += 1;
            }
            $cols = implode(',', $cols);
        }else{                
            $cols=$values;
        }
        
        $cmd = sprintf($cmd, $cols);
        if($condition) $cmd .= " WHERE $condition";
        
        if($return_cmd){
            return $cmd;
        }else{
            $this->execute($cmd, $kargs, SQLITE3_NUM, $meta);
            return $this->rows;
        }
    }
    
    public function delete($table, $condition=NULL, $kargs=NULL, $return_cmd=FALSE){
        $cmd = "DELETE FROM $table";
        if($condition) $cmd .= " WHERE $condition";
        if($return_cmd){
            return $cmd;
        }else{
            $this->execute($cmd, $kargs, SQLITE3_NUM);
            return $this->rows;
        }
    }
    
    public function exists($table, $condition=NULL, $kargs=NULL, $return_cmd=FALSE){
        $cmd = "SELECT 1 FROM $table";
        if($condition) $cmd .= " WHERE $condition";
        if($return_cmd){
            return $cmd;
        }else{
            $items = $this->execute($cmd, $kargs, SQLITE3_NUM);
            return count($items)==1 ? (bool)$items[0][0]: FALSE;
        }
    }
    
    public function execute($cmd, $kargs=NULL, $mode=SQLITE3_ASSOC, $meta=NULL, $use_iterator=FALSE){
        if(!$kargs) $kargs = array();
        
        $sth =is_string($cmd) ?  $this->prepare($cmd) : $cmd;
        
        if($sth==FALSE){
            $info = is_string($cmd) ? "sql-p2: $cmd" : 'SSQLite3Stmt';
            throw new DataException($this->dbh->lastErrorMsg(), $info , 10000);
        }
        
        foreach($kargs as $key=>$value){
            if(isset($meta[$key])){
                $type = $meta[$key];
                if($type==SQLITE3_NULL){
                    if(trim($value)==''){
                        $type = SQLITE3_NULL;
                    }else if($value==(int)$value){
                        $type = SQLITE3_INTEGER;
                    }else if($value==(float)$value){
                        $type = SQLITE3_FLOAT;
                    }else{
                        $type = SQLITE3_TEXT;
                    }
                }
                $sth->bindValue(":$key", $value, $type);
            }else{
                $sth->bindValue(":$key", $value);
            }
        }
        $result = NULL;
        try{
            $result = $sth->execute();
        }catch(Exception $e){
            $info = is_string($cmd) ? "sql-p2: $cmd" : 'SSQLite3Stmt';
            throw new DataException($this->dbh->lastErrorMsg(), $info , 10000);
        } 
        if($result){
            if($use_iterator){
                return new DataIterator($result);
            }else{
                $metas = array();
                $items = array();
                
                $this->rows = $this->dbh->changes();
                $this->cols = $result->numColumns();
                
                if($this->cols){
                    while($item = $result->fetchArray($mode)){
                        for($i=0; $i<$this->cols; $i++){
                            $meta[$result->columnName($i)]=$result->columnType($i);
                        }
                        $metas[] = $meta;
                        $items[] = $item;
                    }
                }
                $this->metas = $metas;
                $this->items = $items;
                return $items;
            }
        }
        return array();
    }
    
    public function prepare($cmd){
        try {
            return $this->dbh->prepare($cmd);
        }catch(Exception $e){
            throw new DataException($this->dbh->lastErrorMsg(), "sql-p1: $cmd", 10001);
        }
    }
    
    public function quote($value, $type=SQLITE3_TEXT, $with_mark=TRUE){
        switch($type){
        case SQLITE3_INTEGER:
        case SQLITE3_FLOAT:
            return $value;
        case SQLITE3_TEXT:
            $value = $this->dbh->escapeString($value);
            return $with_mark ? "'$value'" : $value;
        case SQLITE3_BLOB:
            $value = bin2hex($value);
            return "x'$value'";
        case SQLITE3_NULL:
            return 'NULL';
        default:
            return $value;
        }
    }
    
    public static function version(){
        $version = SQLite3::version();
        return $version['versionString'];
    }
}

class DataException extends Exception {
    public $data = '';
    
    public function __construct($info, $data, $code=10000, $prev=NULL){
        parent::__construct($info, $code, $prev);
        $this->data = $data;
    }
}

class DataIterator implements Iterator {
    private $offset = 0;
    private $result = NULL;
    
    public  $meta = NULL;
    public  $item = NULL;
    
    public function __construct($result) {
        $this->result = $result;
        
        $this->offset = 0;
        $this->fetch();
    }
    function rewind() {
        $this->result->reset();
        
        $this->offset = 0;
        $this->fetch();
    }
    function current() {
        return $this->item;
    }
    function key() {
        return $this->offset;
    }
    function next() {
        $this->offset += 1;
        $this->fetch();
    }
    function valid() {
        return !empty($this->item);
    }
    
    function fetch(){
        $result = $this->result;
        $this->item = $result->fetchArray(SQLITE3_ASSOC);
        $meta = array();
        for($i=0, $ii = count($this->item); $i<$ii; $i++){
            $meta[$result->columnName($i)]=$result->columnType($i);
        }
        $this->meta = $meta;
    }
}
?>

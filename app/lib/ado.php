<?php

require_once P_PATH.'/app/lib/table.php';

class ADO {
    const SCHEMA_TABLES = 20;
    const SCHEMA_TABLE_PRIMARYKEYS = 28;
    const SCHEMA_TABLE_FOREIGNKEYS = 27;
    const SCHEMA_TABLE_CHECKS = 5;
    const SCHEMA_TABLE_CONSTRAINTS = 10; 
    const SCHEMA_TABLE_COLUMNS = 4;
    const SCHEMA_TABLE_KEYS = 8;
    
    const SCHEMA_VIEWS = 23;
    const SCHEMA_INDEXES = 12;
    
    static $DATA_TYPES = array(
          2 => 'INTEGER',   // SHORT(DBTYPE_I2)
          3 => 'INTEGER',   // LONG(DBTYPE_I4)
          4 => 'INTEGER',   // SINGLE(DBTYPE_R4)
          5 => 'REAL',      // DOUBLE(DBTYPE_R8)
          6 => 'DECIMAL',   // CURRENCY(DBTYPE_CY)
          7 => 'TEXT',      // DATETIME(DBTYPE_DATE)
          
         11 => 'INTEGER',   // BIT(DBTYPE_BOOL)
         17 => 'INTEGER',   // BYTE(DBTYPE_UI1)
         72 => 'TEXT',      // GUID(DBTYPE_GUID)
         
        128 => 'BLOB',      // BINARY(DBTYPE_BYTES)
        129 => 'TEXT',      // TEXT(DBTYPE_STR)
        130 => 'TEXT',      // TEXT(DBTYPE_WSTR)
        131 => 'DECIMAL',   // DECIMAL(DBTYPE_NUMERIC)
        132 => 'BLOB',      // UDT(DBTYPE_UDT)
        133 => 'TEXT',      // DBDATE(DBDATE)
        134 => 'TEXT',      // DBTIME(DBDATE)
        135 => 'TEXT',      // DBTIMESTAMP(DBDATE)

    );
    
    static $SQL3_TYPES = array(
        Database::PARAM_INTEGER => 3,
        Database::PARAM_FLOAT => 5,
        Database::PARAM_TEXT => 130,
        Database::PARAM_BLOB => 128
    );
    
    public $link = NULL;
    public $cata = NULL;
    
    public function __construct($provider, $source, $encode, $extra, $is_create=FALSE){
        if($is_create){
            $this->cata = new COM('ADOX.Catalog');
            $this->cata->create("Provider=$provider;Data Source=$source;$extra");
        }else{
            $this->link = new COM('ADODB.Connection');
            $this->link->open("Provider=$provider;Data Source=$source;$extra");
        }
        $this->encode = $encode;
    }
    
    public function table_data($name, $table_data_cb, $database){
        $sq = $this->encode("[$name]");
        $rs = $this->link->execute($sq, $c, 2);
        
        $ii = $rs->fields->count(); 
        while(!$rs->EOF){
            $dr = array();
            for($i=0; $i<$ii; $i++){
                $dc = $rs->fields[$i];
                switch($dc->type){
                default:
                    $nm = $this->decode($dc->name);
                    $va = is_string($dc->value) ? $this->decode($dc->value) : $dc->value;
                    $dr[$nm] = $va;
                    break;
                }
            }
            $table_data_cb($database, $name, $dr);
            $rs->moveNext();
        }
        $rs->close();
    }
    
    public function table_list(){
        $ds = array();
        $rs = $this->link->openSchema(self::SCHEMA_TABLES);
        
        while(!$rs->EOF){
            $dr = array(
                'name'=>$this->decode($rs->fields['TABLE_NAME']->value), 
                'type'=>$rs->fields['TABLE_TYPE']->value
            );
            if($dr['type']=='TABLE') $ds[$dr['name']] = $dr;
            $rs->moveNext();
        }
        return $ds;
    }
    
    public function table_item($name, $extend=''){
        $ds = array();
        $rs = $this->link->openSchema(self::SCHEMA_TABLE_COLUMNS);
        
        while(!$rs->EOF){
            $cons = array(
                'c' => array(),
                'f' => array(),
                'n' => array('enabled'=>!$rs->fields['IS_NULLABLE']->value, 'on_conflict'=>'ABORT'),
                'p' => array('enabled'=>FALSE, 'on_conflict'=>'ABORT', 'order' => 'ASC', 'autoincr' => FALSE),
                'u' => array('enabled'=>FALSE, 'on_conflict'=>'ABORT')
            );
            
            $default=NULL;
            if(strstr($extend, 'd')){
                $default = $this->decode($rs->fields['COLUMN_DEFAULT']->value);
                if($default){
                    if(strtolower($default)=='=no'){
                        $default = '0';
                    }elseif(strtolower($default)=='=yes'){
                        $default = '1';
                    }
                }
            }
            $dr = array(
                'i' => $rs->fields['ORDINAL_POSITION']->value, 
                'name' => $this->decode($rs->fields['COLUMN_NAME']->value), 
                'type' => self::$DATA_TYPES[(int)$rs->fields['DATA_TYPE']], 
                'default' => $default,
                'collation' =>'BINARY',
                'cons' => $cons
            );
            $tn = $this->decode($rs->fields['TABLE_NAME']->value);
            if($tn==$name) $ds[$dr['name']] = $dr;
            $rs->moveNext();
        }
        
        usort($ds, array($this,'sort_items_cb'));
        return array(
            'name' => $name,
            'type' => 'NORMAL',
            'cols' => $ds,
            'cons' => array(
                'p' => strstr($extend, 'p') ? $this->table_primarykeys($name) : array(),
                'u' => strstr($extend, 'u') ? $this->table_uniques($name) : array(),
                'f' => strstr($extend, 'f') ? $this->table_foreignkeys($name) : array(),
                'c' => strstr($extend, 'c') ? $this->table_checks($name) : array()
            )
        );
    }
    
    public function table_primarykeys($table_name){
        $rs = $this->link->openSchema(self::SCHEMA_TABLE_PRIMARYKEYS);
        
        $ds = array();
        while(!$rs->EOF){
            $dr = array(
                'i' => $rs->fields['ORDINAL']->value, 
                'name' => $this->decode($rs->fields['COLUMN_NAME']->value), 
                'collation'=>'BINARY', 
                'order'=>'ASC'
            );
            $tn = $this->decode($rs->fields['TABLE_NAME']->value);
            if($tn==$table_name){
                $ds[$dr['i']-1] = $dr;
            }
            $rs->moveNext();
        }
        ksort($ds);
        if(!empty($ds)){
            return array(
                array(
                    'autoincr' => FALSE,
                    'on_conflict' => 'ABORT',
                    'cols'=> $ds
                )
            );
        }else{
            return array();
        }
    }
    
    public function table_uniques($table_name){
        $rs = $this->link->openSchema(self::SCHEMA_TABLE_CONSTRAINTS);
        
        $ds = array();
        while(!$rs->EOF){
            $tn = $this->decode($rs->fields['TABLE_NAME']->value);
            $tp = $this->decode($rs->fields['CONSTRAINT_TYPE']->value);
            if($tn==$table_name && $tp=='UNIQUE'){
                $nm = $this->decode($rs->fields['CONSTRAINT_NAME']->value);
                $ds[$nm] = array(
                    'autoincr' => FALSE,
                    'on_conflict' => 'ABORT',
                    'cols'=> $this->table_unique_keys($table_name, $nm)
                );
            }
            $rs->moveNext();
        }
        return $ds;
    }
    
    public function table_unique_keys($table_name, $cons_name){
        $rs = $this->link->openSchema(self::SCHEMA_TABLE_KEYS);
        
        $ds = array();
        while(!$rs->EOF){
            $tn = $this->decode($rs->fields['TABLE_NAME']->value);
            $cn = $this->decode($rs->fields['CONSTRAINT_NAME']->value);
            if($tn==$table_name && $cn==$cons_name){
                $dr = array(
                    'i' => $rs->fields['ORDINAL_POSITION']->value, 
                    'name' => $this->decode($rs->fields['COLUMN_NAME']->value), 
                    'collation' => 'BINARY', 
                    'order' => 'ASC'
                );
                $nm = $this->decode($rs->fields['CONSTRAINT_NAME']->value);
                $ds[$dr['i']] = $dr;  
            }
            $rs->moveNext();
        }
        ksort($ds);
        return $ds;
    }
    
    public function table_foreignkeys($table_name){
        $rs = $this->link->openSchema(self::SCHEMA_TABLE_FOREIGNKEYS);
        
        $ds = array();
        while(!$rs->EOF){
            $dr = array(
                'name' => $this->decode($rs->fields['FK_NAME']->value),
                'cols' => array(
                    array(
                        'i'=> $rs->fields['ORDINAL']->value,
                        'name'=> $this->decode($rs->fields['FK_COLUMN_NAME']->value)
                    )
                ),
                
                'refer' => array(
                    'name' => $this->decode($rs->fields['PK_TABLE_NAME']->value),
                    'cols' => array(
                        array('name'=> $this->decode($rs->fields['PK_COLUMN_NAME']->value)),
                    )
                ),
                
                'match' => NULL,
                'on_update' => $this->decode($rs->fields['UPDATE_RULE']->value),
                'on_delete' => $this->decode($rs->fields['DELETE_RULE']->value),
                
                'deferred' => $rs->fields['DEFERRABILITY']->value==1
            );
            $tn = $this->decode($rs->fields['FK_TABLE_NAME']->value);
            if($tn==$table_name){
                if(isset($ds[$dr['name']])){
                    $ds[$dr['name']]['cols'][] = $dr['cols'][0];
                    usort($ds[$dr['name']]['cols'], array($this, 'sort_items_cb'));                    
                }else{
                    $ds[$dr['name']] = $dr;
                }
            }
            $rs->moveNext();
        }
        return $ds;
    }
    
    public function table_checks($table_name){
        $rs = $this->link->openSchema(self::SCHEMA_TABLE_CHECKS);
        
        $ds = array();
        while(!$rs->EOF){
            $it = explode('.', $this->decode($rs->fields['CONSTRAINT_NAME']->value));
            $tn = trim($it[0], '[]');
            $va = trim($it[1], '[]');
            $cc = $this->decode($rs->fields['CHECK_CLAUSE']->value);
            if(stripos($cc, $va)===FALSE) $cc = "[$va] $cc";
            if(stripos($cc, '#')!==FALSE) $cc = preg_replace('/#([0-9:\/\s]+)#/', '"$1"',$cc); 
            
            if($tn==$table_name && $va && $cc){
                $ds[] = array('i'=>count($ds), 'enabled'=>TRUE, 'expr'=>$cc);
            }
            $rs->moveNext();
        }
        return $ds;
    }
    
    public function decode($text){
        return iconv($this->encode, 'UTF-8//TRANSLIT', $text);
    }
    
    public function encode($text){
        return iconv('UTF-8', $this->encode.'//TRANSLIT', $text);
    }
    
    public function sort_items_cb($a, $b){
        return $a['i'] - $b['i'];
    }
    
    public function view_list(){
        $ds = array();
        $rs = $this->link->openSchema(self::SCHEMA_VIEWS);
        
        $ii = $rs->fields->count();
        while(!$rs->EOF){
            $vw = $this->decode($rs->fields['VIEW_DEFINITION']->value);
            if(stripos($vw, '#')!==FALSE) $vw = preg_replace('/#([0-9:\/\s]+)#/', '"$1"',$vw); 
            $dr = array(
                'name'=>$this->decode($rs->fields['TABLE_NAME']->value),
                'type'=>'VIEW', 
                'statement'=> $vw
            );
            
            $ds[$dr['name']] = $dr;
            $rs->moveNext();
        }
        return $ds;
    }
    
    public function index_list($table_name){
        $ds = array();
        $rs = $this->link->openSchema(self::SCHEMA_INDEXES);
        
        $ii = $rs->fields->count();
        while(!$rs->EOF){
            $dr = array(
                'name'=>$this->decode($rs->fields['INDEX_NAME']->value), 
                'type'=>'INDEX', 
                'table'=>$this->decode($rs->fields['TABLE_NAME']->value), 
                'unique'=>(bool)$rs->fields['UNIQUE']->value, 
                'cols'=>array(
                    array(
                        'i' => $rs->fields['ORDINAL_POSITION']->value,
                        'name'=>$this->decode($rs->fields['COLUMN_NAME']->value),
                        'order'=>'ASC', 
                        'collation'=>'BINARY'
                    )
                )
            );
            $pk = (bool)$rs->fields['PRIMARY_KEY']->value;
            if($dr['table']==$table_name && !$pk){
                if(isset($ds[$dr['name']])){
                    $ds[$dr['name']]['cols'][$dr['cols'][0]['i']-1] = $dr['cols'][0];
                    ksort($ds[$dr['name']]['cols']);
                }else{
                    $ds[$dr['name']] = $dr;
                }
            }
            
            $rs->moveNext();
        }
        return $ds;
    }
    
    public function create_table($data){
        $table = new COM('ADOX.Table');
        $table->Name = $data['name'];
        foreach($data['cols'] as $item){
            $column = new COM('ADOX.Column');
            $column->name = $item['name'];
            $type = table_column_type($item['type']);
            $column->type = self::$SQL3_TYPES[$type];
            $table->columns->append($column);
        }
        
        $this->cata->tables->append($table);
    }
    
    public function create_view($data){
        $view = new COM('ADOX.View');
        $command = new COM('ADO.Command');
        $command->commandText = $data['statement'];
        $view->append($data['name'], $command);
        
        $this->cata->views->append($view);
    }
}
?>

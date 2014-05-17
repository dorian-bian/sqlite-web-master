<?php
require_once P_PATH.'/app/lib/sql/sqlparser.php';

class TableParser extends SqlParser {
    public function start(&$data){
        $data += array(
            'name' => NULL,
            'type' => 'NORMAL',
            'cols' => array(),
            'cons' => array(
                'p' => array(),
                'u' => array(),
                'f' => array(),
                'c' => array(),
            )
        );
        if($this->shift('CREATE')){
            $this->shift('TEMP');
            $this->shift('VIRTUAL');
            if($this->shift('TABLE')){
                $this->shift('IF NOT EXISTS');
                $this->shift('(nm) DOT');
                if($this->shift('nm', $m)){
                    $data['name'] = $this->strip($m[0]);
                    if($this->shift('USING (nm)', $m)){
                        $data['type'] = strtoupper($this->strip($m[1]));
                    }
                    return $this->shift('LP') && $this->create_args($data) && $this->shift('RP');
                } 
            }
        }
        return FALSE;
    }
    
    public function create_args(&$data){
        $i = 0;
        if($this->column_def($item)){
            $item['i'] = $i;
            $data['cols'][$item['name']] = $item;
            $pass = TRUE;
            while($pass && $this->match('COMMA nm')){
                $i += 1;
                $pass = $this->shift('COMMA') && $this->column_def($item);
                $item['i'] = $i;
                $data['cols'][$item['name']] = $item;
            }
            
            if($pass){
                if($this->shift('COMMA')){
                    while($pass && $this->match('CONSTRAINT|PRIMARY KEY|UNIQUE|CHECK|FOREIGN KEY')){
                        $pass = $this->table_constraint($data) && ($this->shift('COMMA') || TRUE);
                    }
                    return $pass;
                }elseif($this->match('RP')){
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    
    public function column_def(&$item){
        $item = array(
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
        
        if($this->shift('nm', $m)){
            $item['name'] = $this->strip($m[0]);
            
            $pass = TRUE;
            if($this->match('ids')){
                $pass = $this->shift('ids+ (LP signed (COMMA signed)? RP)?', $m);
                $item['type'] = $this->strip($m[0]);
            }
            $rule = 'CONSTRAINT|PRIMARY KEY|NULL|NOT NULL|UNIQUE|CHECK|DEFAULT|COLLATE|REFERENCES|NOT DEFERRABLE|DEFERRABLE';
            while($pass && $this->match($rule)){
                $pass = $this->column_constraint($item);
            }
            return $pass;
        }
        return FALSE;
    }
    
    public function column_constraint(&$item){
        $this->shift('CONSTRAINT nm');
        if($this->shift('PRIMARY KEY')){
            $p = array(
                'enabled' => TRUE,
                'order' => 'ASC', 
                'autoincr' => FALSE, 
                'on_conflict' => 'ABORT'
            );
            if($this->shift('ASC|DESC', $m)){
                if(strtoupper($m[0])=='DESC') $p['order']='DESC';
            }
            if($this->shift('ON CONFLICT (ROLLBACK|ABORT|FAIL|IGNORE|REPLACE)', $m)){
                $p['on_conflict'] = strtoupper($m[1]);
            }
            $p['autoincr'] = $this->shift('AUTOINCR');
            $item['cons']['p'] = $p;
            return TRUE;
        }elseif($this->shift('NOT NULL')){
            $n = array(
                'enabled' => TRUE,
                'on_conflict' => 'ABORT'
            );
            if($this->shift('ON CONFLICT (ROLLBACK|ABORT|FAIL|IGNORE|REPLACE)', $m)){
                $n['on_conflict'] = strtoupper($m[1]);
            }
            $item['cons']['n'] = $n;
            return TRUE;
        }elseif($this->shift('NULL')){
            $this->shift('ON CONFLICT (ROLLBACK|ABORT|FAIL|IGNORE|REPLACE)');
            return TRUE;
        }elseif($this->shift('UNIQUE')){
            $u = array(
                'enabled' => TRUE, 
                'on_conflict'=>'ABORT'
            );
            if($this->shift('ON CONFLICT (ROLLBACK|ABORT|FAIL|IGNORE|REPLACE)', $m)){
                $u['on_conflict'] = strtoupper($m[1]);
            }
            $item['cons']['u'] = $u;
            return TRUE;
        }elseif($this->shift('CHECK')){
            if($this->match('LP')){
                $item['cons']['c'][] = array(
                    'i' => count($item['cons']['c']),
                    'expr' => $this->inner('LP', 'RP')
                );
                return TRUE;
            }
        }elseif($this->shift('DEFAULT')){
            $rule = '(MINUS|PLUS)? term|id';
            if($this->shift($rule, $m)){
                $item['default'] = $m[0];
                return TRUE;
            }elseif($this->match('LP')){
                $item['default'] = $this->inner('LP', 'RP'); 
                return TRUE;
            }
        }elseif($this->shift('COLLATE')){
            if($this->shift('ids', $m)){
                $item['collation'] = $this->strip($m[0]);
                return TRUE;
            }
        }elseif($this->match('REFERENCES')){
            if($this->foreign_key_clause($f)){
                $f['i'] = count($item['cons']['f']);
                $item['cons']['f'][] = $f;
                return TRUE;
            }
        }elseif($this->shift('NOT DEFERRABLE|DEFERRABLE')){
            $this->shift('INITIALLY DEFERRED|INITIALLY IMMEDIATE');
            return TRUE;
        }
        
        return FALSE;
    }
    
    public function table_constraint(&$data){
        $this->shift('CONSTRAINT nm');
        if($this->shift('PRIMARY KEY|UNIQUE', $m)){
            $type = strtoupper($m[0])=='UNIQUE' ? 'u' : 'p';
            $item = array(
                'cols' => array(),
                'autoincr' => FALSE,
                'on_conflict' => 'ABORT'
            );
            
            if($this->shift('LP') && $this->nlist($m, 1)){
                $item['cols'] = $m;
                if($this->shift('AUTOINCR')){
                    $item['autoincr'] = TRUE;
                }
                
                if($this->shift('RP')){
                    if($this->shift('ON CONFLICT (ROLLBACK|ABORT|FAIL|IGNORE|REPLACE)', $m)){
                        $item['on_conflict'] = strtoupper($m[1]);
                    }
                    
                    if(count($item['cols'])>1){
                        $item['i'] = count($data['cons'][$type]);
                        $data['cons'][$type][] = $item;
                    }else{
                        $ccol = $item['cols'][0];
                        $data['cols'][$ccol['name']]['cons'][$type] = array(
                            'enabled' => TRUE,
                            'order' => $ccol['order'],
                            'collation' => $ccol['collation'], 
                            'autoincr' => $item['autoincr'],
                            'on_conflict'=> $item['on_conflict']
                        );
                    }
                    return TRUE;
                }
            }
        }elseif($this->shift('CHECK')){
            $data['cons']['c'][] = array(
                'i' => count($data['cons']['c']),
                'enabled' => TRUE,
                'expr' => $this->inner('LP', 'RP')
            ); 
            return TRUE;
        }elseif($this->shift('FOREIGN KEY')){
            if($this->shift('LP') && $this->nlist($m) && $this->shift('RP')){
                if($this->foreign_key_clause($f)){
                    if(count($m)>1){
                        $f['i'] = count($data['cons']['f']);
                        $f['cols'] = $m;
                        $data['cons']['f'][] = $f;
                    }else{
                        $name = $m[0]['name'];
                        $f['i'] = count($data['cols'][$name]['cons']['f']);
                        $data['cols'][$name]['cons']['f'][] = $f;
                    }
                    return TRUE;
                }
            }
        }
        
        return FALSE;
    }
    
    public function foreign_key_clause(&$f){
        $f = array(
            'name' => NULL,
            'cols' => array(),
            
            'refer' => array(
                'name' => NULL,
                'cols' => array()),
                
            'match' => NULL,
            'on_update' => 'NO ACTION',
            'on_delete' => 'NO ACTION',
            
            'deferred' => FALSE
        );
        if($this->shift('REFERENCES')){
            if($this->shift('nm', $m)){
                $f['refer']['name'] = $this->strip($m[0]);
                if($this->shift('LP') && $this->nlist($m, 1) && $this->shift('RP')){
                    $f['refer']['cols'] = $m;
                }
                
                $pass = TRUE;
                while($pass && $this->match('ON|MATCH')){
                    if($this->shift('ON (DELETE|UPDATE)', $m)){
                        $event = strtolower($m[1]);
                        if($this->shift('SET NULL|SET DEFAULT|CASCADE|RESTRICT|NO ACTION', $m)){
                            $f['on_'.$event] = preg_replace('/\s+/', ' ', strtoupper($m[0]));
                        }else{
                            $pass = FALSE;
                        }
                    }elseif($this->shift('MATCH (ids)', $m)){
                        $f['match'] = $this->strip($m[1]);
                    }else{
                        $pass = FALSE;
                    }
                };
                if($pass){
                    if($this->shift('DEFERRABLE INITIALLY DEFERRED')){
                        $f['deferred'] = TRUE;
                    }else{
                        $this->shift('NOT? DEFERRABLE (INITIALLY (DEFERRED|IMMEDIATE))?');
                    }
                    
                    if(!$this->match('RP|COMMA|CONSTRAINT|PRIMARY KEY|FOREIGN KEY|NOT NULL|UNIQUE|CHECK|DEFAULT|COLLATE|REFERENCES')){
                        $pass = FALSE;
                    }
                    return $pass;
                }
                
                
            }
        }
        return FALSE;
    }
    
    public function build($data, $type='start'){
        switch($type){
        case 'start':
            $nodes = array();
            $nodes[] = 'CREATE';
            if($data['type']!='NORMAL') $nodes[] = 'VIRTUAL';
            $nodes[] = 'TABLE';
            $nodes[] = $this->quote($data['name']);
            if($data['type']!='NORMAL') {
                $nodes[] = 'USING';
                $nodes[] = $this->quote($data['type']);
            }
            $nodes[] = '(';
            
            $cols = $this->build($data['cols'], 'cols');
            $nodes[] = $cols;
            
            if(isset($data['cons'])){
                $cons = $this->build($data['cons'], 'cons');
                if($cons!='') $nodes[] = ','.$cons;
            }
            $nodes[] = ')';
            return implode(' ', $nodes);
        case 'cols':
            $nodes = array();
            foreach($data as $item){
                $_nodes = array();
                $_nodes[] = $this->quote($item['name']);
                if(trim($item['type'])) $_nodes[] = $this->quote(trim($item['type']));
                if(trim($item['default'])!=='') $_nodes[] = "DEFAULT ({$item['default']})";
                if($item['cons']['n']['enabled']){
                    $_nodes[] = 'NOT NULL';
                    if($item['cons']['n']['on_conflict']!='ABORT'){ 
                        $_nodes[] = 'ON CONFLICT '.$item['cons']['n']['on_conflict'];
                    }
                }
                if($item['cons']['p']['enabled']){
                    $_nodes[] = 'PRIMARY KEY';
                    if($item['cons']['p']['order']!='ASC') $_nodes[] = $item['cons']['p']['order'];
                    if($item['cons']['p']['on_conflict']!='ABORT'){ 
                        $_nodes[] = 'ON CONFLICT '.$item['cons']['p']['on_conflict'];
                    }
                    if($item['cons']['p']['autoincr']) $_nodes[] = 'AUTOINCREMENT';
                }
                if($item['cons']['u']['enabled']){
                    $_nodes[] = 'UNIQUE';
                    if($item['cons']['u']['on_conflict']!='ABORT'){
                        $_nodes[] = 'ON CONFLICT '.$item['cons']['u']['on_conflict'];
                    }
                }
                if($item['collation']!='BINARY'){
                    $_nodes[] = 'COLLATE '.$this->quote($item['collation']);
                }
                if(isset($item['cons']['f'])){
                    foreach($item['cons']['f'] as $_item){
                        $_nodes[] = $this->build($_item, 'cons.f');
                    }
                }
                
                if(isset($item['cons']['c'])){
                    foreach($item['cons']['c'] as $_item){
                        $_nodes[] = 'CHECK ('.$_item['expr'].')';
                    }
                }
                if(count($_nodes)>0) $nodes[] = implode(' ', $_nodes);
            }
            return implode(',', $nodes);
        case 'cons':
            $nodes = array();
            if(isset($data['p'][0])){
                $item = $data['p'][0];
                $_nodes = array();
                $_nodes[] = 'PRIMARY KEY (';
                $_nodes[] = $this->ntext($item['cols'], 1);
                if($item['autoincr']) $_nodes[] = 'AUTOINCREMENT';
                $_nodes[] = ')';
                
                if($item['on_conflict']!='ABORT'){
                    $_nodes[] = 'ON CONFLICT';
                    $_nodes[] = $item['on_conflict'];
                }
                $nodes[] = implode(' ', $_nodes);
            }
            
            if(isset($data['u'])){
                $_nodes = array();
                foreach($data['u'] as $item){
                    $__nodes = array();
                    $__nodes[] = 'UNIQUE';
                    $__nodes[] = '('.$this->ntext($item['cols'], 1).')';
                    if($item['on_conflict']!='ABORT'){
                        $__nodes[] = 'ON CONFLICT';
                        $__nodes[] = $item['on_conflict'];
                    }
                    $_nodes[] = implode(' ', $__nodes);
                }
                if(count($_nodes)>0) $nodes[] = implode(' ', $_nodes);
            }
            if(isset($data['f'])){
                $_nodes = array();
                foreach($data['f'] as $item){
                    $__nodes = array();
                    $__nodes[] = 'FOREIGN KEY';
                    if(!isset($item['cols'])) $item['cols'] = array(); 
                    $__nodes[] = '('.$this->ntext($item['cols']).')';
                    $__nodes[] = $this->build($item, 'cons.f');
                    $_nodes[] = implode(' ', $__nodes);
                }
                if(count($_nodes)>0) $nodes[] = implode(' ', $_nodes);
            }
            if(isset($data['c'])){
                $_nodes = array();
                foreach($data['c'] as $item){
                    $_nodes[] = 'CHECK ('.$item['expr'].')';
                }
                if(count($_nodes)>0) $nodes[] = implode(' ', $_nodes);
            }
            return implode(',', $nodes);
        case 'cons.f':
            $nodes = array();
            $nodes[] = "REFERENCES";
            $nodes[] = $this->quote($data['refer']['name']);
            
            if(isset($data['refer']['cols'])){
                $nodes[] = '('.$this->ntext($data['refer']['cols']).')';
            }
            $nodes[] = "ON DELETE {$data['on_delete']}";
            $nodes[] = "ON UPDATE {$data['on_update']}";
            if($data['match']) $nodes[] = $this->quote($data['match']);
            if($data['deferred']) $nodes[] = 'DEFERRABLE INITIALLY DEFERRED';
            return implode(' ', $nodes);
        }
        
        trigger_error('unknow type:'.$type);
    }
}
?>

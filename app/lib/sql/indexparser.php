<?php
require_once P_PATH.'/app/lib/sql/sqlparser.php';

class IndexParser extends SqlParser {
    
    public function start(&$data){
        $data += array(
            'name' => NULL, 
            'unique' => FALSE, 
            'table' => NULL,
            'cols'=>array()
        );
        if($this->shift('CREATE')){
            $data['unique'] = $this->shift('UNIQUE');
            if($this->shift('INDEX')){
                $this->shift('IF NOT EXISTS');
                $this->shift('(nm) DOT');
                $this->shift('nm', $m);
                $data['name'] = $this->strip($m[0]);
                
                if($this->shift('ON (nm)', $m)){
                    $data['table'] = $this->strip($m[1]);
                    if($this->shift('LP') && $this->nlist($m, 1) && $this->shift('RP')){
                        $data['cols'] = $m;
                        return TRUE;
                    }
                }
            }
        }
        return FALSE;
    }
    
    public function build($data){
        $nodes[] = 'CREATE';
        if($data['unique']) $nodes[] = 'UNIQUE';
        $nodes[] = 'INDEX';
        $nodes[] = $this->quote($data['name']);
        $nodes[] = 'ON';
        $nodes[] = $this->quote($data['table']);
        $nodes[] = '(';
        $nodes[] = $this->ntext($data['cols'], 1);
        $nodes[] = ')';
        return implode(' ', $nodes);
    }
    
}
?>

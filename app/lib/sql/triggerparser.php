<?php
require_once P_PATH.'/app/lib/sql/sqlparser.php';

class TriggerParser extends SqlParser {
    
    public function start(&$data){
        $data += array(
          'name' => NULL,
          'moment' => 'BEFORE',
          'event' => NULL,
          'cols' => array(),
          'each' => FALSE,
          'when' => NULL,
          'action' => NULL,
        );
        if($this->shift('CREATE')){
            $this->shift('TEMP');
            if($this->shift('TRIGGER')){
                $this->shift('IF NOT EXISTS');
                $this->shift('(nm) DOT');
                if($this->shift('nm', $m)){
                    $data['name'] = $this->strip($m[0]);
                    if($this->shift('BEFORE|AFTER|INSTEAD OF', $m)){
                        $data['moment'] = strtoupper(preg_replace('/\s+/', ' ', $m[0]));
                    }
                    if($this->shift('DELETE|INSERT|UPDATE', $m)){
                        $data['event'] = strtoupper($m[0]);
                        $data['cols'] = $this->shift('OF') && $this->nlist($m) ? $m : array();
                        if($this->shift('ON (nm)', $m)){
                            $data['table'] = $this->strip($m[1]);
                            if($this->shift('FOR EACH ROW')) $data['each'] = TRUE;
                            if($this->shift('WHEN')){
                                $data['when'] = $this->inner(NULL, 'BEGIN');
                                $this->anchor -= 1;
                                $pass = TRUE;
                            }
                            if($this->match('BEGIN')){
                                $data['action'] = $this->inner('BEGIN', 'END');
                                return TRUE;
                            }
                        }
                    }
                } 
            }
        }
        return FALSE;
    }
    
    public function build($data){
        $nodes = array();
        $nodes[] = 'CREATE TRIGGER';
        $nodes[] = $this->quote($data['name']);
        $nodes[] = $data['moment'];
        $nodes[] = $data['event'];
        if($data['event']=='UPDATE' && isset($data['cols']) && $data['cols']){
            $nodes[] = 'OF';
            $nodes[] = $this->ntext($data['cols']);
        }
        $nodes[] = 'ON';
        $nodes[] = $this->quote($data['table']);
        if($data['each']) $nodes[] = 'FOR EACH ROW';
        if($data['when']) $nodes[] = 'WHEN '.$data['when'];
        $nodes[] = 'BEGIN';
        $nodes[] = $data['action'];
        $nodes[] = 'END';
        return implode(' ', $nodes);
    }
}
?>

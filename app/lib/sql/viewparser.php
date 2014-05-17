<?php
require_once P_PATH.'/app/lib/sql/sqlparser.php';

class ViewParser extends SqlParser {
    
    public function start(&$data){
        if($this->shift('CREATE')){
            $this->shift('TEMP');
            if($this->shift('VIEW')){
                $this->shift('IF NOT EXISTS');
                $this->shift('(nm) DOT');
                if($this->shift('nm', $m)){
                    $data['name'] = $this->strip($m[0]);
                    if($this->shift('AS')){
                        $start = $this->tokens[$this->anchor][2];
                        $data['statement'] = substr($this->text, $start);
                        return TRUE;
                    }
                }
            }
        }
        return FALSE;
    }
    
    public function build($data){
        $nodes[] = 'CREATE VIEW';
        $nodes[] = $this->quote($data['name']); 
        $nodes[] = "AS {$data['statement']}";
        
        return implode(' ', $nodes);
    }
    
}
?>

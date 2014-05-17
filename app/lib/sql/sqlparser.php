<?php
require_once P_PATH.'/app/lib/sql/sqllexer.php';
require_once P_PATH.'/app/lib/sql/ruleparser.php';

class SqlParser {
    public $tokens = array();
    public $anchor = 0;
   
    public $lexer = NULL;
   
    public $text  = '';
    public $data  = array();
    
    public $base  =  array(
        'id' => 'ID|INDEXED',
        'ids' => 'ID|STRING',
        'nm' => 'ID|STRING|INDEXED|JOIN_KW',
        'number' => 'INTEGER|FLOAT',
        'signed' => '(PLUS|MINUS)? (INTEGER|FLOAT)',
        'term'  => 'INTEGER|FLOAT|BLOB|STRING|CTIME_KW|NULL',
    );
    
    public function __construct($text=NULL){
        if(!$text) return;
        $this->text = $text;
        $this->lexer = new SqlLexer($text);
        $this->tokens = $this->lexer->data;
        
        $this->data['_sql_'] = $text;
        
        if(!$this->start($this->data)){
            $token = $this->tokens[$this->anchor];
            $tname = array_search($token[0], $this->lexer->tokenset);
            $text = substr($this->text,0, $token[2]);
            $text .='^'.substr($this->text,$token[2]);
            throw new Exception("Sql Parser Fail: ($tname: {$token[0]}, {$token[1]}, {$token[2]}) ");
        }
    }
    
    public function strip($text){
        $quote = $text[0];
        if( $text=='' || $text==NULL ) return -1;
        switch( $quote ){
            case '\'':  break;
            case '"':   break;
            case '`':   break;                  /* For MySQL compatibility */
            case '[':   $quote = ']';  break;   /* For MS SqlServer compatibility */
            default:    return $text;
        }
        for($i=1, $j=0, $ii=strlen($text); $i<$ii-1; $i++){
            if( $text[$i]==$quote ){
                if( $text[$i+1]==$quote ){
                    $text[$j++] = $quote;
                    $i++;
                }else{
                    break;
                }
            }else{
                $text[$j++] = $text[$i];
            }
        }
        return substr($text, 0, $j);
    }
    
    public function shift($rule, &$data=array()){
        $offset = 0;
        if($this->match($rule, $data, $offset)){
            $this->anchor += $offset;
            return TRUE;
        }
        return FALSE;
    }
    
    public function match($rule, &$data=array(), &$offset=0){
        if(is_string($rule)){
            $parser = new RuleParser($rule);
            $rule = $parser->data;
        }
        return $this->check($rule, $data, $offset);
    }
    
    public function check($rule, &$data=array(), &$offset=0){
        $cursor = $offset;
        switch($rule[0]){
        case '?':
            if($this->check($rule[1], $data, $cursor)){
                $offset = $cursor;
            }
            return TRUE;
        case '*':
            while($this->check($rule[1], $data, $cursor)){
                $offset = $cursor;
            }
            return TRUE;
        case '+':
            if($this->check($rule[1], $data, $cursor)){
                $offset = $cursor;
                while($this->check($rule[1], $data, $cursor)){
                    $offset = $cursor;
                }
                return TRUE;
            }
            return FALSE;
        case '&':
            if($this->check($rule[1], $data, $cursor) && $this->check($rule[2], $data, $cursor)){
                $offset = $cursor;
                return TRUE;
            }
            return FALSE;
        case '|':
            if($this->check($rule[1], $data, $cursor) || $this->check($rule[2], $data, $cursor)){
                $offset = $cursor;
                return TRUE;
            }
            return FALSE;
        case 'g':
            if($this->check($rule[1], $data, $cursor)){
                $texts = array();
                $space = $this->lexer->tokenset['SPACE'];
                for($i = $offset; $i < $cursor; $i++){
                    $token = $this->tokens[$this->anchor+$i];
                    if($token[0] != $space){
                        $texts[]  = $token[1]; 
                    }
                }
                $data[$rule[2]] = implode(' ', $texts);
                $offset = $cursor;
                return TRUE;
            }
            return FALSE;
        case 'i':
            $key = $rule[1];
            
            $pos = $this->anchor+$offset;
            while($this->tokens[$pos][0]==$this->lexer->tokenset['SPACE']) $pos += 1;
            $cursor = $offset = $pos - $this->anchor;
            
            if(ctype_lower($key[0])){
                if($this->match($this->base[$key], $data, $cursor)){
                    $offset = $cursor;
                    return TRUE;
                }
            }elseif($this->tokens[$pos][0] == $this->lexer->tokenset[$rule[1]] ||
                    $rule[1]=='ID' && ($this->lexer->tokenset['ID'] & $this->tokens[$pos][0])){
                $offset += 1;
                return TRUE;
            }else{
                return FALSE;
            }
        }
        return FALSE;
    }
    
    public function inner($beg, $end){
        if($beg){
            $this->shift($beg);
            $vbeg = $this->lexer->tokenset[$beg];
        }else{
            $vbeg = 0;
        }
        $vend = $this->lexer->tokenset[$end];
        for($i=$this->anchor,$ii=count($this->tokens), $level=1; $i<$ii && $level>0; $i++){
            $t = $this->tokens[$i];
            if($t[0]==$vbeg) $level += 1;
            if($t[0]==$vend) $level -= 1;
        }
        $beg = $this->tokens[$this->anchor][2];
        $end = $this->tokens[$i-1][2];
        $text = substr($this->text,$beg, $end-$beg);
        $this->anchor = $i;
        return trim($text);
    }
    
    public function nlist(&$data, $mode=0){
        $data = array();
        $i = 0;
        if($mode){
            if($this->shift('nm', $m)){
                $data[] = array(
                    'i' => $i,
                    'name' => $this->strip($m[0]),
                    'collation' => $this->shift('COLLATE (ids)', $m) ? strtoupper($this->strip($m[1])) : 'BINARY',
                    'order' => $this->shift('ASC|DESC', $m) ? strtoupper($m[0]) : 'ASC'
                );
                while($this->shift('COMMA')){
                    $i += 1;
                    if($this->shift('nm', $m)){
                        $data[] = array(
                            'i' => $i,
                            'name' => $this->strip($m[0]),
                            'collation' => $this->shift('COLLATE (ids)', $m) ? strtoupper($this->strip($m[1])) : 'BINARY',
                            'order' => $this->shift('ASC|DESC', $m) ? strtoupper($m[0]) : 'ASC'
                        );
                    }else{
                        return FALSE;
                    }
                }
                return TRUE;
            }
        }else{
            if($this->shift('nm', $m)){
                $data[] = array(
                    'i' => $i,
                    'name' => $this->strip($m[0])
                );
                while($this->shift('COMMA')){
                    $i += 1;
                    if($this->shift('nm', $m)){
                        $data[] = array(
                            'i' => $i,
                            'name' => $this->strip($m[0])
                        );
                    }else{
                        return FALSE;
                    }
                }
                return TRUE;
            }
        }
        return FALSE;
    }
    
    public function ntext($data, $mode=0){
        if(is_string($data)) return $data;
        $nodes = array();
        if($mode){
            foreach($data as $item){
                $_nodes = array();
                $_nodes[] = $this->quote($item['name']);
                if($item['collation']!='BINARY'){
                    $_nodes[] = 'COLLATE';
                    $_nodes[] = $this->quote($item['collation']);
                }
                if($item['order']!='ASC'){
                    $_nodes[] = 'DESC';
                }
                $nodes[] = implode(' ', $_nodes);
            }
        }else{
            foreach($data as $item){
                $nodes[] = $this->quote($item['name']);
            }
        }
        return implode(',', $nodes);
    }
    
    public function quote($text){
        return '"'.str_replace('"', '""', $text).'"';
    }
    
    public function start(&$data){ return TRUE; }
}
?>

<?php
class RuleParser {
    public $text = '';
    public $data = array();
    
    public $tokens  = array();
    public $anchor = 0;
    public $groups = 0;
    
    public function __construct($text){
        $this->text = $text;
        $this->tokens = $this->smash($text);
        $this->data = array('g', $this->build('EXPR'), 0);
    }
    
    public function smash($text){
        $offset = 0;
        $tokens = array();
        while(strlen($text)){
            if(preg_match('/^[a-zA-Z][a-zA-Z0-9_]*/', $text, $match)){
                $tokens[] = array('i', $match[0], $offset);
                $n = strlen($match[0]);
            }elseif(preg_match('/^[)(|+?*]/', $text, $match)){
                $tokens[] = array($match[0], $match[0], $offset);
                $n = 1;
            }elseif(preg_match('/^\s+/', $text, $match)){
                $n = strlen($match[0]);
            }else{
                $tokens[] = array('?', $text[0], $offset);
                $n = 1;
            }
            $text = substr($text, $n);
            $offset += $n;
        }
        $tokens[] = array('$', NULL, $offset);
        return $tokens;
    }
    
    public function build($vn, $node=array()){
        $token = $this->tokens[$this->anchor];
        switch($vn){
            case 'EXPR':
                switch($token[0]){
                case 'i': 
                case '(':
                    $node = $this->build('TERM', $node);
                    $node = $this->build('EXPR_REST', $node);
                    return $node;
                }
            case 'EXPR_REST':
                switch($token[0]){
                case '|':
                    $this->shift('|');
                    $node = array('|', $node,  $this->build('TERM', $node));
                    $node = $this->build('EXPR_REST', $node);
                    return $node;
                case '$':
                case ')':
                    return $node;
                }
            case 'TERM':
                switch($token[0]){
                case 'i':
                case '(':
                    $node =$this->build('UNIT', $node);
                    $node = $this->build('TERM_REST', $node);
                    return $node;
                }
            case 'TERM_REST':
                switch($token[0]){
                case 'i':
                case '(':
                    $node = array('&',  $node, $this->build('UNIT', $node));
                    $node = $this->build('TERM_REST', $node);
                    return $node;
                case ')':
                case '|':
                case '$':
                    return $node;
                }
            case 'UNIT':
                switch($token[0]){
                case 'i':
                case'(':
                    $node = $this->build('PART', $node);
                    $node = $this->build('MODE', $node);
                    return $node;
                }
            case 'PART':
                switch($token[0]){
                case 'i':
                    $node = $this->shift('i');
                    return $node;
                case '(':
                    $this->groups += 1;
                    $g = $this->groups;
                    $this->shift('(');
                    $node = $this->build('EXPR', $node);
                    $this->shift(')');
                    return array('g', $node, $g);
                }
            case 'MODE':
                switch($token[0]){
                case '+':
                case '*':
                case '?':
                    $this->shift($token[0]);
                    return array($token[0], $node);
                case '(':
                case ')':
                case '|':
                case 'i':
                case '$':
                    return $node;
                }
        }
        
        $this->error($vn);
    }
    
    public function shift($name){
        $anchor = $this->anchor;
        if($this->tokens[$anchor][0]==$name){
            $token = $this->tokens[$anchor];
            $this->anchor += 1;
            return $token;
        }else{
            $this->error('wrong token name:'. $name); 
        }
    }
    
    public function error($message='error'){
        $token = $this->tokens[$this->anchor];
        echo $message. '<br/>';
        echo sprintf('current token:(%s, %s, %s).<br/>', $token[0], $token[1], $token[2]);
        echo sprintf('<strong>%s</strong>%s<br/>', substr($this->text, 0, $token[2]), substr($this->text, $token[2]));
        exit();
    }
}
?>

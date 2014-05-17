<?php
class Module {
    public $outer = NULL;
    public $chain = NULL;
    
    public function __construct($outer, $chain){
        $this->outer = $outer ? $outer : $this;
        $this->chain = $chain;
    }
    
    public function __toString(){ return ''; }
}
?>

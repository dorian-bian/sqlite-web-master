<?php
class Logging {
    public $total = 0;
    public $upper = 150;
    public $lower = 100;
    
    private $fp   = NULL;
    
    public function __construct($file){
        $this->fp = fopen($file, 'r+');
        flock($this->fp, LOCK_EX);
        $this->total = $this->count();
    }
    
    function write($data){
        if($this->total > $this->upper) $this->rotate();
        
        $fp = $this->fp;
        fseek($fp, 0, SEEK_END);
        
        fwrite($fp, json_encode($data)."\n");
        fflush($fp);
        $this->total += 1;
    }
    
    function items($pagesize, $pageindex){
        var_dump($this->total);
        
        $fp = $this->fp;
        fseek($fp, 0, SEEK_SET);
        
        $offset = $this->total - $pagesize * $pageindex;
        
        $limit = min($pagesize + $offset, $this->total) + 1;
        $offset = $offset > 0 ? $offset : 0;
        $data = array();
        
        if($limit){
            for($i=1; $i<$limit; $i++){
                $text = fgets($fp);
                if($i > $offset){
                    $data[] = json_decode($text);
                }
            }
        }
        return array_reverse($data);
    }

    function clean(){
        $fp = $this->fp;
        fseek($fp, 0, SEEK_SET);
        
        ftruncate($fp, 0);
        fflush($fp);
        $this->total = 0;
    }

    function count(){
        $fp = $this->fp;
        fseek($fp, 0, SEEK_SET);
        
        for($i=0; !feof($fp); $i++) { fgets($fp); };
        return $i-1;
    }

    function rotate(){
        $fp = $this->fp;
        fseek($fp, 0, SEEK_SET);
        
        $offset = $this->total - $this->lower - 1;
        if($offset>0){
            for($i=0; !feof($fp) && $i<$offset; $i++) fgets($fp);
            for($a=0; !feof($fp); $i++){
                $text = fgets($fp);
                
                $b = feof($fp) ? FALSE : ftell($fp); 
                fseek($fp, $a);
                $a += fwrite($fp, $text);
                if($b){
                    fseek($fp, $b);
                }else{
                    break;
                }
            }
            
            if($a!=0){
                ftruncate($fp, $a);
                fflush($fp);
                $this->total = $this->lower;
            }
        }
    }
    
    function close(){
        fclose($this->fp);
    }
}
?>

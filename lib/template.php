<?php
class TplParser {
    static $unit = array();
    static $when = NULL;
    static $site = NULL;
    
    public $path = NULL;
    public $lang = NULL;
    public $data = NULL;
    public $node = NULL;
    
    
    public function __construct($path=NULL){
        if($path){
            $this->path = $path;
            $text = file_get_contents($path);
            $this->node = $this->parse($text);
        }
    }
    
    public function parse($text){
        $r_base = '[a-z0-9_-]+';
        $r_name = '[\/\.a-zA-Z0-9_-]+';
        $r_l_ws = '(?:^\h*)?'; 
        $r_r_ws = '\h*\v?';
        $r_text = '"[^"]+"|\'[^\']+\'|\S*';
        
        $r_node = "/(?:$r_l_ws{([+-])|{)(?:($r_base):)?($r_name)([\V]*?)}(?(1)$r_r_ws)/m";
        $r_args = "/($r_name)\s*=\s*($r_text)/";
        
        $root = array(
            'type' => 2,
            'base' => 'data',
            'name' => '_root_',
            'must' => TRUE,
            'args' => array('mode'=>'item'),
            'subs' => array()
        );
        $p = &$root['subs'];
        $stack = array(&$p);
        
        $anchor = 0;
        $ii = preg_match_all($r_node, $text, $m, PREG_OFFSET_CAPTURE);
        for($i=0; $i<$ii; $i++){
            $p[] = array(
                'type' => 0,
                'text' => substr($text, $anchor, $m[0][$i][1] - $anchor)
            );
            $anchor = $m[0][$i][1] + strlen($m[0][$i][0]);
            if($m[1][$i][0]==''){
                $p[] = array(
                    'type' => 1,
                    'base' => $m[2][$i][0],
                    'name' => $m[3][$i][0],
                    'must' => $m[4][$i][0] != '?',
                    'args' => preg_match_all($r_args, $m[4][$i][0], $n) ? array_combine($n[1], $n[2]) : array(),
                );
                
            }else{
                if($m[1][$i][0]=='+'){
                    $p[] = array(
                        'type' => 2,
                        'base' => $m[2][$i][0],
                        'name' => $m[3][$i][0], 
                        'must' => $m[4][$i][0] != '?',
                        'args' => preg_match_all($r_args, $m[4][$i][0], $n) ? array_combine($n[1], $n[2]) : array(),
                        'subs' => array()
                    );
                    $stack[] = &$p;
                    $p = &$p[count($p)-1]['subs'];
                }else{
                    end($stack);
                    $key = key($stack);
                    $p = &$stack[$key];
                    unset($stack[$key]);
                }
            }
        }
        
        $p[] = array(
            'type' => 0,
            'text' => substr($text, $anchor)
        );
        return $root;
    }
}

class TplProducer extends TplParser {
    public function build($node, $link, $i=0){
        if($node['type']==0){
            return $node['text'];
        }else{
            $base = $node['base'] ? $node['base'] : 'data';
            $name = $node['name'];
            
            $text = '';
            $data = $this->fetch($base, $name, $link);
            
            if($node['type']==1){
                switch($base){
                case 'site':
                case 'lang':
                case 'data':
                    $text = $data;
                    if($text && isset($node['args']['dt-format'])){
                        $dfmt = substr($node['args']['dt-format'], 1, -1);
                        $text = strpos($dfmt, '%') ? strftime($dfmt, $text) : gmdate($dfmt, $text);
                    }
                    if($text && isset($node['args']['format'])){
                        $text = substr(sprintf($node['args']['format'], $text), 1, -1);;
                    }
                    
                    if($text && (!isset($node['args']['encode']) || $node['args']['encode']!=='no')){
                        $text = htmlspecialchars($text);
                    }
                    return $text;
                case 'unit':
                    if(isset(self::$unit[$name])){
                        foreach(self::$unit[$name] as $item){
                            if($item['mode']=='node'){
                                if(isset($node['args']['data'])) $link[] = $this->value($node['args']['data'], $link);
                                foreach($item['data'] as $_node){
                                    $text .= $this->build($_node, $link, 0);
                                }
                                
                            }else{
                                $text .= $item['data'];
                            }
                        }
                    }
                    return $text;
                case 'tick':
                    switch($name){
                    case 'i':
                        return $i;
                    case 'even':
                        return $i % 2 ? 'odd' : 'even';
                    }
                case 'when':
                    $a = isset($node['args']['a']) ? $this->value($node['args']['a'], $link) : TRUE;
                    $b = isset($node['args']['b']) ? $this->value($node['args']['b'], $link) : TRUE;
                    switch($name){
                    case 'eq': $pass = $a == $b; break;
                    case 'ne': $pass = $a != $b; break;
                    case 'gt': $pass = $a >  $b; break;
                    case 'ge': $pass = $a >= $b; break;
                    case 'lt': $pass = $a <  $b; break;
                    case 'le': $pass = $a <= $b; break;
                    case 'ok': $pass = (bool)$a; break;
                    case 'no': $pass = !$a;      break;
                    default  : $pass = FALSE;    break;
                    }
                    
                    if($pass && isset($node['args']['mode'])){
                        $mode = $node['args']['mode'];
                        if($mode[0]=='\'' || $mode[0]=='"'){
                            return substr($mode, 1, -1);
                        }else{
                            return isset(self::$when[$mode]) ? self::$when[$mode] : '';
                        }
                    }
                    break;
                case 'url':
                case 'url-0':
                    $path = $_SERVER['PHP_SELF'];
                    $args = isset(self::$site['args']) ? self::$site['args'] : array();
                    $glue = $node['base']=='url' ? '&amp;' : '&';
                    foreach($node['args'] as $key=>$val){
                        if($val[0]=='\'' || $val[0]=='"'){
                            $args[$key] = substr($val, 1, -1);
                        }else{
                            $args[$key] = $this->value($val, $link);
                        }
                    }
                    switch($name){
                    case 'part':
                        return http_build_query($args, '', $glue);
                    case 'self':
                        $args = $args + $_GET;
                        return $_SERVER['PHP_SELF'].'?'.http_build_query($args, '', $glue);
                    case 'base':
                        return $_SERVER['PHP_SELF'].'?'.http_build_query($args, '', $glue);
                    }
                }
            }else{
                switch($base){
                case 'data':
                    if(!is_array($data)) $data = array();
                    $last = count($link);
                    if(isset($node['args']['mode'])){
                        switch($node['args']['mode']){
                        case 'kval':
                            $rows = array();
                            foreach($data as $key=>$val){
                                $rows[] = array('key'=>$key, 'val'=>$val);
                            }
                            $data = $rows;
                            break;
                        case 'item': $data = array($data); break;
                        case 'list': break;
                        }
                    }
                    
                    $i = 0;
                    foreach($data as $item){
                        $link[$last] = $item;
                        foreach($node['subs'] as $_node){
                            $text .= $this->build($_node, $link, $i);
                        }
                        $i += 1;
                    }
                    break;
                case 'when':
                    $a = isset($node['args']['a']) ? $this->value($node['args']['a'], $link) : TRUE;
                    $b = isset($node['args']['b']) ? $this->value($node['args']['b'], $link) : TRUE;
                    
                    switch($name){
                    case 'eq': $pass = $a == $b; break;
                    case 'ne': $pass = $a != $b; break;
                    case 'gt': $pass = $a >  $b; break;
                    case 'ge': $pass = $a >= $b; break;
                    case 'lt': $pass = $a <  $b; break;
                    case 'le': $pass = $a <= $b; break;
                    case 'ok': $pass = (bool)$a; break;
                    case 'no': $pass = !$a;      break;
                    default  : $pass = FALSE;    break;
                    }
                    if($pass){
                        foreach($node['subs'] as $_node){
                            $text .= $this->build($_node, $link);
                        }
                    }
                    break;
                case 'unit':
                    $mode = isset($node['args']['mode']) ? $node['args']['mode'] : 'text';
                    
                    if(!isset(self::$unit[$name])) self::$unit[$name] = array();
                    if($mode=='node'){
                        self::$unit[$name][] = array(
                            'mode' => 'node',
                            'data' => $node['subs']
                        );
                    }else{
                        foreach($node['subs'] as $_node){
                            self::$unit[$name][] = array(
                                'mode' => 'text',
                                'data' => $this->build($_node, $link, $i)
                            );
                        }
                    }
                }
                return $text;
            }
        }
        return '';
    }
    
    public function render(){
        return $this->build($this->node, array(array('_root_'=>$this->data)));
    }
    
    
    public function value($text, $link){
        if($text[0]=='\''||$text[0]=='"'){
            $text = substr($text, 1, -1);
        }else{
            if(strpos($text, ':')!==FALSE){
                list($base, $name) = explode(':', $text, 2);
            }else{
                $base = 'data';
                $name = $text;
            }
            $text = $this->fetch($base, $name, $link);
        }
        return $text;
    }
    
    public function fetch($base, $name, $link){
        switch($base){
        case 'site':
            $item = self::$site;
            break;
        case 'lang':
            $item = $this->lang;
            break;
        case 'data':
            $item = end($link);
            break;
        default:
            return NULL;
        }
        foreach(explode('/', $name) as $node){
            switch($node){
            case '..':
                $item = prev($link);
                break;
            case '':
                $item = $link[1];
                break;
            case '.':
                $item = current($link);
            default:
                $item = isset($item[$node]) ? $item[$node] : NULL;
                break;
            }
            if(is_null($item)) return NULL;
        }
        return $item;
    }
}

class TplCompiler extends TplParser {
    public $temp = NULL;
    
    public function __construct($path=NULL){
        if($path){
            $base = P_PATH.'/tmp/tpl_'.sha1($path).'_';
            $time = filemtime($path);
            $temp = "$base$time";
            if(TRUE || !file_exists($temp)){
                foreach(glob($base.'*') as $oldn){
                    @unlink($oldn);
                }
                parent::__construct($path);
                $code = $this->compile($this->node);
                
                file_put_contents($temp, $code);
            }
            $this->temp = $temp;
        }
    }
    
    public function render(){
        ob_start();
        $_data = array('_root_' => $this->data);
        $_lang = $this->lang;
        $_site = self::$site;
        
        include $this->temp;
        return ob_get_clean();
    }
    
    public function compile($node, $deep=0){
        if($node['type']==0){
            return $node['text'];
        }else{
            $base = $node['base'] ? $node['base'] : 'data';
            $name = $node['name'];
            $code = '';
            if($node['type']==1){
                switch($base){
                case 'site':
                case 'lang':
                case 'data':
                    $code = $this->expand($deep, $base, $name);
                    $item = $code;
                    if(isset($node['args']['dt-format'])){
                        $format = $node['args']['dt-format'];
                        $code = strpos($format, '%') ? "strftime($format, $code)" : "gmdate($format, $code)";
                    }
                    if(isset($node['args']['format'])){
                        $format = $node['args']['format'];
                        $code = "sprintf($format, $code)";
                    }
                    
                    if(!isset($node['args']['encode']) || $node['args']['encode']!='no'){
                        $code = "htmlspecialchars($code)";
                    }
                    if($node['must']){
                        $code = "<?php echo $code; ?>\n";
                    }else{
                        $code = "<?php if(isset($item)) echo $code; ?>\n";
                    }
                    break;
                case 'tick':
                    $i = '$'.str_repeat('_', $deep-1).'i';
                    switch($name){
                    case 'i':
                        $code = "<?php echo $i; ?>\n";
                        break;
                    case 'even':
                        $code = "<?php echo $i % 2 ? 'odd' : 'even'; ?>\n";
                        break;
                    default:
                        $code = '';
                        break;
                    }
                    break;
                case 'unit':
                    $data = $this->expand($deep, 'data');
                    $item = $this->expand($deep+1, 'data');
                    
                    $args = array('$_site', '$_lang', '$_data');
                    if(isset($node['args']['data'])){
                        $args[] = $this->explain($deep, $node['args']['data']);
                        $deep -= 1;
                    }
                    for($i=$deep; $i>0; $i--) $args[] = $this->expand($i, 'data');
                    
                    $args = implode(', ', $args);
                    
                    $code = "<?php if(isset(self::\$unit['$name'])){ ";
                    $code.= "foreach(self::\$unit['$name'] as $item){";
                    $code.= "echo {$item}['mode']=='node' ? call_user_func({$item}['data'], {$args}) : {$item}['data'];";
                    $code.= "}} ?>\n";
                    break;
                case 'when':
                    $a = isset($node['args']['a']) ? $this->explain($deep, $node['args']['a']) : 'TRUE';
                    $b = isset($node['args']['b']) ? $this->explain($deep, $node['args']['b']) : 'TRUE';
                    switch($name){
                    case 'eq': $pass = "$a == $b"; break;
                    case 'ne': $pass = "$a != $b"; break;
                    case 'gt': $pass = "$a >  $b"; break;
                    case 'ge': $pass = "$a >= $b"; break;
                    case 'lt': $pass = "$a <  $b"; break;
                    case 'le': $pass = "$a <= $b"; break;
                    case 'ok': $pass = " isset($a) &&  $a"; break;
                    case 'no': $pass = "!isset($a) || !$a"; break;
                    default:   $pass = 'FALSE';
                    }
                    if(isset($node['args']['mode'])){
                        $mode = $node['args']['mode'];
                        if(isset(self::$when[$mode])){
                            $mode = self::$when[$mode];
                        }else if($mode[0]=='\'' || $mode[0]=='"'){
                            $mode = substr($mode, 1, -1);
                        }
                        $code = "<?php if($pass){ echo '$mode'; } ?>\n";
                    }
                    break;
                case 'url':
                case 'url-0':
                    $path = $_SERVER['PHP_SELF'];
                    $args = array();
                    $glue = $node['base']=='url' ? '&amp;' : '&';
                    
                    foreach(self::$site['args'] as $key=>$val){
                        $args[$key] = "'$key'=>\$_site['args']['$key']";
                    }
                    
                    foreach($node['args'] as $key=>$val){
                        if($val[0]=='\''||$val[0]=='"'){
                            $args[$key] = "'$key'=>$val";
                        }else{
                            $args[$key] = "'$key'=>".$this->explain($deep, $val);
                        }
                    }
                    switch($name){
                    case 'part':
                        $args = "array(".implode(',', $args).")";
                        $value = "http_build_query($args, '', $glue)";
                        break;
                    case 'self':
                        $args = "array_merge(\$_GET, array(".implode(',', $args)."))";
                        $code = "<?php echo '{$_SERVER['PHP_SELF']}?'.http_build_query($args, '', '$glue'); ?>\n";
                        break;
                    case 'base':
                        $args = "array(".implode(',', $args).")";
                        $code = "<?php echo '{$_SERVER['PHP_SELF']}?'.http_build_query($args, '', '$glue'); ?>\n";
                        break;
                    }
                    break;
                }
            }else{
                switch($base){
                case 'site':
                case 'lang':
                case 'data':
                    $code = $this->expand($deep, $base, $name);
                    $mode = isset($node['args']['mode']) ? $node['args']['mode'] : 'list';
                    switch($mode){
                    case 'kval':
                        $data = $this->expand($deep, $base, $name);
                        $temp = '$'.str_repeat('_', $deep).'temp';
                        $item = $this->expand($deep+1, $base);
                        $i = '$'.str_repeat('_', $deep).'i';
                        $code = "<?php $temp = array(); foreach($data as \$key=>\$val){{$temp}[] = compact('key', 'val');}";
                        $code .= "$i=0; foreach($temp as $item) {  $i+=1;?>";
                        foreach($node['subs'] as $_node){
                            $code .= $this->compile($_node, $deep+1);
                        }
                        $code .="<?php } ?>\n";
                        break;
                    case 'item':
                        $data = $this->expand($deep, $base, $name);
                        $item = $this->expand($deep+1, $base);
                        
                        $code = "<?php $item = $data; ?>\n";
                        foreach($node['subs'] as $_node){
                            $code .= $this->compile($_node, $deep+1);
                        }
                        break;
                    default:
                        $data = $this->expand($deep, $base, $name);
                        $item = $this->expand($deep+1, $base);
                        $i = '$'.str_repeat('_', $deep).'i';
                        $code = "<?php $i=0; foreach($data as $item) {  ?>\n";
                        foreach($node['subs'] as $_node){
                            $code .= $this->compile($_node, $deep+1);
                        }
                        $code .="<?php $i+=1; } ?>\n";
                        break;
                    }
                    break;
                case 'when':
                    $a = isset($node['args']['a']) ? $this->explain($deep, $node['args']['a']) : 'TRUE';
                    $b = isset($node['args']['b']) ? $this->explain($deep, $node['args']['b']) : 'TRUE';
                    
                    switch($name){
                    case 'eq': $pass = "$a == $b"; break;
                    case 'ne': $pass = "$a != $b"; break;
                    case 'gt': $pass = "$a >  $b"; break;
                    case 'ge': $pass = "$a >= $b"; break;
                    case 'lt': $pass = "$a <  $b"; break;
                    case 'le': $pass = "$a <= $b"; break;
                    case 'ok': $pass = " isset($a) &&  $a"; break;
                    case 'no': $pass = "!isset($a) || !$a"; break;
                    }
                    
                    $code = "<?php if($pass){ ?>\n";
                    foreach($node['subs'] as $_node){
                        $code .= $this->compile($_node, $deep);
                    }
                    $code .= "<?php } ?>\n";
                    break;
                case 'unit':
                    if(!isset(self::$unit[$name])) self::$unit[$name]=array();
                    
                    $args = array('$_site', '$_lang', '$_data');
                    for($i=$deep+1; $i>1; $i--) $args[] = $this->expand($i,'data');
                    $args = implode(', ', $args);
                    
                    $func = "{$base}_{$name}_".count(self::$unit[$name]);
                    $code = "<?php if(!function_exists('$func')){";
                    $code.= " function $func($args){ ?>\n";
                    foreach($node['subs'] as $_node){
                        $code .= $this->compile($_node, $deep+1);
                    }
                    $code.= "<?php }} ?>\n"; 
                    if(isset($node['args']['mode']) && $node['args']['mode']=='node'){
                        $code.= "<?php self::\$unit['$name'][]= array('mode'=>'node', 'data'=> '$func'); ?>";
                    }else{
                        $args = array('$_site', '$_lang', '$_data');
                        for($i=$deep; $i>0; $i--) $args[] = $this->expand($i,'data');
                        $args = implode(', ', $args);
                        $code.= "<?php ob_start(); $func($args); self::\$unit['$name'][]= array('mode'=>'text', 'data'=> ob_get_clean()); ?>";
                    }
                    break;
                }
            }
            
            return $code;
        }
    }
    
    
    public function explain($deep, $text){
        if($text[0]=='\''||$text[0]=='"'){
            return $text;
        }else{
            if(strpos($text, ':')!==FALSE){
                list($base, $name) = explode(':', $text, 2);
            }else{
                $base = 'data';
                $name = $text;
            }
            $text = $this->expand($deep, $base, $name);
        }
        return $text;
    }
    
    public function expand($deep, $base, $name=NULL){
        $code = '';
        if($name){
            foreach(explode('/', $name) as $node){
                switch($node){
                case '..':
                    $deep -= 1;
                    break;
                case '.':
                    break;
                case '':
                    $deep = 0;
                    break;
                default:
                    $code .= "['$node']";
                    break;
                }
            }
        }
        switch($base){
        case 'site':
            return '$_site'.$code;
        case 'lang':
            return '$_lang'.$code;
        case 'data':
            return ($deep>0 ? '$'.str_repeat('_', $deep) : '$_').'data'.$code; 
        }
        return NULL;
    }
    
}

class TplAnalyzer extends TplParser {
    public function render(){
        return $this->build($this->node);
    }
    
    public function build($node, $deep=0){
        $text = '';
        if($node['type']){
            $base = $node['base'] ? $node['base'] : 'data';
            $name = $node['name'];
            if($deep){
                $text.= "<strong>{$base}:{$name}</strong> ";
                if($node['args']){
                    foreach($node['args'] as $key=>$val){
                        $val = htmlspecialchars($val);
                        $text.= " <em class=\"$key\">$key=$val</em>";
                    }
                }
            }
            if($node['type']==2){
                $text.= '<ul>';
                foreach($node['subs'] as $_node){
                    if($_node['type']){
                        $_base = $_node['base'] ? $_node['base'] : 'data';
                        $_name = $_node['name'];
                        $_text = $this->build($_node, $deep+1);
                        if($_base=='data' && $_name=='content' && isset($this->data['content'])){
                            $_text.= $this->data['content'];
                        }
                        $text.= "<li class=\"{$_base}\">{$_text}</li>";
                    }
                }
                $text.= '</ul>';
            }
        }
        return $text;
    }
}

class Template {
    static $mode = 1;
    
    static $site = array('args'=>array());
    static $mods = array(
        'TplProducer', 
        'TplCompiler', 
        'TplAnalyzer'
    );
    static $when = array(
        'select' => ' selected="selected"', 
        'check'  => ' checked="checked"',
        'active' => ' class="active"'
    );
    
    public $lang = array();
    public $data = array();
    
    public function __construct($path){
        $this->path = $path;
    }
    
    public function render($path=NULL){
        $M = self::$mods[self::$mode];
        
        TplParser::$when = self::$when;
        TplParser::$site = self::$site;
        
        $m = new $M($this->path);
        
        $m->data = $this->data;
        $m->lang = $this->lang;
        
        if($path){
            $text =  $m->render();
            file_put_contents($path, $text);
        }else{
            return $m->render();
        }
    }
}
?>

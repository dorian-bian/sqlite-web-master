<?php
class SqlLexer {
    public $tokenset = array(
        'SEMI', 'EXPLAIN', 'QUERY', 'PLAN', 'BEGIN', 'TRANSACTION', 'DEFERRED', 'IMMEDIATE',
        'EXCLUSIVE', 'COMMIT', 'END', 'ROLLBACK', 'SAVEPOINT', 'RELEASE', 'TO', 'TABLE',
        'CREATE', 'IF', 'NOT', 'EXISTS', 'TEMP', 'LP', 'RP', 'AS', 'COMMA', 'ID', 'INDEXED',
        'ABORT', 'ACTION', 'AFTER', 'ANALYZE', 'ASC', 'ATTACH', 'BEFORE', 'BY', 'CASCADE',
        'CAST', 'COLUMNKW', 'CONFLICT', 'DATABASE', 'DESC', 'DETACH', 'EACH', 'FAIL', 'FOR',
        'IGNORE', 'INITIALLY', 'INSTEAD', 'LIKE_KW', 'MATCH', 'NO', 'KEY', 'OF', 'OFFSET',
        'PRAGMA', 'RAISE', 'REPLACE', 'RESTRICT', 'ROW', 'TRIGGER', 'VACUUM', 'VIEW', 'VIRTUAL',
        'REINDEX', 'RENAME', 'CTIME_KW', 'ANY', 'OR', 'AND', 'IS', 'BETWEEN', 'IN', 'ISNULL',
        'NOTNULL', 'NE', 'EQ', 'GT', 'LE', 'LT', 'GE', 'ESCAPE', 'BITAND', 'BITOR', 'LSHIFT',
        'RSHIFT', 'PLUS', 'MINUS', 'STAR', 'SLASH', 'REM', 'CONCAT', 'COLLATE', 'BITNOT', 
        'STRING', 'JOIN_KW', 'CONSTRAINT', 'DEFAULT', 'NULL', 'PRIMARY', 'UNIQUE', 'CHECK', 
        'REFERENCES', 'AUTOINCR', 'ON', 'INSERT', 'DELETE', 'UPDATE', 'SET', 'DEFERRABLE', 
        'FOREIGN', 'DROP', 'UNION', 'ALL', 'EXCEPT', 'INTERSECT', 'SELECT', 'DISTINCT', 'DOT',
        'FROM', 'JOIN', 'USING', 'ORDER', 'GROUP', 'HAVING', 'LIMIT', 'WHERE', 'INTO', 'VALUES',
        'INTEGER', 'FLOAT', 'BLOB', 'REGISTER', 'VARIABLE', 'CASE', 'WHEN', 'THEN', 'ELSE', 
        'INDEX', 'ALTER', 'ADD', 'TO_TEXT', 'TO_BLOB', 'TO_NUMERIC', 'TO_INT', 'TO_REAL', 
        'ISNOT', 'END_OF_FILE', 'ILLEGAL', 'SPACE', 'UNCLOSED_STRING', 'FUNCTION', 'COLUMN', 
        'AGG_FUNCTION', 'AGG_COLUMN', 'CONST_FUNC', 'UMINUS', 'UPLUS');
        
    public $keywords = array(
        'ABORT', 'ACTION', 'ADD', 'AFTER', 'ALL', 'ALTER', 'ANALYZE','AND', 'AS', 'ASC', 
        'ATTACH', 'AUTOINCREMENT', 'BEFORE', 'BEGIN', 'BETWEEN','BY', 'CASCADE', 'CASE', 
        'CAST', 'CHECK', 'COLLATE', 'COLUMN', 'COMMIT','CONFLICT', 'CONSTRAINT', 'CREATE', 
        'CROSS','CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'DATABASE', 'DEFAULT', 
        'DEFERRABLE', 'DEFERRED', 'DELETE', 'DESC', 'DETACH', 'DISTINCT', 'DROP', 'EACH', 
        'ELSE', 'END', 'ESCAPE',  'EXCEPT','EXCLUSIVE', 'EXISTS', 'EXPLAIN', 'FAIL', 'FOR', 
        'FOREIGN', 'FROM', 'FULL', 'GLOB', 'GROUP', 'HAVING', 'IF', 'IGNORE', 'IMMEDIATE', 
        'IN', 'INDEX', 'INDEXED', 'INITIALLY', 'INNER', 'INSERT', 'INSTEAD', 'INTERSECT', 
        'INTO', 'IS', 'ISNULL', 'JOIN', 'KEY', 'LEFT', 'LIKE', 'LIMIT', 'MATCH', 'NATURAL', 
        'NO', 'NOT', 'NOTNULL', 'NULL', 'OF', 'OFFSET', 'ON', 'OR', 'ORDER', 'OUTER', 'PLAN', 
        'PRAGMA', 'PRIMARY', 'QUERY', 'RAISE', 'REFERENCES', 'REGEXP', 'REINDEX', 'RELEASE',
        'RENAME', 'REPLACE', 'RESTRICT', 'RIGHT', 'ROLLBACK', 'ROW', 'SAVEPOINT', 'SELECT',
        'SET', 'TABLE','TEMP', 'TEMPORARY', 'THEN', 'TO', 'TRANSACTION', 'TRIGGER', 'UNION',
        'UNIQUE', 'UPDATE', 'USING', 'VACUUM', 'VALUES', 'VIEW', 'VIRTUAL', 'WHEN', 'WHERE');
    
    public $mappings = array(
        'COLUMN'                => 'COLUMNKW',
        'TEMP'                  => 'TEMP',
        'TEMPORARY'             => 'TEMP',
        'CROSS'                 => 'JOIN_KW',
        'FULL'                  => 'JOIN_KW',
        'INNER'                 => 'JOIN_KW',
        'LEFT'                  => 'JOIN_KW',
        'NATURAL'               => 'JOIN_KW',
        'OUTER'                 => 'JOIN_KW',
        'RIGHT'                 => 'JOIN_KW',
        'CURRENT_DATE'          => 'CTIME_KW', 
        'CURRENT_TIME'          => 'CTIME_KW', 
        'CURRENT_TIMESTAMP'     => 'CTIME_KW',
        'GLOB'                  => 'LIKE_KW',
        'LIKE'                  => 'LIKE_KW',
        'REGEXP'                => 'LIKE_KW',
        'AUTOINCREMENT'         => 'AUTOINCR',
    );
    
    public $fallback = array(
        'ABORT', 'ACTION', 'AFTER', 'ANALYZE', 'ASC', 'ATTACH', 'BEFORE', 'BEGIN', 'BY', 
        'CASCADE', 'CAST', 'COLUMNKW', 'CONFLICT', 'DATABASE', 'DEFERRED', 'DESC', 'DETACH', 
        'EACH', 'END', 'EXCLUSIVE', 'EXPLAIN', 'FAIL', 'FOR', 'IGNORE', 'IMMEDIATE', 'INITIALLY', 
        'INSTEAD', 'LIKE_KW', 'MATCH', 'NO', 'PLAN', 'QUERY', 'KEY', 'OF', 'OFFSET', 'PRAGMA', 
        'RAISE', 'RELEASE', 'REPLACE', 'RESTRICT', 'ROW', 'ROLLBACK', 'SAVEPOINT', 'TEMP', 
        'TRIGGER', 'VACUUM', 'VIEW', 'VIRTUAL', 'EXCEPT', 'INTERSECT', 'UNION', 'REINDEX', 
        'RENAME', 'CTIME_KW', 'IF');
    
    public $text = '';
    public $data = array();
    
    public function __construct($text){
        $keywords = $this->keywords;
        $this->keywords = array_merge(array_combine($keywords, $keywords), $this->mappings);
        $this->tokenset = array_flip($this->tokenset);
        
        foreach($this->fallback as $word){
            $this->tokenset[$word] +=  1<< 8;
        }
        $this->tokenset['ID'] = 1 << 8;
        
        if($text) $this->data = $this->smash($text);
    }
    
    public function smash($text){
        $this->text = $text;
        
        $offset = 0;
        $tokens = array();
        while($rest=strlen($text)){
            switch($text[0]){
            case " ": case "\t": case "\n": case "\f": case "\r":
                preg_match('/^\s+/', $text, $m);
                $size = strlen($m[0]);
                $name = 'SPACE';
                break;
            case '-':
                if(preg_match('/^--.*?$/m', $text, $m)){
                    $size = strlen($m[0]);
                    $name = 'SPACE';
                }else{
                    $size = 1;
                    $name = 'MINUS';
                }
                break;
            case '(':
                $size = 1;
                $name ='LP';
                break;
            case ')':
                $size = 1;
                $name = 'RP';
                break;
            case ';':
                $size = 1;
                $name = 'SEMI';
                break;
            case '+':
                $size = 1;
                $name = 'PLUS';
                break;
            case '*':
                $size = 1;
                $name = 'STAR';
                break;
            case '/':
                if(($rest>1 && $text[1]!='*') || $rest==1){
                    $size = 1;
                    $name ='SLASH';
                }else{
                    $i = strpos($text, '*/', 2);
                    $size = $i===FALSE ? $rest : $i + 2;
                    $name = 'SPACE'; 
                }
                break;
            case '%':
                $size = 1;
                $name = 'REM';
                break;
            case '=':
                $size = $rest>1 && $text[1]=='=' ? 2 : 1;
                $name = 'EQ';
                break;
            case '<':
                $size = 2;
                switch($rest>1 ? $text[1] : NULL){
                case '=':
                    $name = 'LE';
                    break;
                case '>':
                    $name = 'NE';
                    break;
                case '<':
                    $name = 'LSHIFT';
                    break;
                default:
                    $size = 1;
                    $name = 'LT';
                    break;
                }
                break;
            case '>':
                $size = 2;
                switch($rest>1 ? $text[1] : NULL){
                case '=':
                    $name = 'LE';
                    break;
                case '>':
                    $name = 'NE';
                    break;
                default:
                    $size = 1;
                    $name = 'LT';
                    break;
                }
                break;
            case '!':
                if($rest>1 && $text[1]=='='){
                    $size = 2;
                    $name = 'ILLEGAL';
                }else{
                    $size = 1;
                    $name = 'NE';
                }
                break;
            case '|':
                if($rest>1 && $text[1]=='|'){
                    $size = 2;
                    $name = 'CONCAT';
                }else{
                    $size = 1;
                    $name = 'BITOR';
                }
                break;
            case ',':
                $size = 1;
                $name = 'COMMA';
                break;
            case '&':
                $size = 1;
                $name = 'BITAND';
                break;
            case '~':
                $size = 1;
                $name = 'BITNOT';
                break;
            case '`':
            case '\'':
            case '"':
                $c = $text[0];
                for($i=1; $i<$rest; $i++){
                    if($text[$i]==$c){
                        if($i<$rest-1 && $text[$i+1]==$c){
                            $i++;
                        }else{
                            break;
                        }
                    }
                }
                
                if($i<$rest){
                    $size = $i + 1;
                    if($c=='\''){
                        $name = 'STRING';
                    }else{
                        $name = 'ID';
                    }
                }else{
                    $size = $rest;
                    $name = 'ILLEGAL';
                }
                break;
            case '.':
                if($rest==1 || !ctype_digit($text[1])){
                    $size = 1;
                    $name = 'DOT';
                    break;
                }
            case '0': case '1': case '2': case '3': case '4':
            case '5': case '6': case '7': case '8': case '9':
                $rule = '/(\d+(\.\d*)?|\.\d+)([eE][+-]?\d+)?/';
                if(preg_match($rule, $text, $m)){
                    $size = strlen($m[0]);
                    $name = 'FLOAT';
                }elseif(preg_match('/^\d+/', $text, $m)){
                    $size = strlen($m[0]);
                    $name = 'INTEGER';
                }
                break;
            case '[':
                $i = $rest>1 ? strpos($text, ']', 1) : FALSE;
                if($i===FALSE){
                    $size = 1;
                    $name = 'ILLEGAL';
                }else{
                    $size = $i+1;
                    $name = 'ID';
                }
                break;
            case '?':
                preg_match('/^\?[0-9]*/', $text, $m);
                $size = strlen($m[0]);
                $name = 'VAR';
                break;
            case '$':
            case '@':
            case ':':
                if(preg_match('/^[0-9a-zA-Z_$\x7F-\xFF]+/', $text, $m)){
                    $size = strlen($m[0]);
                    $name = 'VAR';
                }else{
                    $size = 1;
                    $name = 'ILLEGAL';
                }
                break;
            case 'x': case 'X':
                if($rest>2 && $text[1]=='\'' && strpos($text, "'", 2)!==FALSE){
                    if(preg_match('/^[xX]\'[0-9a-fA-F]+\'/', $text, $m)){
                        $size = strlen($m[0]);
                        $name = 'BLOB';
                    }else{
                        $size = 2;
                        $name = 'ILLEGAL';
                    }
                }
            default:
                if(preg_match('/^[0-9a-zA-Z_$\x7F-\xFF]+/', $text, $m)){
                    $word = strtoupper($m[0]);
                    $size = strlen($m[0]);
                    $name = isset($this->keywords[$word]) ? $this->keywords[$word] : 'ID';
                }else{
                    $size = 1;
                    $name = 'ILLEGAL';
                }
                break;
            }

            $tokens[] = array($this->tokenset[$name], substr($text, 0, $size), $offset);
            
            $text = substr($text, $size);
            $offset += $size;
        }
        
        $tokens[] = array($this->tokenset['END_OF_FILE'],  NULL, $offset);
        return $tokens;
    }
}
?>

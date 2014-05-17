<?php
class Lorem {
    public $dict = array('exercitationem', 'perferendis', 'perspiciatis', 
        'sunt', 'iure', 'nam', 'nobis', 'eum', 'cum', 'officiis', 'excepturi',
        'odio', 'consectetur', 'quasi', 'aut', 'quisquam', 'vel', 'eligendi',
        'itaque', 'non', 'odit', 'tempore', 'quaerat', 'dignissimos',
        'facilis', 'neque', 'nihil', 'expedita', 'vitae', 'vero', 'ipsum',
        'nisi', 'animi', 'cumque', 'pariatur', 'velit', 'modi', 'natus',
        'iusto', 'eaque', 'sequi', 'illo', 'sed', 'ex', 'et', 'voluptatibus',
        'tempora', 'veritatis', 'ratione', 'assumenda', 'incidunt', 'nostrum',
        'placeat', 'aliquid', 'fuga', 'provident', 'praesentium', 'rem',
        'necessitatibus', 'suscipit', 'adipisci', 'quidem', 'possimus',
        'voluptas', 'debitis', 'sint', 'accusantium', 'unde', 'sapiente',
        'voluptate', 'qui', 'aspernatur', 'laudantium', 'soluta', 'amet',
        'quo', 'aliquam', 'saepe', 'culpa', 'libero', 'ipsa', 'dicta',
        'reiciendis', 'nesciunt', 'doloribus', 'autem', 'impedit', 'minima',
        'maiores', 'repudiandae', 'ipsam', 'obcaecati', 'ullam', 'enim',
        'totam', 'delectus', 'ducimus', 'quis', 'voluptates', 'dolores',
        'molestiae', 'harum', 'dolorem', 'quia', 'voluptatem', 'molestias',
        'magni', 'distinctio', 'omnis', 'illum', 'dolorum', 'voluptatum', 'ea',
        'quas', 'quam', 'corporis', 'quae', 'blanditiis', 'atque', 'deserunt',
        'laboriosam', 'earum', 'consequuntur', 'hic', 'cupiditate',
        'quibusdam', 'accusamus', 'ut', 'rerum', 'error', 'minus', 'eius',
        'ab', 'ad', 'nemo', 'fugit', 'officia', 'at', 'in', 'id', 'quos',
        'reprehenderit', 'numquam', 'iste', 'fugiat', 'sit', 'inventore',
        'beatae', 'repellendus', 'magnam', 'recusandae', 'quod', 'explicabo',
        'doloremque', 'aperiam', 'consequatur', 'asperiores', 'commodi',
        'optio', 'dolor', 'labore', 'temporibus', 'repellat', 'veniam',
        'architecto', 'est', 'esse', 'mollitia', 'nulla', 'a', 'similique',
        'eos', 'alias', 'dolore', 'tenetur', 'deleniti', 'porro', 'facere',
        'maxime', 'corrupti','laborum', 'eveniet');
    
    public function number($min, $max, $precise=0){
        return mt_rand($min, $max) +  mt_rand(0, 10 ^ $precise) / (10 ^ $precise);
    }
    
    public function article($min=3, $max=5) {
        $text = array();
        for($i=0, $ii=mt_rand($min, $max); $i<$ii; $i++){
            $text[] = $this->paragraph();
        }
        
        return implode("\n", $text);
    }
    
    public function paragraph($min=1, $max=5) {
        $text = array();
        for($i=0, $ii=mt_rand($min, $max); $i<$ii; $i++){
            $text[] = $this->sentence();
        }
        
        return '  '.implode(' ', $text);
    }
    
    public function sentence($min=6, $max=18) {
        $text = array();
        $count = mt_rand($min, $max);
        for($i=0, $ii=mt_rand(1,3); $i<$ii && $count > 0; $i++){
            $s = $i<$ii-1 ? mt_rand(1, $count) : $count;
            $text[] = $this->words($s);
            $count -= $s;
        }
        
        return ucfirst(implode(',', $text)).(mt_rand(1,10) > 1 ? '.' : '?');
    }
    
    public function words($size, $glue=' '){
        $text = array();
        $buffer = count($this->dict);
        do{
            shuffle($this->dict);
            for($i=0, $ii=min($size, $buffer); $i<$ii; $i++){
                $text[] = $this->dict[$i];
            }
            $size -= $buffer;
        }while($size > 0);
        return implode($glue, $text);
    }
    
    public function format($format){
        return preg_replace_callback('/{?\[([0-9]+)?(?:-([0-9]+))?([acdwxzACDWXZ])\]}?/',
            array($this, 'format_cb'), $format);
    }
    
    public function format_cb($m){
        static $i = 0;
        if($m[0][0]=='{'){
            return substr($m[0], 1, -1);
        }else{
            $ii = $m[1] ? $m[1] : 1;
            $ii = $m[2] && $m[2] > $ii ? mt_rand($ii, (int)$m[2]) : $ii;
            $tt = '';
            $m3 = strtolower($m[3]);
            for($i=0; $i<$ii; $i++){
                switch($m3){
                case 'a':
                    $aa  = mt_rand(0, 35);
                    $tt .= $aa < 10 ? $aa : chr(ord('a') + $aa - 10);
                    break;
                case 'c':
                    $tt .= chr(ord('a') + mt_rand(0, 26));
                    break;
                case 'd':
                    $tt .= mt_rand(0, 9);
                    break;
                case 'w':
                    $tt .= ' '.$this->dict[mt_rand(0, count($this->dict)-1)];
                    break;
                case 'x':
                    $aa  = mt_rand(0, 16);
                    $tt .= $aa < 10 ? $aa : chr(ord('a') + $aa - 10);
                    break;
                case 'z':
                    $tt .= '-'.$this->dict[mt_rand(0, count($this->dict)-1)];
                    break;
                }
            }
            if($m3=='i')$tt.= $i++;
            if($m3=='w' || $m3=='z') $tt = substr($tt, 1);
            return ctype_upper($m[3]) ? strtoupper($tt) : $tt;
        }
    }
    
    public function image($width, $height){
        ob_start();
        $im = imagecreatetruecolor($width, $height);
        if(function_exists('imageantialias')) imageantialias($im, true);
        $bg = imagecolorallocate($im, 240,240,240);
        $fg = imagecolorallocate($im, 100,100,100);
        imagefill($im, 0, 0, $bg);
        
        imagerectangle($im, 0, 0, $width-1, $height-1, $fg);
        imagesetthickness($im, 1);
        
        imageline($im, 0, 0, $width-1, $height-1, $fg);
        imageline($im, 0, $height-1, $width-1, 0, $fg);
        imagejpeg($im);
        imagedestroy($im);
        return ob_get_clean();
    }
}
?>

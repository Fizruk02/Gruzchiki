<?


class template
{
    
    
    public function getList( $POST ){

        $res = array_map(function($it) {
            return array_merge($it, ['code'=> file_get_contents(self::filedir($it['id']) )]) ;
        }, arrayQuery('SELECT * FROM `a_parser` ORDER BY name', [], true));

        return json_encode([
             'success'=> 'ok'
            ,'data'=> $res
        ]);
    
    }

    public function edit( $POST ){
        
        if(!$id = $POST['id'])
            return response_if_error('не хватает параметров');
        
        arrayQuery('UPDATE `a_parser` SET name=:n  WHERE id=:id', [ ':n'=> $POST['name'], ':id'=> $id  ]);
        file_put_contents(self::filedir($id), $POST['code']);
        return json_encode([
             'success'=> 'ok'
        ]);
    
    }
    
    public function remove( $POST ){
        
        if(!$id = $POST['id'])
            return response_if_error('не хватает параметров');
        
        query('DELETE FROM `a_parser` WHERE id=?', [ $id ]);
        rename(self::filedir($id), self::deleteddir($id));
        return json_encode([
             'success'=> 'ok'
        ]);
    
    }
    
    public function add( $POST ){
        
        $data=singleQuery('SELECT * FROM `a_parser` WHERE id=?',[ insertQuery('INSERT INTO `a_parser` (`name`) VALUES (?)', [ $POST['name']]) ]);
        

        $code  = '<?'.PHP_EOL.
        $code .= '$_SERVER[\'DOCUMENT_ROOT\'] = __DIR__.\'/../../public_html\';'.PHP_EOL;
        $code .= 'require $_SERVER[\'DOCUMENT_ROOT\'].\'/admin/functions/functions.php\';'.PHP_EOL;
        $code .='setlocale(LC_ALL, \'ru_RU\');'.PHP_EOL;
        $code .='date_default_timezone_set(\'Europe/Moscow\');'.PHP_EOL;
        $code .='header(\'Content-type: text/html; charset=utf-8\');'.PHP_EOL;
        $code .='include_once $_SERVER[\'DOCUMENT_ROOT\'] . \'/admin/parser/phpquery.php\';'.PHP_EOL;
        $code .='if(!$src=str_replace(\'.php\',\'\',basename(__FILE__))) exit;'.PHP_EOL.PHP_EOL.PHP_EOL;
        $code .='$url = \'\';'.PHP_EOL;
        $code .='$doc = phpQuery::newDocument(file_get_contents($url));'.PHP_EOL;






        
        file_put_contents(self::filedir($data['id']), $code);
        $data['code']=$code;
        return json_encode([
              'success'=> 'ok'
             ,'data'=> $data
        ]);
    
    }
    
    private function filedir($id){
        $dir = $_SERVER['DOCUMENT_ROOT'].'/../cron/parser/';
        if(!is_dir($dir)) mkdir($dir);
        return $dir.$id.'.php';
    }
    private function deleteddir($id){
        $dir=$_SERVER['DOCUMENT_ROOT'].'/../cron/parser/deleted/';
        if(!is_dir($dir)) mkdir($dir);
        return $dir.$id.'.php';
    }
}


















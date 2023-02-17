<?php
namespace systems\classes\db;

class db
{

    public function err(){
        global $stmt;
        if($e=$stmt->errorInfo()[2]) return bterr($e,'','');else return false;
    }

    public function deleteDuplicates($table,$uniqFields, $incFIeld='id'){
        /**
         * удаление дубликатов из таблицы
         * $table - название таблицы
         * $uniqFields - уникальные поля (массив или строка)
         * $incFIeld - шнкрементное поле таблицы (по умолчанию "id")
         * пример:
         * deleteDuplicates('users', 'chat_id')
         * deleteDuplicates('users', ['email','phone'])
         */
        $id=trim(str_replace('`', '', $incFIeld));
        $table=trim(str_replace('`', '', $table));
        if(!$table||!$uniqFields || !$id) return;
        if(!is_array($uniqFields)) $uniqFields=[$uniqFields];
        if(!count($uniqFields)) return;
        $f=implode(',',array_map(function($it){return '`'.trim(str_replace('`', '', $it)).'`';},$uniqFields));
        query('DELETE `'.$table.'` FROM `'.$table.'`
              LEFT OUTER JOIN (SELECT MIN(`'.$id.'`) AS `'.$id.'`, '.$f.' FROM `'.$table.'` GROUP BY '.$f.') AS `tmp` ON `'.$table.'`.`'.$id.'` = `tmp`.`'.$id.'`  
              WHERE `tmp`.`'.$id.'` IS NULL;');
    }

    public function create_tables($arr) {
        /**
         * создает нужные таблицы для работы модуля, необходимо передать массив запросов
         * например
         * $arr = [ 'CREATE TABLE `test` ( `id` INT NOT NULL AUTO_INCREMENT , `t_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`)) ENGINE = InnoDB' ]
         */
        $tables = [];
        foreach($arr as $sql){
            $table = getStrBetween($sql, 'CREATE TABLE', '(');
            $table =  trim(str_replace('`', '', $table));
            if($table)
                array_push($tables, [ 'table'=> $table, 'status'=> false, 'create'=>$sql]);

        }

        $table_name = 'Tables_in_'.dbName;
        $result = arrayQuery("SHOW TABLES");
        foreach($result as $row){
            $table = $row[$table_name];
            foreach($tables as $tab)
                if($tab['table']==$table)
                    $tab['status'] = true;
        }
        foreach($tables as $tab)
            if(!$tab['status']) query($tab['create']);
    }

    /**
     * СПИСОК ТАБЛИЦ
     * @param $par:
     *      full - ассоциативныый массив ключ/массив с полями таблиц, если не указан, то обычный массив
     *      no_id - удалять поле id и ID в возврате полей ( если  )
     * @return array
     */
    public function show_tables( $par ){
        $dbTables = arrayQuery('SHOW TABLES');

        $dbTables = array_map(function($it){
            return array_values($it)[0];
        }, $dbTables);

        if( $par['full']??false ) {
            $res=[];
            foreach($dbTables as $table) {
                $cols=$this->show_columns($table, 'short');

                if( $par['no_id']??false ) {
                    if( ($k = array_search('id', $cols))!==false || ($k = array_search('ID', $cols))!==false  ) {
                        array_splice($cols, $k,1);
                    }
                }
                $res[$table]=$cols;
            }
            $dbTables = $res;
        }

        return $dbTables;
    }

    /**
     * @param $table - Таблицы
     * @param $type: short - обычный массив, full - ассоциативныый массив ключ/массив с полями таблиц
     * @return array
     */
    public function show_columns( $table, $type='short' ){
        $arr = arrayQuery('SHOW COLUMNS FROM `'.$table.'`');
        if($type==='short') {
            $arr = array_map(function($it){
                return $it['Field'];
            }, $arr);
        }
        return $arr;
    }

    public function err_correction(array $p){
        global $stmt;
        $err=$p['err'];
        $query=$p['query'];
        $par=$p['par'];

        if(strpos( $err,"doesn't have a default value")) {
            if ($field = getStrBetween($err, "Field '", "'")){
                if ($table = singleQuery('EXPLAIN ' . $query, $par)['table']) {
                    $def = false;
                    $cols = arrayQuery('SHOW COLUMNS FROM `' . $table . '`');
                    foreach ($cols as $col) {
                        if (mb_strtolower($col['Field']) === mb_strtolower($field)) {
                            if (in_array($col['Type'], ['float','int'])||strpos($col['Type'],'tinyint')!==false) $def = 0;
                            if (strpos($col['Type'],'varchar')!==false) $def = '""';
                            if ($def !== false) {
                                query('ALTER TABLE `' . $table . '` ALTER `' . $field . '` SET DEFAULT ' . $def);
                                qwer('ALTER TABLE `' . $table . '` ALTER `' . $field . '` SET DEFAULT ' . $def, 'query');
                                if (!$err2=$stmt->errorInfo()[2]) {
                                    insertQuery($query, $par,1);
                                    return true;
                                } else qwer('ALTER ERR: '.$err2, 'query');
                            } else {
                                qwer($table.' - '.$field, 'query');
                                qwer('Type: '.$col['Type'], 'query');
                            }
                            break;
                        }
                    }
                }
            }
        }


        qwer($query,'query');
        qwer($err,'query');
        return false;


    }


}
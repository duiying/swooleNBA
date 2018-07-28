<?php
class AysMysql {
    public $dbSource = "";
    public $dbConfig = [];
    public function __construct() {
        $this->dbSource = new Swoole\Mysql;

        $this->dbConfig = [
            'host'      => '127.0.0.1',
            'port'      => 3306,
            'user'      => 'root',
            'password'  => 'WYX*wyx123',
            'database'  => 'swoole',
            'charset'   => 'utf8',
        ];
    }

    public function execute($id, $username) {
        // connect
        $this->dbSource->connect($this->dbConfig, function($db, $result) use ($id, $username) {
            echo "mysql-connect".PHP_EOL;
            if($result === false) {
                var_dump($db->connect_error);
            }

            $sql = "update test set `username` = '".$username."' where id=".$id;
            $db->query($sql, function($db, $result){
                if($result === false) {
                    var_dump($db->error);
                }elseif($result === true) {
                    var_dump($db->affected_rows);
                }else {
                    print_r($result);
                }

                // 关闭mysql连接
                $db->close();
            });

        });
        return true;
    }
}

$obj    = new AysMysql();
$flag   = $obj->execute(1, 'test11');
var_dump($flag).PHP_EOL;
echo "start".PHP_EOL;
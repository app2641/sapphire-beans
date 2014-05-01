<?php


namespace Sapphire\Database;

class Database extends \PDO
{

    /**
     * database.iniデータ
     *
     * @var String
     **/
    private $ini_data;


    /**
     * 有効なドライバー群
     *
     * @var Array
     **/
    protected static $save_point_transactions = array("pgsql", "mysql");


    /**
     * トランザクションのレベル
     *
     * @var int
     **/
    protected $transaction_level = 0;



    /**
     * コンストラクタ
     *
     * @param  String $database_block  database.ini区画
     * @return void
     **/
    public function __construct ($database_block)
    {
        $this->_ifExistsDatabaseIni($database_block);

        $dsn  = sprintf('mysql:dbname=%s;host=%s', $this->ini_data['db'], $this->ini_data['host']);
        parent::__construct($dsn, $this->ini_data['username'], $this->ini_data['password'], array(
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ));
    }



    /**
     * database.iniファイルの設定が正しいかどうかを確認する
     *
     * @param String $database_block  database.iniの区画
     * @return 
     **/
    private function _ifExistsDatabaseIni ($database_block)
    {
        // DB_INI_FILE 定数が設定されているかどうか
        if (! defined('DB_INI_FILE')) {
            throw new \Exception('Is not defined database.ini constant!');
        }

        // database.iniファイルが存在するかどうか
        if (! file_exists(DB_INI_FILE)) {
            throw new \Exception('Is not exists database.ini file!');
        }


        $this->ini_data = parse_ini_file(DB_INI_FILE, true)[$database_block];
        if (! $this->ini_data) {
            throw new \Exception('Is not found '.$database_block.' block!');
        }
    }


    
    /**
     * 指定ドライバが正しいかどうか
     *
     * @return boolean
     **/
    protected function nestable ()
    {
        return in_array(
            $this->getAttribute(\PDO::ATTR_DRIVER_NAME),
            self::$save_point_transactions
        );
    }



    /**
     * トランザクションの開始
     *
     * @return void
     **/
    public function beginTransaction ()
    {
        if(!$this->nestable() || $this->tansaction_level == 0) {
            parent::beginTransaction();
        } else {
            $this->exec("SAVEPOINT LEVEL{$this->transaction_level}");
        }

        $this->transaction_level++;
    }



    /**
     * コミット
     *
     * @return void
     **/
    public function commit ()
    {
        $this->transaction_level--;

        if(!$this->nestable() || $this->transaction_level == 0) {
            parent::commit();
        } else {
            $this->exec("RELEASE SAVEPOINT LEVEL{$this->transaction_level}");

        }
    }



    /**
     * ロールバック
     *
     * @return void
     **/
    public function rollBack () {
        $this->transaction_level--;

        if(!$this->nestable() || $this->transaction_level == 0) {
            parent::rollBack();
        } else {
            $this->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transaction_level}");
        }
    }



    /**
     * プリペアドステートメントを使用したSQLの構築
     *
     * @param  String $sql   SQL文
     * @param  Array  $bind  セットするパラメータ配列
     * @return PDOStatement
     **/
    public function build ($sql, $bind = array())
    {
        // stdclassの場合はarrayにキャスト
        if ($bind instanceof \stdClass) {
            $bind = (array) $bind;
        }

        // 配列でない場合は配列化
        if (! is_array($bind)) {
            $bind = array($bind);
        }

        // mysql strict mode 対策　STRICT_TRANS_TABLES、STRICT_ALL_TABLES
        // http://dev.mysql.com/doc/refman/5.1/ja/server-sql-mode.html
        // booleanをintに変更
        foreach($bind as $key => $val) {
            if (is_bool($val) === true) {
                $bind[$key] = (int) $val;
            }
        }

        $stmt = $this->prepare($sql);
        $stmt->execute($bind);

        return $stmt;
    }

}

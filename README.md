SapphireBeans
==============
SapphireBeans は俺々データベースクラスだ。

### 設定ファイルの準備
まず、任意の場所に database.ini ファイルを生成する。

```
[develop]
db       = "database_name"
host     = "localhost"
username = "root"
password = "root"

[production:develop]
host     = "xxx.xxx.xxx.xxx"
```

DB_INI_FILE 定数に database.ini へのパスを指定する。

```
define('DB_INI_FILE', '/path/to/database.ini');
```

### Helper クラス
Helper クラスからデータベースへ接続をする。

```
<?php
use Sapphire\Database\Helper;

$db   = Helper::connection('develop');
$sql  = 'SELECT * FROM user WHERE user.name = ?';
$user = $db->build($sql, 'hoge')->fetch();
```


### Registry クラス
一度、Helper から接続できれば Registy クラスを介して接続を取得出来る。

```
<?php
use Sapphire\Utility\Registry;

$db  = Registry::get('db');
$sql = 'UPDATE user SET name = ?, furigana = ?';
$db->build($sql, array('piyo', 'ぴよ'));
```


### 入れ子トランザクション
トランザクションを入れ子で動かすことが出来る。

```
<?php
use Sapphire\Utility\Registry;
$db = Registry::get('db');

try {
	$db->beginTransaction();
	
	try {
		$db->beginTransaction();
		$db->commit();

	} catch (\Exception) {
		$db->rollBack();
		throw $e;
	}
	
	$db->commit();

} catch (\Exception $e) {
	$db->rollBack();
	throw $e;
}
```

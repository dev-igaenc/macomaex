<?php
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

include_once('./_common.php');

if ($is_admin != 'super') exit;

//폴더생성
$ex_dir = G5_DATA_PATH.'/db_down';
@ mkdir(G5_DATA_PATH.'/db_down/', G5_DIR_PERMISSION);
@ mkdir($ex_dir, G5_DIR_PERMISSION);
@chmod($ex_dir, G5_DIR_PERMISSION);

//폴더내 모든 파일 삭제
  $dhandle = opendir($ex_dir);
  if ($dhandle) {
    while (false !== ($fname = readdir($dhandle))) {
       if (is_dir( "{$ex_dir}/{$fname}" )) {
          if (($fname != '.') && ($fname != '..')) {
             $this->rmdir_all("$ex_dir/$fname");
          }
       } else {
          unlink("{$ex_dir}/{$fname}");
       }
    }
    closedir($dhandle);
  }

	//DB다운로드 함수
	function backup_tables($tables = '*')
	{
	  
	  //get all of the tables
	  if($tables == '*')
	  {
		$tables = array();
		$result = sql_query('SHOW TABLES');
		while($row = sql_fetch_array($result))
		{
		  $tables[] = $row[0];
		}
	  }
	  else
	  {
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	  }

	  foreach($tables as $table)
	  {
		  //백업파일 생성
		  /*mysqldump -h serverHostName -u mysqlUserName -p --tab="someTableExport.csv" --fields-optionally-enclosed-by=0x22 --fields-escaped-by='' --fields-terminated-by=0x2C --lines-terminated-by=0x0D0A --databases databaseName --tables table1 table2 table3
		  */

		exec("mysqldump -u".G5_MYSQL_USER." -p".G5_MYSQL_PASSWORD." ".G5_MYSQL_DB." {$table} > ".G5_DATA_PATH."/db_down/{$table}.sql", $output, $exit_status);
		//var_dump($exit_status); // (int) The exit status of the command (0 for success, > 0 for errors)
		//var_dump($output); // (array) If exit status != 0 this will handle the error message. 
	  }
	  return $tables;
	}
//백업실행 명령
$tablez=backup_tables("company");

//압축파일 생성
$dateString = date("Y-m-d", time());
$passed=base64_decode($config['cf_10']); 
//$passed="4321";
$zip = new ZipArchive();
if( $zip->open(G5_DATA_PATH.'/db_down/DB'.$dateString.'.zip', ZipArchive::CREATE)) {
	if($passed) $zip->setPassword($passed);
	foreach($tablez as $table){
		$zip->addFile(G5_DATA_PATH . '/db_down/'.$table.'.sql','/'.$table.'.sql');
		if($passed) $zip->setEncryptionName('/'.$table.'.sql', ZipArchive::EM_AES_256);
	}
	$zip->close();
	header('Location: //'.$_SERVER['HTTP_HOST'].'/data/db_down/DB'.$dateString.'.zip'); //다운받게 함
}
foreach($tablez as $table){
	unlink(G5_DATA_PATH.'/db_down/'.$table.'.sql');
}

?>

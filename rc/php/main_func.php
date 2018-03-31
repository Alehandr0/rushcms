<?php

	// DB Connect:
	
		function DBConnect(){
			
			global $MySQLConn;
			
			if(!$MySQLConn){

				include $_SERVER["DOCUMENT_ROOT"].'/options.php';

				$MySQLConn = new mysqli($DBHost, $DBUser, $DBPassword, $DBName);
				$MySQLConn->query('SET NAMES "utf8"');
				$MySQLConn->query('set character_set_connection=utf8');
				$MySQLConn->query('set names utf8');
				
			}
			
			return $MySQLConn;
		
		}
		
	// RC4 encryption
	
		function rc4($str, $type, $RC4_Key = false){
			
			if($RC4_Key===false){
				
				include $_SERVER["DOCUMENT_ROOT"].'/options.php';
				
			}

			if($RC4_Key){

				if($type=="decode"){
					
					$str = mb_substr($str, 13);		
					$str = base64_decode($str);
					
				}
			
				$s = array();
				for ($i = 0; $i < 256; $i++) {
					$s[$i] = $i;
				}
				$j = 0;
				for ($i = 0; $i < 256; $i++) {
					$j = ($j + $s[$i] + ord($RC4_Key[$i % strlen($RC4_Key)])) % 256;
					$x = $s[$i];
					$s[$i] = $s[$j];
					$s[$j] = $x;
				}
				$i = 0;
				$j = 0;
				$res = '';
				for ($y = 0; $y < strlen($str); $y++) {
					$i = ($i + 1) % 256;
					$j = ($j + $s[$i]) % 256;
					$x = $s[$i];
					$s[$i] = $s[$j];
					$s[$j] = $x;
					$res .= $str[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
				}
				if($type=="encode") $res = base64_encode($res);
				
				if($type=="encode" && $res) $res = '#rc4_prefix#:'.$res;
			
			}
			else $res = $str;
			
			return $res;
			
		}
		
	// Auth functions
	
		function Auth($Options = false){
			
			global $MySQLConn;

			$RootPath = $_SERVER["DOCUMENT_ROOT"];

			session_start();
			$Auth = intval($_SESSION['auth_user_rights']);
			$UserDBID = $_SESSION['auth_user_db_id'];
			session_write_close();

			if($UserDBID && file_exists($RootPath.'/rc/_tmp/.usrblf__'.$UserDBID)){

				Logout();
				
				unlink($RootPath.'/rc/_tmp/.usrblf__'.$UserDBID);
				
				return false;

			}
			else{
				if($Auth<1 || !$Auth || !$UserDBID){
					
					$CookText = trim($_COOKIE["user_auth"]);

					if($CookText){

						$CookTextArray = explode(':', $CookText);
						
						$UserId = intval($CookTextArray[0]);
						
						if($UserId){
							
							if(!$MySQLConn) $MySQLConn = DBConnect();

							$result = $MySQLConn->query("SELECT id, tree_parent_id, user_pass, user_login, tree_cd FROM col_users WHERE user_cook_text='$CookText' LIMIT 0,1");
							$row = $result->fetch_assoc();
							mysqli_free_result($result);
							
							$TreeParentId = intval($row['tree_parent_id']);

							$RealCookText = $row['id'].':'.md5(md5($row['user_login']).md5($row['tree_cd']).$_SERVER['HTTP_USER_AGENT'].GetUserIp());

							if($row['id'] && $CookText==$RealCookText){
								
								$result = $MySQLConn->query("SELECT user_rights FROM col_users WHERE id='$TreeParentId' LIMIT 0,1");
								$row_group = $result->fetch_assoc();
								mysqli_free_result($result);

								$UserDBID = $row['id'];
								$Auth = intval($row_group['user_rights']);
								
								session_start();
								$_SESSION['auth_user_db_id'] = $UserDBID;
								$_SESSION['auth_user_rights'] = $Auth;
								session_write_close();

							}
							else SetCookie("user_auth", '', time()-31536000, "/");

						}
					
					}
					
				}
				
				if(!$Auth) $Auth = 0;
				
				$RealAuth = $Auth;
				$FakeAuth = intval($_COOKIE["user_fake_auth"]);
				
				if($FakeAuth>2 && $Auth==2) $Auth = $FakeAuth;

				if($Options=='full_info') return array('auth' => $Auth, 'real_auth' => $RealAuth, 'user_id' => $UserDBID);
				else return $Auth;
				
			}
			
		}

		function Login($Login, $Pass){

			$RootPath = $_SERVER["DOCUMENT_ROOT"];
			
			global $MySQLConn, $Auth;

			if(!$MySQLConn) $MySQLConn = DBConnect();
			
			$LoginResult = false;

			$LoginRC4 = rc4($Login, 'encode');
			$PassHASH = password_hash($Pass, PASSWORD_DEFAULT);
			
			$result = $MySQLConn->query("SELECT id, tree_parent_id, user_pass, tree_cd FROM col_users WHERE user_login='$LoginRC4' AND tree_is_folder='0' LIMIT 0,1");
			$row = $result->fetch_assoc();
			mysqli_free_result($result);

			if(password_verify($Pass, $row['user_pass'])){
				
				$UserDBID = $row['id'];
				$TreeParentId = intval($row['tree_parent_id']);
				
				$result = $MySQLConn->query("SELECT user_rights FROM col_users WHERE id='$TreeParentId' LIMIT 0,1");
				$row_group = $result->fetch_assoc();
				mysqli_free_result($result);
				
				$Auth = intval($row_group['user_rights']);
				
				session_start();
				$_SESSION['auth_user_db_id'] = $UserDBID;
				$_SESSION['auth_user_rights'] = $Auth;
				session_write_close();
				
				$CookText = $UserDBID.':'.md5(md5($LoginRC4).md5($row['tree_cd']).$_SERVER['HTTP_USER_AGENT'].GetUserIp());
				
				$result = $MySQLConn->query("UPDATE col_users SET user_cook_text='$CookText' WHERE id='$UserDBID'");
				
				SetCookie("user_auth", $CookText, time()+31536000, "/");
				
				$LoginResult = true;
				
			}

			return $LoginResult;
			
		}
		
		function Logout(){
			
			global $Auth;

			session_start();
			unset($_SESSION['auth_user_db_id']);
			unset($_SESSION['auth_user_rights']);
			session_write_close();

			SetCookie("user_auth", "", time()-3600, "/");
			SetCookie("user_fake_auth", "", time()-3600, "/");
			
			$Auth = 0;

			return true;
			
		}
		
		function GetUserIp(){
		
			$IPAddress = false;
			
			if ($_SERVER['HTTP_CLIENT_IP'])	$IPAddress = $_SERVER['HTTP_CLIENT_IP'];
			else if($_SERVER['HTTP_X_FORWARDED_FOR']) $IPAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			else if($_SERVER['HTTP_X_FORWARDED']) $IPAddress = $_SERVER['HTTP_X_FORWARDED'];
			else if($_SERVER['HTTP_FORWARDED_FOR']) $IPAddress = $_SERVER['HTTP_FORWARDED_FOR'];
			else if($_SERVER['HTTP_FORWARDED'])	$IPAddress = $_SERVER['HTTP_FORWARDED'];
			else if($_SERVER['REMOTE_ADDR']) $IPAddress = $_SERVER['REMOTE_ADDR'];
				
			return $IPAddress;
			
		}
	
	//	Other
	
		function DelCache($Dir = false){
			
			// Удаление кэша
			
			if(!$Dir) $Dir = $_SERVER["DOCUMENT_ROOT"].'/rc/_cache/';

			if (is_dir($Dir)){

				$FilesAndFolders = scandir($Dir);
				
				foreach ($FilesAndFolders as $Item){
					if ($Item != "." && $Item != ".." && $Item != ".gitkeep"){
						if (is_dir($Dir."/".$Item)) DelCache($Dir."/".$Item);
						else unlink($Dir."/".$Item);
					} 
				}
				
			}
			
		}
	
		function RemoveDir($Dir, $DelTargetFolder = false){
			
			// Функция удаления каталога со всем содержимым. Если $DelTargetFolder = false, 
			// то каталог указанный в $Dir будет очищен от содержимого, но сам не будет удален 
			
			if(!$DelTargetFolder) $TargetFolder = $Dir;

			if (is_dir($Dir)){
				
				$FilesAndFolders = scandir($Dir);
				
				foreach ($FilesAndFolders as $Item){
					if ($Item != "." && $Item != ".."){
						if (is_dir($Dir."/".$Item)) RemoveDir($Dir."/".$Item, true);
						else unlink($Dir."/".$Item);
					} 
				}
				if($Dir!=$TargetFolder) rmdir($Dir);
				
			}
			
		}
		
		function ClearTmpDir(){
			
			$RootPath = $_SERVER["DOCUMENT_ROOT"];
			
			$TmpDirPath = $RootPath.'/rc/_tmp/';
			$DelTime = time() - 86400;
			$BlackListDelTime = time() - 3600;
			
			$FilesAndFolders = scandir($TmpDirPath);

			if(is_array($FilesAndFolders)){
			
				foreach ($FilesAndFolders as $Item){
					
					if ($Item != "." && $Item != ".." && $Item != ".gitkeep"){
						
						$FileTime = filemtime($TmpDirPath."/".$Item);
						
						if($FileTime < $DelTime){
						
							if (is_dir($TmpDirPath."/".$Item)) RemoveDir($TmpDirPath."/".$Item, true);
							else unlink($TmpDirPath."/".$Item);
						
						}
						else if(!is_dir($TmpDirPath."/".$Item) && mb_substr($Item, 0, 9)=='.usrblf__' && $FileTime < $BlackListDelTime) unlink($TmpDirPath."/".$Item);

					} 
					
				}
			
			}
			
		}
		
		function LogDumpData($Data, $LogKey = false){
			
			// Функция для логирования данных в php-виде
			
			$RootPath = $_SERVER["DOCUMENT_ROOT"];
			
			$LogFileName = $RootPath.'/log_data.php';
			
			if($LogKey){
			
				if(file_exists($LogFileName)) include $LogFileName;
				else $LogData = array();
				
				if($LogData[$LogKey]) $LogKey .= '_'.round(microtime(true)*1000).'_'.mt_rand(1,9999);
				
				$LogData[$LogKey] = $Data;
			
			}
			else $LogData['log_data'] = $Data;
			
			file_put_contents($LogFileName, '<?php $LogData='.var_export($LogData, true).';?>');
			
		}
		
		function LogDumpDataClear(){

			$LogFileName = $_SERVER["DOCUMENT_ROOT"].'/log_data.php';
			
			file_put_contents($LogFileName, '<?php $LogData='.var_export(array(), true).';?>');
			
		}
		
		function GetParamsArrFromStr($ParamsStr){
			
			// Возвращает массив параметров из строки вида "[параметр_1]", "[параметр_2]", "[параметр_3]"
			
			$Params = array();
			$ParamsStr = trim($ParamsStr);

			if(mb_strlen($ParamsStr) > 2){
			
				$ParamsStr = mb_substr($ParamsStr, 1, mb_strlen($ParamsStr)-2);
				
				$Params = preg_split('/\"\s*,\s*\"/', $ParamsStr);

			}
			
			return $Params;

		}

		function ChangeRC4Key($DataArr, $OldKey, $NewKey){
			
			// Перешифровывает значения в указанных в $DataArr таблицах и полях БД
			/*
			
				$DataArr = array(
				
					'table_name_1' => array('encrypted_column_1', 'encrypted_column_2'),
					'table_name_2' => array('encrypted_column_3', 'encrypted_column_4'),

				);
				
				Перешифрует поля encrypted_column_1 и encrypted_column_2 в таблице table_name_1
				и поля encrypted_column_3 и encrypted_column_4 в таблице table_name_2
				
				Перед использованием ф-и не забудьте сделать бэкап!
			
			*/

			foreach($DataArr as $TableName => $ColumnsList){
			
				if(!$MySQLConn) $MySQLConn = DBConnect();
				
				foreach($ColumnsList as $Key => $ColumnName){
					
					$SQLColList .= ", $ColumnName";
					
				}
				
				$SQLUpdIdPart = '';
				$SQLUpdWhenPartArr = array();

				$result = $MySQLConn->query("SELECT id $SQLColList FROM $TableName");
				while($row = $result->fetch_assoc()){
					
					$Id = $row['id'];
					
					if($SQLUpdIdPart) $SQLUpdIdPart .= ", $Id";
					else $SQLUpdIdPart = "$Id";
				
					foreach($row as $ColumnKey => $Value){
					
						if($ColumnKey!='id'){
					
							$NewValue = rc4(rc4($Value, 'decode', $OldKey), 'encode', $NewKey);
							
							$SQLUpdWhenPartArr[$ColumnKey] .= " WHEN $Id THEN '$NewValue'";
						
						}
						
					}
					
				}
				mysqli_free_result($result);

				foreach($SQLUpdWhenPartArr as $ColumnKey => $SQLUpdWhenPart){
					
					$result = $MySQLConn->query("UPDATE $TableName SET $ColumnKey = CASE id $SQLUpdWhenPart ELSE $ColumnKey END WHERE id IN ($SQLUpdIdPart)");

				}
			
			}
			
		}
		
?>
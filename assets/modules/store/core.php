<?php
//@ini_set("display_errors","0");
//error_reporting(0);

if( ! defined('IN_MANAGER_MODE') || IN_MANAGER_MODE !== true || ! $modx->hasPermission('exec_module')) {
    die('<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the EVO Content Manager instead of accessing this file directly.');
}

$version = "1.0.0";
$Store = new Store;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch($action){
case 'saveuser':
	$_SESSION['STORE_USER'] = $modx->db->escape($_POST['res'] ?? '');
	break;

case 'exituser':
	$_SESSION['STORE_USER'] = '';
	break;

case 'install':
case 'install_file':
	if (is_dir(MODX_BASE_PATH . 'assets/cache/store/')) $Store->removeFolder(MODX_BASE_PATH . 'assets/cache/store/');
	$id = (int)($_REQUEST['cid'] ?? 0);
	@mkdir("../assets/cache/store", 0777);
	@mkdir("../assets/cache/store/tmp_install", 0777);
	@mkdir("../assets/cache/store/install", 0777);

	if($action == 'install') {
		$file = empty($_POST['file']) ? ($_GET['file'] ?? '') : $_POST['file'];
		if ($file!='%url%' && $file!='' && $file!=' '){
			$url = $file;
		} else {
			$Store->errors[] = 'URL пакета не передан (file is empty)';
			$Store->quit();
		}

		if (!$Store->downloadFile($url, MODX_BASE_PATH."assets/cache/store/temp.zip")){
			$Store->quit();
		}
	} else {
		$extension = pathinfo($_FILES['install_file']['name'] ?? '', PATHINFO_EXTENSION);
		if( !in_array($extension, array('zip'))) {
			die('Only ZIP-Files allowed');
		}
		if (!move_uploaded_file($_FILES['install_file']['tmp_name'], MODX_BASE_PATH."assets/cache/store/temp.zip")) {
			die('Uploaded File could not be moved to assets/cache/store/');
		}
		$msg = $Store->lang['install_file_success'];
	}

	$zip = new ZipArchive;
	$res = $zip->open(MODX_BASE_PATH."assets/cache/store/temp.zip");
	if ($res === TRUE) {
		$zip->extractTo(MODX_BASE_PATH."assets/cache/store/tmp_install");
		$zip->close();

		// Ищем распакованную папку верхнего уровня
		$dir = '';
		$handle = opendir('../assets/cache/store/tmp_install');
		if ($handle) {
			while (false !== ($name = readdir($handle))) {
				if ($name != "." && $name != "..") $dir = $name;
			}
			closedir($handle);
		}

		if ($dir === '') {
			$Store->errors[] = 'Пустой архив';
			$Store->quit();
		}

		$Store->copyFolder('../assets/cache/store/tmp_install/'.$dir, '../assets/cache/store/install');
		$Store->removeFolder('../assets/cache/store/tmp_install/install/');

		$Store->copyFolder('../assets/cache/store/tmp_install/'.$dir, '../');
		$Store->removeFolder('../install/');
		$Store->removeFolder('../assets/cache/store/tmp_install/');

		if (isset($_GET['method']) && $_GET['method'] == 'fast'){
			chdir('../assets/modules/store/installer/');
			ob_start();
			require "instprocessor-fast.php";
			$content = ob_get_contents();
			ob_end_clean();
		} else {
			header("Location: ".$modx->config['site_url']."assets/modules/store/installer/index.php?action=options");
			die();
		}
	}

	$Store->removeFolder(MODX_BASE_PATH.'assets/cache/store/');
	if($action == 'install') {
		die('[{"result":"true"}]');
	} else {
		die($msg);
	}

	break;

default:
	//prepare list of snippets
	$types = array('snippets','plugins','modules');

	foreach($types as $value){
		$result=$modx->db->query('SELECT name,description FROM '.$modx->db->config['table_prefix'].'site_'.$value);
		while($row = $modx->db->GetRow($result)) {
			$PACK[$value][$row['name']] = '1';
		}
		$PACK[$value.'_writable']  = is_writable(MODX_BASE_PATH.'assets/'.$value);
	}

	$Store->lang['user_email'] = $_SESSION['mgrEmail'] ?? '';
	$Store->lang['hash'] = isset($_SESSION['STORE_USER']) ? stripslashes( $_SESSION['STORE_USER'] ) : '';
	$Store->lang['lang'] = $Store->language;
	$Store->lang['_type'] = json_encode($PACK);
	$Store->lang['v'] = $version;
	if ($modx->config['manager_theme_mode'] == 4 ){
		$Store->lang['body_class_name'] = 'darkness';
	}
	$tpl = Store::parse( $Store->tpl(dirname( __FILE__ ).'/template/main.html') ,$modx->config ) ;
	$tpl = Store::parse( $tpl ,$Store->lang ) ;
	echo $tpl;
	break;
}


class Store{
	public $lang;
	public $language;
	public $errors = [];

	function __construct(){
		global $modx;
		$lang = $modx->config['manager_language'];
		if (file_exists( dirname(__FILE__) .  '/lang/'.$lang.'.php')){
			include_once(dirname(__FILE__) .  '/lang/'.$lang.'.php');
		} else {
			include_once(dirname(__FILE__) .  '/lang/english.php');
		}
		$this->lang = $_Lang;
		$this->language = substr($lang,0,2);
	}

	function quit(){
		die('[{"result":"false","error":"'.implode(' \r\n ', $this->errors ).'"}]');
	}

	function get_version($text){
		if (preg_match('/<strong>(.*?)<\/strong>/s', $text, $match)) {
			return trim($match[1]);
		}
		if (preg_match('/^\s*([\d]+\.[\d]+(?:\.[\d]+)?)/', $text, $match)) {
			return $match[1];
		}
		return '';
	}

	static function parse($tpl,$field){
		global $modx;
		foreach($field as $key=>$value)  $tpl = str_replace('[+'.$key.'+]',$value,$tpl);
		$evtOut = $modx->invokeEvent('OnManagerMainFrameHeaderHTMLBlock');
		$onManagerMainFrameHeaderHTMLBlock = is_array($evtOut) ? implode("\n", $evtOut) : '';
		$tpl = str_replace('[+onManagerMainFrameHeaderHTMLBlock+]',$onManagerMainFrameHeaderHTMLBlock,$tpl);
		return $tpl;
	}

	function tpl($file){
		$lang = $this->lang;
		ob_start();
		include($file);
		$tpl = ob_get_contents();
		ob_end_clean();
		return $tpl;
	}

	public function downloadFile ($url, $path) {
		$newfname = $path;
		try {
			if (ini_get('allow_url_fopen') == true) {
				$file = @fopen($url, "rb");
				if (! $file) {
					throw new \Exception("Could not open the remote file!");
				}
				$newf = @fopen($newfname, "wb");
				if (!$newf) {
					fclose($file);
					throw new \Exception("Could not open local file for writing!");
				}
				while(!feof($file)) {
					fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
				}
				fclose($file);
				fclose($newf);
				return true;
			} else if (function_exists('curl_init')) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				$content = curl_exec($ch);
				curl_close($ch);
				file_put_contents($newfname, $content);
				return true;
			} else {
				$this->errors[] = 'Download error: no fopen and no curl available';
				return false;
			}
		} catch(\Exception $e) {
			$this->errors[] = 'Error:Download: ' . $e->getMessage();
			return false;
		}
	}

	public function removeFolder($path){
		$dir = realpath($path);
		if ( !is_dir($dir)) return;
		$it = new RecursiveDirectoryIterator($dir);
		$files = new RecursiveIteratorIterator($it,
		RecursiveIteratorIterator::CHILD_FIRST);
		foreach($files as $file) {
			if ($file->getFilename() === '.' || $file->getFilename() === '..') {
				continue;
			}
			if ($file->isDir()){
				rmdir($file->getRealPath());
			} else {
				unlink($file->getRealPath());
			}
		}
		rmdir($dir);
	}

	public static function copyFolder($src, $dest) {
		$path = realpath($src);
		$dest = realpath($dest);
		if (!is_dir($path) || !is_dir($dest)) return;
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
		foreach($objects as $name => $object)
		{
			$startsAt = substr(dirname($name), strlen($path));
			self::mkDir($dest.$startsAt);
			if ( $object->isDir() ) {
				@self::mkDir($dest.substr($name, strlen($path)));
			}

			if(is_writable($dest.$startsAt) and $object->isFile())
			{
				copy((string)$name, $dest.$startsAt.DIRECTORY_SEPARATOR.basename($name));
			}
		}
	}

	private static function mkDir($folder, $perm=0777) {
		if(!is_dir($folder)) {
			mkdir($folder, $perm);
		}
	}
}
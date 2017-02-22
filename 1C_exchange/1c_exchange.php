<?php
/**
 * 1C:Exchange
 * @version 1.3
 * Date 23.02.2017
 * DELTA
 * http://delta-ltd.ru/
 * Russia, Rostov-na-Donu
 *
 *
 *
 *
 *
 *
 */

define('MODX_API_MODE', true);
include_once(dirname(dirname(__FILE__)).'/index.php');

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 'on');


$logs= fopen('1c_exchange_logs', 'a');


$session_name= session_name();
$session_id= session_id();
if( ! isset($_SESSION['1c']['time'])) $_SESSION['1c']['time']= date('Y-m-d-H-i-s');
$session_time= $_SESSION['1c']['time'];
if( ! isset($_SESSION['1c']['date'])) $_SESSION['1c']['date']= date('Y-m').'/';
$session_date= $_SESSION['1c']['date'];

// $_SESSION['1c']= array();
if( ! $_SESSION['1c']['step']) $_SESSION['1c']['step']= 1;
// $_SESSION['1c']['xml']['2016-10-12-10-19-12__import.xml']['type']= 'import';
// $_SESSION['1c']['xml']['2016-10-12-10-19-12__offers.xml']['filetype']= 'offers';


/*
	ЭТАПЫ
	1. Начало - получение данных из 1С
	2. Выгрузка групп
	3. Выгрузка товаров
 */


$dir_root  = MODX_BASE_PATH;
$dir_1c    = '1c_exchange/box/';
$dir_files = 'assets/images/1c/';

$maxtime= 10;


tolog('go:: '.$session_id);


if($_GET['type']=='catalog')
{
	if($_GET['mode']=='checkauth' && $_SESSION['1c']['step']==1)
	{
		tolog('mode:: checkauth');
		$ret .= "success\n";
		$ret .= $session_name."\n";
		$ret .= $session_id."\n";

	}elseif($_GET['mode']=='init' && $_SESSION['1c']['step']==1){
		tolog('mode:: init');
		$ret .= "zip=".(1024*1024*5)."\n";
		$ret .= "file_limit=no\n";



	}elseif($_GET['mode']=='file' && isset($_GET['filename']) && $_SESSION['1c']['step']==1){
		tolog('mode:: file:: '.$_GET['filename']);
		if( ! file_exists($dir_root.$dir_1c.$session_date)) mkdir($dir_root.$dir_1c.$session_date, 0777, true);
		if( ! file_exists($dir_root.$dir_files)) mkdir($dir_root.$dir_files, 0777, true);

		$filename= $_GET['filename'];
		$ext= substr($filename,strrpos($filename,'.'));
		if($ext=='.xml')
		{
			tolog('mode:: file:: '.$ext);
			$file= $session_date.$session_time.'_'.$filename;
			$file_path= $dir_root.$dir_1c.$file;

			if( ! $_SESSION['1c']['xml'][$file])
			{
				tolog('mode:: file:: new');
				if(file_exists($file_path)) unlink($file_path);
				if(substr($file, -11)=='_import.xml') $filetype= 'import';
				elseif(substr($file, -11)=='_offers.xml') $filetype= 'offers';
				$_SESSION['1c']['xml'][$file]['type']= $filetype;
			}

		}else{
			tolog('mode:: file:: '.$ext);
			$file= md5($filename).$ext;
			$file_path= $dir_root.$dir_files.$file;

			if($_SESSION['1c']['files'][$file]!='ing')
			{
				tolog('mode:: file:: new');
				if(file_exists($file_path)) unlink($file_path);
				$_SESSION['1c']['files'][$file]= 'ing';
			}
		}

		if($file_path)
		{
			if($foo= fopen($file_path, 'ab'))
			{
				$data= file_get_contents('php://input');
				
				if($data!==false && ! empty($data))
				{
					tolog('mode:: file:: write');
					$byte= fwrite($foo, $data);
					if($byte) $ret= "success\n";

				}else $ret= "failure\n";
			}else $ret= "failure\n";
		}else $ret= "failure\n";



	}elseif(false && $_GET['mode']=='import' && $_SESSION['1c']['step']==1){
		tolog('mode:: import:: '.$_SESSION['1c']['step']);

		$microtime_start= microtime(true);
		$next= false;
		$iiii= 0;
		$kkkk= 0;

		if($_SESSION['1c']['step']==1) $_SESSION['1c']['step']= 2;

		// sleep(5);

		foreach($_SESSION['1c']['xml'] AS $file => $info)
		{
			if($_SESSION['1c']['step']<=3 && $info['type']!='import') continue;

			$xml= new SimpleXmlIterator($dir_root.$dir_1c.$file, null, true);



			if($info['type']=='import')
			{
				tolog('mode:: import:: '.$_SESSION['1c']['step']);
				$groups= $xml->{'Классификатор'}->{'Группы'}->{'Группа'};
				if($_SESSION['1c']['step']==2)
				{
					tolog('mode:: import:: groups');
					if($groups && $groups->count()) action('groups', $groups, $iiii, $kkkk, $next);
				}else $iiii= $groups->count();

				if( ! $next)
				{
					if($_SESSION['1c']['step']==2) $_SESSION['1c']['step']= 3;

					if($_SESSION['1c']['step']==3)
					{
						tolog('mode:: import:: items');
						$items= $xml->{'Каталог'}->{'Товары'}->{'Товар'};
						if($items && $items->count()) action('items', $items, $iiii, $kkkk, $next);
					}
				}

				if( ! $next && $_SESSION['1c']['step']==3) $_SESSION['1c']['step']= 4;

				$ret= "success\n";



			}elseif($info['type']=='offers'){
				tolog('mode:: import:: '.$_SESSION['1c']['step']);

				$ret= "success\n";



			}else $ret= "failure\n";

			if($next && $iiii<300)
			{
				tolog('mode:: import:: next');
				$context= stream_context_create(array('http'=>array('timeout'=>1)));
				//@file_get_contents('http://'.getenv('HTTP_HOST').'/1c_exchange/1c_exchange.php?type=catalog&mode=import&iiii='.$iiii, false, $context);
			}
		}

		print_r($_SESSION['1c']);

	}else $ret= "failure\n";
}else $ret= "failure\n";

print $ret;



function action($type, $rows, &$iiii, &$kkkk, &$next)
{
	global $maxtime, $microtime_start;

	$rows->rewind();
	while($row= $rows->current())
	{
		if(microtime(true)-$microtime_start >$maxtime || $kkkk>=100)
		{
			$next= true;
			return;
		}

		$iiii++;
		if($iiii>=$_SESSION['1c']['seek'])
		{
			$kkkk++;
			$_SESSION['1c']['seek']++;

			if($type=='groups')
			{
				print $row->{'Наименование'} ."\n";
			}

			if($type=='items')
			{
				print $row->{'Наименование'} ."\n";
			}
		}

		if($type=='groups')
		{
			$sub= $row->{'Группы'}->{'Группа'};
			if($sub && $sub->count()) action($type, $sub, $iiii, $kkkk, $next);
		}

		$rows->next();
	}
}


function tolog($log)
{
	global $logs;
	fwrite($logs, date('Y.m.d - H:i:s').' | '.$log ."\n");
}

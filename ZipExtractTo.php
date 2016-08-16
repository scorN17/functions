<?php
function zip_extract_to($zip_file, $base, &$zip, &$fileslist)
{
	for($ii=0; $ii<$zip->numFiles; $ii++)
	{
		$zip_path= $zip->getNameIndex($ii);
		$zip_path_i= iconv('CP866', 'UTF-8//TRANSLIT//IGNORE', $zip_path);
		if(substr($zip_path_i, -1) != '/')
		{
			$rassh= substr($zip_path_i, strrpos($zip_path_i, '.'));
			$filename= md5($zip_path_i) . $rassh;
			$art= substr($zip_path_i, strrpos($zip_path_i, '/')+1);
			$art= str_replace($rassh, '', $art);
			copy('zip://'.$zip_file.'#'.$zip_path, $base.'/'.$filename);
			if($rassh == '.txt')
			{
				$fileslist['txt']= $filename;
			}else{
				$fileslist['img'][$art]= array($zip_path_i, $filename, $rassh);
			}
		}
	}
}
?>

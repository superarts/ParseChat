<?php

require('/Users/leo/prj/android/script/lib/lykits.php');

//	download
/*
for ($i = 1; $i <= 555; $i++)
{
	$cmd = "wget 'http://sex.guokr.com/experience/?page=$i' list$i.html";
	echo "$cmd\n";
	system($cmd);
}
 */

//	parse
$map = array();
$map['title'] = '-div-0-div-0-a-_value';
$map['url'] = '-div-0-div-0-a-@attributes-href';
$map['author'] = '-div-1-div-0-div-a-_value';
$map['reply'] = '-div-0-div-1-_value';
$map['date'] = '-div-1-div-1-_value';
$path = "root-html-1-body-div-0-div-0-section-1-ul-0-li";
$fp = fopen('list.txt', 'a');
for ($i = 1; $i <= 555; $i++)
{
	$filename = "lists/index.html?page=$i";
	$doc = file_to_doc($filename);
	$array = doc_to_array($doc);
	$array = array_parse($array, $path, $map);
	//print_r(array_parse($array, $path, $map));
	//print_r($array);
	foreach ($array as $post)
	{
		$title	= $post['title'];
		$url	= $post['url'];
		$author	= $post['author'];

		$title = str_replace("  ", '', $title);
		$title = str_replace("\n", '', $title);
		$title = str_replace("\r", '', $title);
		$title = str_replace("神马", '什么', $title);
		$title = str_replace("女票", '女朋友', $title);
		$title = str_replace("男票", '男朋友', $title);
		//$title = str_replace("JJ", '阴茎', $title);

		if (strpos($title, "\r") !== false)
		{
			echo "Warning r: $title\n";
			die;
		}
		else if (strpos($title, "\n") !== false)
		{
			echo "Warning n: $title\n";
			die;
		}
		else if (strpos($url, "http://sex.guokr.com") === false)
		{
			echo "Warning url: $url\n";
			die;
		}
		else if ($author == '')
		{
			echo "Warning empty author: $title\n";
			$author = 'Admin';
		}

		echo "$title\n";
		fwrite($fp, $title."\n");
		fwrite($fp, $url."\n");
		fwrite($fp, $post['author']."\n");
		fwrite($fp, $post['reply']."\n");
		fwrite($fp, $post['date']."\n");
	}
}
fclose($fp);

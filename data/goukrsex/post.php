<?php

require('/Users/leo/prj/android/script/lib/lykits.php');

$array = file('list.txt');
//	print_r($array);
//	download
/*
for ($i = 0; $i < count($array) / 5; $i++)
{
	$title	= substr($array[$i * 5 + 0], 0, -1);
	$url	= substr($array[$i * 5 + 1], 0, -1);
	$author	= substr($array[$i * 5 + 2], 0, -1);
	$reply	= substr($array[$i * 5 + 3], 0, -1);
	$date	= substr($array[$i * 5 + 4], 0, -1);
	//echo "$title\n";
	if (strpos($url, 'http://sex.guokr.com/post/') === false) {
		echo "warning: $url\n";
		die;
	}
	echo "wget '$url' --output-document post$i.html\n";
}
 */

//	parse

$index = 10000;

$article = array();
$article['title']	= substr($array[$index * 5 + 0], 0, -1);
$article['url']		= substr($array[$index * 5 + 1], 0, -1);
$article['author']	= substr($array[$index * 5 + 2], 0, -1);
$article['reply']	= substr($array[$index * 5 + 3], 0, -1);
$article['date']	= substr($array[$index * 5 + 4], 0, -1);
print_r($article);

$filename = "posts/post$index.html";
$doc = file_to_doc($filename);
echo "----\n";
//print_r(doc_to_array($doc));
$array = doc_to_array($doc);
//print_array($array, 'root');

echo "\n----\n";
echo get_content_text($doc);
echo "\n----\n";
echo get_content_html($doc);
echo "\n----\n";

//	analyze data structure
$map = array();
$map['class_id'] = '-@attributes-id';
//$map['comment_id'] = '-@attributes-data-reply';
$map['author_url'] = '-div-0-figure-a-0-@attributes-href';
$map['author_avatar'] = 'div-0-figure-a-0-img-@attributes-src';
$map['author_name'] = '-div-0-figure-a-1-_value';
$map['comment_url'] = '-div-0-div-div-a-@attributes-href';
$map['date'] = '-div-0-div-div-a-_value';
$path = "root-html-1-body-div-0-div-0-section-div-2-ul-li";
$item = array_get_path($array, $path);
//print_array($item);
$items = array_parse_map($item, $map);
//print_r($items);
foreach ($items as $comment)
{
	$text = get_comment_text($doc, $comment['class_id']);
	$html = get_comment_html($doc, $comment['class_id']);
	echo "----\n$text\n";
	echo "----\n$html\n";
	print_r($comment);
}

/*
$replies = $doc->getElementsByTagName('div');
//print_r($replies);
foreach ($replies as $reply)
{
	echo $reply->className;
	print_r($reply);
}
$comments = $doc->getElementById('cmtContent');
if ($comments == null)
	echo "wow";
print_r($comments);
 */

//echo str_count_duplicate('xxxxx xxxxx xxxxxxxx ', 'x');
//echo str_remove_duplicate("xxxxx\n\n\nxx xx xxx xxx x xxx", "\n", 1);
echo "\n----\n";

function str_count_duplicate($s, $char = ' ')
{
	$max = 0;
	$count = 0;
	for ($i = 0; $i < strlen($s); $i++)
	{
		//echo "$i: $char/{$s[$i]}\n";
		if ($s[$i] == $char)
			$count++;
		else
			$count = 0;
		if ($max < $count)
			$max = $count;
	}
	return $max;
}

function str_remove_duplicate($s, $char = ' ', $limit = 1)
{
	$sub = str_repeat($char, $limit);
	$ret = $s;
	for ($i = str_count_duplicate($s, $char); $i >= $limit + 1; $i--)
	{
		$duplicate = str_repeat($char, $i);
		$ret = str_replace($duplicate, $sub, $ret);
		//echo "$i: $duplicate, $sub\n";
	}
	return $ret;
}

function get_content_text($doc)
{
	$contents = $doc->getElementById('articleContent');
	$text = $contents->textContent;
	$pos = strpos($text, "\n相关文章\n\nhttp://");
	if ($pos > 0)
		$text = substr($text, 0, $pos);
	$text = str_remove_duplicate($text, " ", 1);
	$text = str_remove_duplicate($text, "\n", 2);
	$text = trim($text);
	return $text;
}

function get_content_html($doc)
{
	$contents = $doc->getElementById('articleContent');
	$text = $doc->saveHTML($contents);
	$pos = strpos($text, "<p>相关文章\n</p>");
	if ($pos > 0)
		$text = substr($text, 0, $pos);
	return $text;
}

function get_comment_html($doc, $id)
{
	$contents = $doc->getElementById($id);
	$text = $doc->saveHTML($contents);
	$text = str_get_between($text, '<div id="cmtContent" class="cmt-content">', '</div>');
	$text = str_remove_duplicate($text, " ", 1);
	$text = str_remove_duplicate($text, "\n", 1);
	$text = trim($text);
	return $text;
}

function get_comment_text($doc, $id)
{
	$contents = $doc->getElementById($id);
	$text = $contents->childNodes->item(3)->textContent;
	$text = str_remove_duplicate($text, " ", 1);
	$text = str_remove_duplicate($text, "\n", 1);
	$text = trim($text);
	return $text;
}

?>

<?php

$index = 6508;

require('/Users/leo/prj/script/lib/lykits.php');

use Parse\ParseClient;
use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseUser;
use Parse\ParseFile;
use Parse\ParseException;

//	parse

function process_post($index)
{
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

	$post = $article;
	$path = "root-html-1-body-div-0-div-0-section-article-div-div-0-div-0-a-0-_value";
	$post['author_name'] = array_get_path($array, $path);
	$path = "root-html-1-body-div-0-div-0-section-article-div-div-0-div-0-a-0-@attributes-href";
	$post['author_url'] = array_get_path($array, $path);
	$path = "root-html-1-body-div-0-div-0-section-article-div-div-0-div-1-span-0";
	$post['date'] = array_get_path($array, $path);
	$post['reply'] = substr($article['reply'], 0, -6);
	$path = "root-html-1-body-div-0-div-0-section-article-div-div-0-figure-a-img-@attributes-src";
	$post['author_avatar'] = array_get_path($array, $path);
	print_r($post);

	$post_text = get_content_text($doc);
	$post_html = get_content_html($doc);

	echo "\n----\n";
	echo strtok(get_content_text($doc), "\n")."...\n";
	echo "\n----\n";
	echo strtok(get_content_html($doc), "\n")."...\n";
	echo "\n----\n";

	//	analyze data structure
	$map = array();
	$map['class_id'] = '-@attributes-id';
	//$map['comment_id'] = '-@attributes-data-reply';
	$map['author_url'] = '-div-0-figure-a-0-@attributes-href';
	$map['author_avatar'] = 'div-0-figure-a-0-img-@attributes-src';
	$map['author_name'] = '-div-0-figure-a-1-_value';
	//$map['comment_url'] = '-div-0-div-div-a-@attributes-href';
	$map['url'] = '-div-0-div-div-a-@attributes-href';
	$map['date'] = '-div-0-div-div-a-_value';
	$path = "root-html-1-body-div-0-div-0-section-div-2-ul-li";
	$item = array_get_path($array, $path);
	//print_array($item);
	$items = array_parse_map($item, $map);
	//print_r($items);

	parse_save_user($post);
	echo "current user: ".ParseUser::getCurrentUser()->getUsername()."\n";
	$object_room = ParseObject::create("ChatRooms");
	$object_room->set("name", $post['title']);
	$object_room->set("user", ParseUser::getCurrentUser());
	$object_room->set("liked", 0);
	$object_room->set("reported", 1);
	$hash = hash('ripemd160', $post['title']);
	$object_room->set("hash", $hash);
	$object_room = parse_save_object($object_room);//, 'title');
	//	if ($object_room)
	{
		$object_chat = ParseObject::create("Chat");
		//echo $object_room->getObjectId()."\n";
		$object_chat->set('roomId', $object_room->getObjectId());
		$object_chat->set('text', $post_text);
		$object_chat->set('html', $post_html);
		$object_chat->set('user', ParseUser::getCurrentUser());
		$object_chat->set('url', $post['url']);
		$object_chat->set('date', $post['date']);
		$object_chat->set('reply_count', $post['reply']);
		$object_chat->set('liked', 0);
		$object_chat->set('reported', 0);
		$hash = hash('ripemd160', $post_text);
		echo "post hash: $hash\n";
		$object_chat->set("hash", $hash);
		parse_save_object($object_chat);//, 'hash');

		foreach ($items as $comment)
		{
			parse_save_user($comment);
			echo "current user: ".ParseUser::getCurrentUser()->getUsername()."\n";

			print_r($comment);
			if ($comment['class_id'] == '')
			{
				continue;
			}
			$comment_text = get_comment_text($doc, $comment['class_id']);
			$comment_html = get_comment_html($doc, $comment['class_id']);
			echo "----\n".strtok($comment_text, "\n")."...\n";
			echo "----\n".strtok($comment_html, "\n")."...\n";

			$object_comment = ParseObject::create("Chat");
			$object_comment->set('roomId', $object_room->getObjectId());
			$object_comment->set('text', $comment_text);
			$object_comment->set('html', $comment_html);
			$object_comment->set('user', ParseUser::getCurrentUser());
			$object_comment->set('url', $comment['url']);
			$object_comment->set('date', $comment['date']);
			$object_comment->set('liked', 0);
			$object_comment->set('reported', 0);
			$hash = hash('ripemd160', $comment_text);
			echo "comment hash: $hash\n";
			$object_comment->set("hash", $hash);
			parse_save_object($object_comment);//, 'hash');
		}
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
}

/*
$object = ParseObject::create("TestObject");
$object->set("elephant", "go");
$object->set("today", new DateTime());
$object->setArray("mylist", [1, 2, 3]);
$object->setAssociativeArray(
  "languageTypes", array("php" => "awesome", "ruby" => "wtf")
);
parse_save_object($object, 'elephant');
parse_save_user('test002');
echo "current user: ".ParseUser::getCurrentUser()->getUsername()."\n";
 */

//echo str_count_duplicate('xxxxx xxxxx xxxxxxxx ', 'x');
//echo str_remove_duplicate("xxxxx\n\n\nxx xx xxx xxx x xxx", "\n", 1);
for ($i = $index; $i <= 16636; $i++)
{
	echo "---- PROCESSING $i\n";
	system("echo $i >> log.txt");
	process_post($i);
}
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

/**
 * return the number of objects with the same key/value
 */
function parse_count_object($object, $key)
{
	$value = $object->get($key);
	$class = $object->getClassName();

	$query = new ParseQuery($class);
	$query->equalTo($key, $value);
	//echo "count of $key: $count\n";
	$success = false;
	do {
		echo "count: $count\n";
		try {
			$count = $query->count();
			$success = true;
		} catch (ParseException $ex) {
			echo $ex->getMessage()."\n";
			if ($ex->getMessage() == 'invalid JSON')
				$success = true;
		}
	} while ($count < 0);
	return $count;
}

/**
 * return the first of objects with the same key/value
 */
function parse_first_object($object, $key)
{
	$value = $object->get($key);
	$class = $object->getClassName();

	$query = new ParseQuery($class);
	//echo "$key: $value\n";
	$query->equalTo($key, $value);
	//$query->limit(1);
	$ret = null;
	$success = false;
	do {
		echo "$key: $value\n";
		try {
			$ret = $query->first();
			$success = true;
		} catch (ParseException $ex) {
			echo $ex->getMessage()."\n";
			if ($ex->getMessage() == 'invalid JSON')
				$success = true;
		}
	} while ($success == false);
	return $ret;
}

/**
 * save object only if it's not unique
 */
function parse_save_object($object, $key = 'hash')
{
	$obj = parse_first_object($object, $key);
	//if (parse_count_object($object, $key) == 0)
	//print_r($obj);
	if ($obj == null)
	{
		$success = false;
		do {
			echo "$key: $value\n";
			try {
				$object->save();
				$success = true;
			} catch (ParseException $ex) {
				echo $ex->getMessage()."\n";
				if ($ex->getMessage() == 'invalid JSON')
					$success = true;
			}
		} while ($success == false);
		//echo "saved: ".$object->getObjectId()."\n";
		return $object;
	}
		else echo "found duplicated object\n";
	return $obj;
}

/**
 * sign up user only if the username is unique, otherwise sign him in
 */
function parse_save_user($post)
{
	$username = $post['author_name'];
	$hash = hash('ripemd160', $username);
	$email = "$hash@gmail.com";
	$password = "contrasenia";
	$user = new ParseUser();
	$user->setUsername($email);
	$user->setEmail($email);
	$user->set('fullname', $username);
	$user->set('fullname_lower', strtolower($username));
	$user->set('url', $post['author_url']);
	$user->set('image_url', $post['author_avatar']);
	$user->setPassword($password);

	$success = false;
	if (strpos($post['author_avatar'], 'http://') !== false)
	{
		echo "picture\n";
		do {
			try {
				$contents = file_get_contents($post['author_avatar']);
				$file = ParseFile::createFromData($contents, "myfile.txt");
				$file->save();
				$success = true;
				$user->set("picture", $file);
				$user->set("thumbnail", $file);
			} catch (Exception $ex) {
				echo $ex->getMessage()."\n";
				if (strpos($ex->getMessage(), 'Cannot retrieve data for unsaved ParseFile') !== false)
					$success = true;
			}
		} while ($success == false);
	}

	if (parse_count_object($user, 'username') == 0)
	{
		$success = false;
		do {
			echo "signup\n";
			try {
				$user->signUp();
				$success = true;
			} catch (ParseException $ex) {
				echo $ex->getMessage()."\n";
				if (($ex->getMessage() == 'invalid JSON') || 
					(strpos($ex->getMessage(), 'already taken') !== false) ||
					(strpos($ex->getMessage(), 'Cannot retrieve data for unsaved ParseFile') !== false)
				)
					$success = true;
			}
		} while ($success == false);
	}
	else
	{
		//echo "found duplicated user\n";
		$success = false;
		do {
			echo "signin\n";
			try {
				ParseUser::logIn($email, $password);
				$success = true;
			} catch (ParseException $ex) {
				echo $ex->getMessage()."\n";
				if ($ex->getMessage() == 'invalid JSON')
					$success = true;
			}
		} while ($success == false);
	}
}

?>

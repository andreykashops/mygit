<?php
/*
Автор набора функций andreykashops
Version: 3.0


aksurl()
timthumb()
aksatachament()

require_once 'inc/tfunctions.php';
*/




/**
 * Обрезает текст до указанной длины с указанными параметрами
 *
 * @since	3.0.0
 *	
 * @param	int|array	$count|$args = {
 *				Необязательно. Поумолчанию 100.
 *				Длинна строки (int) либо набор параметров (array) ниже.
 *				
 *				@type	int		$count		100		Длинна строки
 *				@type	string	$mymore		' ...'	Текст, заканчивающий строку
 *				@type	boolean	$symbols	true	Обрезать по символам или словам
 *				@type	boolean	$echo		true	Показать или вернуть
 *				@type	boolean	$more		false	Работать с текстом до тега <!--more-->
 *				@type	boolean	$delshort	true	Удалять шоткоды
 *				@type	boolean	$delhtml	true	Удалять HTML теги
 *				@type	boolean	$delurl		true	Удалять URL
 *				@type	boolean	$delpunct	true	Удалять знаки пунктуации вконце строки
 *				@type	boolean	$lastword	true	Удалять последние слово
 *			}
 * @param string $content   Необязательно. Текст для обрезки. 
 *							Поумолчанию контент текущей статьи
 *
 * @return string Обрезанный текст.
 */
function ttrim($args = null, $content = null){
	global $post;
	
	$defaults = array(
		'count'		=> 100,		//Длинна строки
		'mymore'	=> ' ...',	// Текст, заканчивающий строку
		'symbols'	=> true,	// Обрезать по символам или словам
		'echo'		=> true,	// Показать или вернуть
		'more'		=> false,	// Работать с текстом до тега <!--more-->
		'delshort'	=> true,	// Удалять шоткоды
		'delhtml'	=> true,	// Удалять HTML теги
		'delurl'	=> true,	// Удалять URL
		'delpunct'	=> true,	// Удалять знаки пунктуации вконце строки
		'lastword'	=> true,	// Удалять последние слово
	);
	
	if((int)$args === $args)
		$args = array( 'count' => $args );
	
	$r = wp_parse_args( $args, $defaults );

	// Текст, с которым работаем
	$out = $content ? $content : $post->post_content;
	
	// Обрезаем текст до <!--more--> или удаляем его
	if($r['more']){
		$out = preg_split('/<!--more-->/s', $post->post_content); 
		$out = $out[0];
	}else
		$out = str_replace("<!--more-->", '', $out);
	
	// Удаляем шоткоды
	$r['delshort'] && $out = preg_replace("!\[/?[^\]]+\]!U", '', $out);
	
	// Удаляем HTML теги
	$r['delhtml'] && $out = preg_replace("!\</?[^\>]+\>!U", '', $out);
	
	// Удаляем ссылки
	$r['delurl'] && $out = preg_replace("#(?<!\])\bhttp://[^\s\[<]+#i", '', $out);
	
	// Режем по символам или же по словам
	$out = 	$r['symbols']
			? mb_substr($out, 0, $r['count'])
			: wp_trim_words($out, $r['count']);

	
	// Удаляем последние слово
	if($r['lastword'] && count($out) > $r['count'])
		$out = preg_replace('@(.*)\s[^\s]*$@s', '\\1', $out); 
	
	// Удаляем лишние пробелы в начале и в конце
	$out = trim($out);
	
	// Удаляем знаки пунктуации вконце строки
	$r['delpunct'] && $out = preg_replace("#[[:punct:]]+$#is", '', $out);
	
	// Добавляем окончание
	$r['mymore'] && $out .= $r['mymore'];
	
	if(!$r['echo'])
		return $out;
	
	echo $out;
}




/**
 * Выводит на экран список переменных в textarea
 *
 * @since	1.0.0
 * @update	3.0.0
 *	
 * @params All types. Список переменных для вывода.
 *
 * @return Выводит textarea.
 */
function p(){
	$arg_list = func_get_args();
	
	echo '<textarea name="" id="" cols="30" rows="10" style="width:100%;position:relative;z-index:99999999;">';
	
	foreach($arg_list as $arg)
		echo var_export($arg), PHP_EOL . "\n";
	
	echo '</textarea>';
}





/**
 * Задает размеры миниатюр WordPress.
 *
 * @since	3.0.0
 *
 * @param array $sizes {
 *					Массив наборов размеров.
 *
 *					@type array {
 *						@type int		width	- Ширина.
 *						@type int		height	- Высота.
 *						@type boolean	crop	- Кадрировать. Не обязательно. 
 *												  По умолчанию true.
 *					}
 *				}
 *	
 * @return
 */
function register_sizes($sizes = array()) {
	
	if(!function_exists('add_image_size'))
		return false;

	if(is_array($sizes)){
		foreach($sizes as $size){
			list($w, $h, $c) = $size;
			add_image_size( $w . 'x' . $h, $w, $h, isset($c) ? $c : true );
		}
	}
}




/**
 * Отправляет SMS уведомление. Данные для отправки берутся с настроек шаблона/сайта.
 *
 * @since	3.0.0
 *
 * @return
 */
function sms_notifi($sms){
	$sms_tel	= _topt('admin_phone');
	$sms_login	= _topt('sms_login');
	$sms_pass	= _topt('sms_pass');
	
	if($sms_tel && $sms_login && $sms_pass && $sms){
		$query = 'http://smsc.ru/sys/send.php?login=' . urlencode($sms_login) . '&psw=' . $sms_pass . '&phones=' . urlencode($sms_tel) . '&mes=' . urlencode($sms) . '&translit=1&charset=utf-8';
		$return = file_get_contents($query);
		return $return;
	}
	
	return false;
}




/**
 * Добавляет в структуру ЧПУ к концу URL страниц (type page) .html
 *
 * @since	3.0.0
 *
 * @return
 */
function html_page_permalink() {
	global $wp_rewrite;
	
	if ( !strpos($wp_rewrite->get_page_permastruct(), '.html')){
		$wp_rewrite->page_structure = $wp_rewrite->page_structure . '.html';
	}
}


#!/usr/bin/env php
<?php

$tts_url = "http://translate.google.com/translate_tts";
$tts_voice = "pt-BR";

include_once "phpagi.php";
$dir = "/var/lib/asterisk/sounds/tts/";
if( !is_dir($dir) ) mkdir($dir, 0775);

$agi = new AGI();
$text = $argv[1];
$file = "google-".md5($text);
$dir_file = $dir.$file;
$tmp_file_mp3 = "/tmp/$file.mp3";
$tmp_file_wav = "/tmp/$file.wav";
$txt_file = "$dir_file.txt";

$agi->verbose("Início do Google tts AGI.");

if (!isset($text)) {
    $agi->verbose("Texto vazio :(");
    return 0;
}

if (file_exists("$dir_file.wav") || file_exists("$dir_file.sln")) {
    $agi->verbose("Arquivo $file existente.");
    $agi->stream_file($dir_file,"#");
    return 0;
}

$agi->verbose("Tentando contato com Google.");
$url = $tts_url."?ie=UTF-8&tl=".$tts_voice."&q=".urlencode($text)."&client=tw-ob";
$agi->verbose($url);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux; rv:8.0) Gecko/20100101");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
$result = curl_exec($ch);
if (curl_errno($ch)) {
    $agi->verbose("Erro: ".curl_error($ch));
    return 0;
}

$fp = fopen($tmp_file_mp3, "w");
fwrite($fp,$result);
curl_close ($ch);
fclose($fp);

if(file_exists($tmp_file_mp3)) {
    
	exec("mpg123 -q -w $tmp_file_wav $tmp_file_mp3");
	
	if (file_exists($tmp_file_wav)) {
		exec("sox --ignore-length $tmp_file_wav -q -r 8000 -c 1 $dir_file.wav");
		exec("sox --ignore-length $tmp_file_wav -q -r 8000 -t raw $dir_file.sln");
		$fp = fopen($txt_file, "w");
		fwrite($fp,$text);
		fclose($fp);
		unlink($tmp_file_wav);
		unlink($tmp_file_mp3);
	} else {
		$agi->verbose("Erro: Arquivo $tmp_file_wav não existe");
		unlink($tmp_file_mp3);
		return 0;
	}	
	
} else {
    $agi->verbose("Erro: Arquivo $tmp_file_mp3 não existe");
    return 0;
}

if (file_exists("$dir_file.wav") || file_exists("$dir_file.sln")) {
    $agi->verbose("Arquivo $file foi gerado");
    $agi->wait_for_digit(1000);
    $agi->stream_file($dir_file,"#");
} else {
    $agi->verbose("Erro: Arquivo $file não existe.");
    return 0;
}

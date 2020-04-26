#!/usr/bin/env php
<?php

/**
https://github.com/mrpbueno/asterisk-tts/blob/master/README.md#antes-de-come%C3%A7ar
**/

// API key:
$tts_apikey = "TEXT_TO_SPEECH_APIKEY";
// URL:
$tts_url = "TEXT_TO_SPEECH_URL";
// Voice:
$tts_voice = "pt-BR_IsabelaV3Voice";

include_once "phpagi.php";
$dir = "/var/lib/asterisk/sounds/tts/";
if( !is_dir($dir) ) mkdir($dir, 0775);

$agi = new AGI();
$text = $argv[1];
$file = "watson-".md5($text);
$dir_file = $dir.$file;
$tmp_file = "/tmp/$file.wav";
$txt_file = "$dir_file.txt";

$agi->verbose("Início do Watson tts AGI.");

if (!isset($text)) {
    $agi->verbose("Texto vazio :(");
    return 0;
}

if (file_exists("$dir_file.wav") || file_exists("$dir_file.sln")) {
    $agi->verbose("Arquivo $file existente.");
    $agi->stream_file($dir_file,"#");
    return 0;
}

$agi->verbose("Tentando contato com IBM Text to Speech API.");
$url = $tts_url."/v1/synthesize?voice=".$tts_voice;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"text\":\"$text\"}");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_USERPWD, "apikey" . ":" . "$tts_apikey");
$headers = [];
$headers[] = "Content-Type: application/json";
$headers[] = "Accept: audio/wav;rate=8000";
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$result = curl_exec($ch);
if (curl_errno($ch)) {
    $agi->verbose("Erro: ".curl_error($ch));
    return 0;
}

$fp = fopen($tmp_file, "w");
fwrite($fp,$result);
curl_close ($ch);
fclose($fp);

if(file_exists($tmp_file)) {
    exec("sox --ignore-length $tmp_file -q -r 8000 -c 1 $dir_file.wav");
    exec("sox --ignore-length $tmp_file -q -r 8000 -t raw $dir_file.sln");
    $fp = fopen($txt_file, "w");
    fwrite($fp,$text);
    fclose($fp);
    unlink($tmp_file);
} else {
    $agi->verbose("Erro: Arquivo $tmp_file não existe");
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

<?php
/**
 * 音乐流式代理
 * GET ?id=歌曲ID → 流式输出音频
 * 用本地 Meting 库解析网易云
 */
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); exit('Bad Request'); }

require_once __DIR__ . '/lib/Meting.php';
use Metowolf\Meting;

$api = new Meting('netease');
$data = json_decode($api->format()->url($id), true);
$song_url = $data['url'] ?? '';
if (!$song_url) { http_response_code(404); exit('Not available'); }

@ini_set('max_execution_time', '0');
@set_time_limit(0);

$ch = curl_init($song_url);
curl_setopt_array($ch, [
    CURLOPT_NOBODY => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 8,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_FOLLOWLOCATION => true,
]);
curl_exec($ch);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'audio/mpeg';
$content_length = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
curl_close($ch);

header('Content-Type: ' . $content_type);
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=86400');
header('Access-Control-Allow-Origin: *');
if ($content_length > 0) header('Content-Length: ' . $content_length);

$range_from = 0;
if (isset($_SERVER['HTTP_RANGE'])) {
    preg_match('/bytes=(\d+)-/', $_SERVER['HTTP_RANGE'], $m);
    if ($m) {
        $range_from = intval($m[1]);
        http_response_code(206);
        header('Content-Range: bytes ' . $range_from . '-' . ($content_length > 0 ? $content_length - 1 : '*') . '/' . ($content_length > 0 ? $content_length : '*'));
    }
}

$ch = curl_init($song_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_BUFFERSIZE => 65536,
    CURLOPT_WRITEFUNCTION => function($ch, $chunk) {
        echo $chunk;
        flush();
        return strlen($chunk);
    },
]);
if ($range_from > 0) curl_setopt($ch, CURLOPT_RESUME_FROM, $range_from);
curl_exec($ch);
curl_close($ch);

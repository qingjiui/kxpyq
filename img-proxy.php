<?php
/**
 * 图片缩放代理
 * 用法: img-proxy.php?url=图片地址&w=800
 * 缩放后缓存到 assets/cache/
 */

// GD 扩展检测
if (!function_exists('gd_info')) {
    // 无 GD 时直接 302 跳转到原图
    $raw_url = @$_GET['url'];
    if ($raw_url && preg_match('#^https?://#i', $raw_url)) {
        header('Location: ' . $raw_url);
    } else {
        http_response_code(500);
        echo 'GD extension not installed';
    }
    exit;
}

header('Cache-Control: public, max-age=2592000'); // 30天缓存

$url = @$_GET['url'];
$max_w = intval(@$_GET['w']) ?: 800;
$max_h = intval(@$_GET['h']) ?: 0;

if (empty($url)) { http_response_code(400); exit('Missing url'); }
if (!preg_match('#^https?://#i', $url)) { http_response_code(400); exit('Invalid url'); }

// 缓存目录
$cache_dir = __DIR__ . '/assets/cache';
if (!is_dir($cache_dir)) { @mkdir($cache_dir, 0755, true); }

// 缓存文件名
$hash = md5($url) . '_' . $max_w . 'x' . $max_h;
$ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg','jpeg','png','gif','webp','bmp'])) { $ext = 'jpg'; }
$cache_file = $cache_dir . '/' . $hash . '.' . $ext;

// 命中缓存
if (file_exists($cache_file) && filesize($cache_file) > 0) {
    $mime = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp','bmp'=>'image/bmp'];
    header('Content-Type: ' . ($mime[$ext] ?? 'image/jpeg'));
    header('Age: ' . (time() - filemtime($cache_file)));
    readfile($cache_file);
    exit;
}

// 下载原图（禁gzip）
$img_data = @file_get_contents($url, false, stream_context_create([
    'http'=>[
        'timeout'=>10,
        'user-agent'=>'Mozilla/5.0 (compatible; KXpyq/1.0)',
        'follow_location'=>true,
        'max_redirects'=>3,
        'header'=>"Accept-Encoding: identity\r\n"
    ]
]));

// 兜底：如果还是gzip了就解压
if ($img_data && strlen($img_data) > 2 && @substr($img_data, 0, 2) === "\x1f\x8b") {
    $decompressed = @gzdecode($img_data);
    if ($decompressed !== false) $img_data = $decompressed;
}

if ($img_data === false || strlen($img_data) < 100) {
    header('Location: ' . $url);
    exit;
}

// 创建图片对象
$src = @imagecreatefromstring($img_data);
if (!$src) {
    header('Content-Length: ' . strlen($img_data));
    echo $img_data;
    exit;
}

$orig_w = imagesx($src);
$orig_h = imagesy($src);

// ===== 修复 EXIF 方向 =====
if (function_exists('exif_read_data') && in_array($ext, ['jpg','jpeg'])) {
    $tmpfile = tempnam(sys_get_temp_dir(), 'pxy');
    file_put_contents($tmpfile, $img_data);
    $exif = @exif_read_data($tmpfile, 'IFD0');
    @unlink($tmpfile);
    $orientation = $exif['Orientation'] ?? 1;
    switch ($orientation) {
        case 2: // 水平翻转
            imageflip($src, IMG_FLIP_HORIZONTAL);
            break;
        case 3: // 旋转180
            $src = imagerotate($src, 180, 0);
            break;
        case 4: // 垂直翻转
            imageflip($src, IMG_FLIP_VERTICAL);
            break;
        case 5: // 顺时针90 + 水平翻转
            $src = imagerotate($src, 270, 0);
            imageflip($src, IMG_FLIP_HORIZONTAL);
            break;
        case 6: // 顺时针90 (最常见的竖拍)
            $src = imagerotate($src, 270, 0);
            break;
        case 7: // 逆时针90 + 水平翻转
            $src = imagerotate($src, 90, 0);
            imageflip($src, IMG_FLIP_HORIZONTAL);
            break;
        case 8: // 逆时针90
            $src = imagerotate($src, 90, 0);
            break;
    }
    // 旋转后重新获取宽高
    if (in_array($orientation, [5,6,7,8])) {
        $orig_w = imagesy($src);
        $orig_h = imagesx($src);
    }
}
// ===== EXIF 修复结束 =====

// 计算缩放尺寸
$dst_w = $orig_w;
$dst_h = $orig_h;

if ($max_w > 0 && $dst_w > $max_w) {
    $ratio = $max_w / $dst_w;
    $dst_w = $max_w;
    $dst_h = intval($orig_h * $ratio);
}
if ($max_h > 0 && $dst_h > $max_h) {
    $ratio = $max_h / $dst_h;
    $dst_h = $max_h;
    $dst_w = intval($dst_w * $ratio);
}

// 尺寸没变，直接缓存原图
if ($dst_w == $orig_w && $dst_h == $orig_h) {
    file_put_contents($cache_file, $img_data);
    $mime = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp','bmp'=>'image/bmp'];
    header('Content-Type: ' . ($mime[$ext] ?? 'image/jpeg'));
    header('Content-Length: ' . strlen($img_data));
    echo $img_data;
    imagedestroy($src);
    exit;
}

// 缩放
$dst = imagecreatetruecolor($dst_w, $dst_h);
imagealphablending($dst, false);
imagesavealpha($dst, true);
imagecopyresampled($dst, $src, 0, 0, 0, 0, $dst_w, $dst_h, $orig_w, $orig_h);

// 输出并缓存
ob_start();
switch ($ext) {
    case 'png':  header('Content-Type: image/png');  imagepng($dst, null, 6); break;
    case 'gif':  header('Content-Type: image/gif');   imagegif($dst); break;
    case 'webp': header('Content-Type: image/webp');  imagewebp($dst, null, 80); break;
    default:     header('Content-Type: image/jpeg');   imagejpeg($dst, null, 85); break;
}
$img_out = ob_get_clean();

@file_put_contents($cache_file, $img_out);
header('Content-Length: ' . strlen($img_out));
echo $img_out;

imagedestroy($src);
imagedestroy($dst);

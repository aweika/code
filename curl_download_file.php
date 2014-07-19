/**
 * CURL 下载文件
 * @param unknown $file_url 文件URL地址
 * @return string 文件路径 eg:/uploads/image/20121212/xxx.jpg
 */
function curl_download_file($file_url) {
    $url = $file_url;
    $curl = curl_init($url);
    $file_path = '/uploads/image/'.date("YmdHis") . '_' . rand(10000, 99999) . get_image_extension($url);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($curl, CURLOPT_MAXREDIRS, 7);
    curl_setopt ($curl, CURLOPT_HEADER, false);
    curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, 20);
    $imageData = curl_exec($curl);
    curl_close($curl);
    $tp = fopen($file_path, 'w');
    fwrite($tp, $imageData);
    fclose($tp);

    return $file_path;
}


/**
 * 获取图片后缀名，带.
 * @param unknown $img_path 图片绝对路径
 * @return string 文件扩展名 eg: .jpg
 */
function get_image_extension($img_path) {
    // 获取文件后缀
    $type_array = explode('.', $img_path);
    $num = count($type_array);
    return '.'.$type_array[$num-1]; //返回文件后缀
}
<?php
header("Content-type: text/html; charset=utf-8");


function get_taobao_item_id($url){

    // 解析url
    $urls=parse_url( $url );

    // 提取url，得到商品id
    // 一淘商品
    if ((strpos(@$urls['host'], 'etao.com') > 0) && (strpos(@$urls['host'], 'detail.etao.com')==0)) {
        $url_array = explode('.',$urls['path']);
        $id = ltrim($url_array[0],'/');

    // 淘宝商品
    } else if ((strpos(@$urls['host'], 'taobao.com') > 0) && (strpos(@$urls['host'], 'item.taobao.com')==0)) {
        parse_str($urls['query'],$url_array);
        $id = @$url_array['id'] ? $url_array['id'] : "" ;
    // 天猫商品
    } else if ((strpos(@$urls['host'], 'tmall.com') > 0 ) && (strpos(@$urls['host'], 'detail.tmall.com')==0)) {
        parse_str($urls['query'],$url_array);
        $id = @$url_array['id'] ? $url_array['id'] : "" ;
    // 非法商品
    }else{
        return false;
    }

    return $id;
}


/**
 * 下载淘宝页面
 * @param url $url
 * @return string
 */
function download_page($url) {
    // 创建一个新cURL资源
    $ch = curl_init();
    // 设置URL和相应的选项
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_REFERER, "http://www.taobao.com/");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    // 抓取URL并把它传递给浏览器
    $output = curl_exec($ch);
    // 关闭cURL资源，并且释放系统资源
    curl_close($ch);

    return $output;
}


/**
 * 获取淘宝评论URL
 * @param unknown $id
 * @return string
 */
function get_taobao_comment_url($id){

    $pages = download_page('http://item.taobao.com/item.htm?id='.$id);

    preg_match_all('/data-listApi="(.*)"/Usi', $pages, $commont);//评论列表地址

    $comment_list_url = $commont[1][0].'&currentPageNum=1&rateType=&orderType=sort_weight&showContent=1&attribute=&callback=?';

    return $comment_list_url;
}


/**
 * 获取天猫评论URL
 * @param unknown $id
 * @return string
 */
function get_tmall_comment_url($id){
    $comment_list_url = 'http://rate.tmall.com/list_detail_rate.htm?itemId='.$id.'&spuId=&currentPage=1&sellerId=&order=0&forShop=1&callback=?';

    return $comment_list_url;
}


/**
 * 根据商品URL获取评论
 * @param string $url 淘宝或天猫商品链接地址
 * @param int $maxPage 采集最大页数，设置为NULL则获取全部评论
 * @return multitype:multitype:NULL unknown  multitype:unknown
 */
function get_comment_list($url, $maxPage=5) {

    // 数据集
    $data = array();

    // 评论总页数，初始共一页，程序自动获取
    $lastPage = 1;

    if (strpos($url, 'tmall.com') === false) {
        // 淘宝
        $id = get_taobao_item_id($url);
        $url = get_taobao_comment_url($id);

        for ($i=1; $i<=$lastPage; $i++) {

            $url = preg_replace('/PageNum=(\d+)&/','PageNum='.$i.'&', $url);

            //抓取评论
            $product_comment_list = download_page($url);
            $product_comment_list = iconv('gbk','utf-8//IGNORE', $product_comment_list);
            preg_match('#\((.*?)\)#U', $product_comment_list, $comment_list_matchs);
            $content = json_decode($comment_list_matchs[1], true);

            $lastPage = $content['maxPage'];

            // 重组数据
            foreach ($content['comments'] as $item) {
                $rate = array();
                $rate['username'] = $item['user']['nick'];
                $rate['content'] = $item['content'];
                $rate['sku'] = $item['auction']['sku'];
                $rate['datetime'] = $item['date'];
                $data[] = $rate;
            }

            // 超出指定页，退出。防止获取全部评论
            if ($maxPage && ($i >= $maxPage)) {
                break;
            }
        }

    } else {
        // 天猫
        $id = get_taobao_item_id($url);
        $url = get_tmall_comment_url($id);

        for ($i=1; $i<=$lastPage; $i++) {

            $url = preg_replace('/currentPage=(\d+)&/','currentPage='.$i.'&', $url);

            //抓取评论
            $product_comment_list = download_page($url);

            $product_comment_list = iconv('gbk','utf-8//IGNORE', $product_comment_list);
            preg_match('#\((.*?)\)#U', $product_comment_list, $comment_list_matchs);
            $content = json_decode($comment_list_matchs[1], true);

            $content = $content['rateDetail'];

            $lastPage = $content['paginator']['lastPage'];

            foreach ($content['rateList'] as $item) {
                $rate = array();
                $rate['username'] = $item['displayUserNick'];
                $rate['content'] = $item['rateContent'];
                $rate['sku'] = $item['auctionSku'];
                $rate['datetime'] = $item['rateDate'];
                $data[] = $rate;
            }

            // 超出指定页，退出。防止获取全部评论
            if ($maxPage && ($i >= $maxPage)) {
                break;
            }
        }
    }

    return $data;
}


// 获取淘宝评论测试
$url = 'http://item.taobao.com/item.htm?id=14150972514';
$data = get_comment_list($url);
var_dump($data);

// 获取天猫评论测试
$url = 'http://detail.tmall.com/item.htm?id=39969082707';
$data = get_comment_list($url);
var_dump($data);
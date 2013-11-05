<?php
/**
 * 根据自增ID生成商品短链接代码
 */
if (!function_exists('get_short_url')) {
	function get_short_url($value) {
		// 字符库
		$key = "TvFy8Bx9ApDK5mLi3NgPwQkR6tUf0VnCoWlMaXrZb2E1cIeGhHd4JjYq7sOuSz";
		// 生成短字符串长度
		$num = 7;
		$base = strlen( $key );
		$arr = array();
		while( $value != 0 ) {
			$arr[] = $value % $base;
			$value = floor( $value / $base );
		}
		$result = "";
		while( isset($arr[0]) ) $result .= substr($key, array_pop($arr), 1 );

		while (strlen($result) < $num) {
			$result .= $key[(mt_rand(0, 61))];
		}

		return $result;
	}
}
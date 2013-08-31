<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Redis操作类
 *
 * @package 		library
 * @author 	    	王玉鹏 <wangyp129@gmail.com>
 * @version 		V1.0
 * @copyright 		Copyright (c) 2013, aituan.com
 * @modifier		王玉鹏 <wangyp129@gmail.com>
 * @lastmodifide	2013-8-29 下午2:04:35
 */

class AI_redis {

	private $_ci ; //CI super

	private $logger; //日志对象

	private $redis; //redis对象

	/**
	 * 初始化Redis
	 * $config = array(
	 *  'server' => '127.0.0.1' 服务器
	 *  'port'   => '6379' 端口号
	 * )
	 * @param array $config
	 */
	public function init($config = array()) {
		$this->_ci =& get_instance();
		$this->_ci->load->config('redis');

		$this->logger = Logger::getLogger(__CLASS__);

		if (!isset($config['server']))  $config['server'] = $this->_ci->config->item('redis_host');
		if (!isset($config['port']))  $config['port'] = $this->_ci->config->item('redis_port');

		$this->redis = new Redis();
		$this->redis->connect($config['server'], $config['port']);

		$this->logger->debug("connect: 'server' => '{$config['server']}', 'port' => '{$config['port']}', Result: Success'");
		return $this->redis;
	}

	/**
	 * 设置值
	 * @param string $key KEY名称
	 * @param string|array $value 获取得到的数据
	 * @param int $timeOut 时间
	 */
	public function set($key, $value, $timeOut = 0) {
		$value = json_encode($value);
		$retRes = $this->redis->set($key, $value);
		if ($timeOut > 0) $this->redis->setTimeout($key, $timeOut);

		$this->logger->debug("set: 'key' => '{$key}', 'value' => '{$value}', 'time' => {$timeOut}, Result: Success'");
		return $retRes;
	}

	/**
	 * 通过KEY获取数据
	 * @param string $key KEY名称
	 */
	public function get($key) {
		$result = $this->redis->get($key);

		$this->logger->debug("get: 'key' => '{$key}', 'value' => '{$result}', Result: Success'");
		return json_decode($result, TRUE);
	}

	/**
	 * 删除一条数据
	 * @param string $key KEY名称
	 */
	public function delete($key) {

		$this->logger->debug("delete: 'key' => '{$key}', Result: Success'");
		return $this->redis->delete($key);
	}

	/**
	 * 清空数据
	 */
	public function flushAll() {
		$this->logger->debug("flushAll:  Result: Success'");
		return $this->redis->flushAll();
	}

	/**
	 * 数据入队列
	 * @param string $key KEY名称
	 * @param string|array $value 获取得到的数据
	 * @param bool $right 是否从右边开始入
	 */
	public function push($key, $value ,$right = true) {
		$value = json_encode($value);

		$this->logger->debug("push: 'key' => '{$key}', 'value' => '{$value}', Result: Success'");
		return $right ? $this->redis->rPush($key, $value) : $this->redis->lPush($key, $value);
	}

	/**
	 * 数据出队列
	 * @param string $key KEY名称
	 * @param bool $left 是否从左边开始出数据
	 */
	public function pop($key , $left = true) {
		$val = $left ? $this->redis->lPop($key) : $this->redis->rPop($key);

		$this->logger->debug("pop: 'key' => '{$key}', 'value' => '{$val}', Result: Success'");
		return json_decode($val);
	}

	/**
	 * 数据自增
	 * @param string $key KEY名称
	 */
	public function increment($key) {
		return $this->redis->incr($key);
	}

	/**
	 * 数据自减
	 * @param string $key KEY名称
	 */
	public function decrement($key) {
		return $this->redis->decr($key);
	}

	/**
	 * key是否存在，存在返回ture
	 * @param string $key KEY名称
	 */
	public function exists($key) {
		return $this->redis->exists($key);
	}

	/**
	 * 返回redis对象
	 * redis有非常多的操作方法，我们只封装了一部分
	 * 拿着这个对象就可以直接调用redis自身方法
	 */
	public function redis() {
		return $this->redis;
	}
}

/* End of file Someclass.php */
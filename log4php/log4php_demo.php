<?Php

require 'apache-log4php-2.3.0/Logger.php';

// 日志记录器
$this->logger = Logger::getLogger('sp');
$this->logger->error('恶意授权');
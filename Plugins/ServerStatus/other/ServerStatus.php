<?php
namespace CheckInfo;
if(get_url() != '{ServerStatus_url}' || $_GET['key'] != '{ServerStatus_key}'){
	exit();
}
function get_url(){
	if((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')){
		//return 'https'.'://'.$_SERVER['HTTP_HOST'].'/'.substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'],'/')+1);
		return 'https'.'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
	}else{
		//return 'http'.'://'.$_SERVER['HTTP_HOST'].'/'.substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'],'/')+1);
		return 'http'.'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
	}
}

namespace InnStudio\Prober\Components\PreDefine;
namespace InnStudio\Prober\Components\Helper;
\define('XPROBER_TIMER', \microtime(true));
\define('XPROBER_IS_DEV', false);
class HelperApi {
    public static function setFileCacheHeader() {
        $seconds = 3600 * 24 * 30 * 12;
        $ts = \gmdate('D, d M Y H:i:s', (int)$_SERVER['REQUEST_TIME'] + $seconds) . ' GMT';
        \header("Expires: {$ts}");
        \header('Pragma: cache');
        \header("Cache-Control: public, max-age={$seconds}");
    }
    public static function getWinCpuUsage() {
        $usage = array(
            'idle' => 100,
            'user' => 0,
            'sys' => 0,
            'nice' => 0,
        );
        if (\class_exists('\\COM')) {
            $wmi = new \COM('Winmgmts://');
            $server = $wmi->execquery('SELECT LoadPercentage FROM Win32_Processor');
            $total = 0;
            foreach ($server as $cpu) {
                $total+= (int)$cpu->loadpercentage;
            }
            $total = (int)$total / \count($server);
            $usage['idle'] = 100 - $total;
            $usage['user'] = $total;
        } else {
            if (!\function_exists('\exec')) {
                return $usage;
            }
            $p = array();
            \exec('wmic cpu get LoadPercentage', $p);
            if (isset($p[1])) {
                $percent = (int)$p[1];
                $usage['idle'] = 100 - $percent;
                $usage['user'] = $percent;
            }
        }
        return $usage;
    }
    public static function getNetworkStats() {
        $filePath = '/proc/net/dev';
        if (!@\is_readable($filePath)) {
            return null;
        }
        static $eths = null;
        if (null !== $eths) {
            return $eths;
        }
        $lines = \file($filePath);
        unset($lines[0], $lines[1]);
        $eths = array();
        foreach ($lines as $line) {
            $line = \preg_replace('/\s+/', ' ', \trim($line));
            $lineArr = \explode(':', $line);
            $numberArr = \explode(' ', \trim($lineArr[1]));
            $eths[$lineArr[0]] = array(
                'rx' => (int)$numberArr[0],
                'tx' => (int)$numberArr[8],
            );
        }
        return $eths;
    }
    public static function getDiskTotalSpace() {
        if (!\function_exists('\disk_total_space')) {
            return 0;
        }
        static $space = null;
        if (null === $space) {
            $space = (float)\disk_total_space(__DIR__);
        }
        return $space;
    }
    public static function getDiskFreeSpace() {
        if (!\function_exists('\disk_total_space')) {
            return 0;
        }
        static $space = null;
        if (null === $space) {
            $space = (float)\disk_free_space(__DIR__);
        }
        return $space;
    }
    public static function getCpuModel() {
        $filePath = '/proc/cpuinfo';
        if (!@\is_readable($filePath)) {
            return '';
        }
        $content = \file_get_contents($filePath);
        $cores = \substr_count($content, 'cache size');
        $lines = \explode("\n", $content);
        $modelName = \explode(':', $lines[4]);
        $modelName = \trim($modelName[1]);
        $cacheSize = \explode(':', $lines[8]);
        $cacheSize = \trim($cacheSize[1]);
        return "{$cores} x {$modelName} / " . \sprintf('%s cache', $cacheSize);
    }
    public static function getServerTime() {
        return \date('Y-m-d H:i:s');
    }
    public static function getServerUtcTime() {
        return \gmdate('Y/m/d H:i:s');
    }
    public static function getServerUptime() {
        $filePath = '/proc/uptime';
        if (!@\is_file($filePath)) {
            return array(
                'days' => 0,
                'hours' => 0,
                'mins' => 0,
                'secs' => 0,
            );
        }
        $str = \file_get_contents($filePath);
        $num = (float)$str;
        $secs = (int)\fmod($num, 60);
        $num = (int)($num / 60);
        $mins = (int)$num % 60;
        $num = (int)($num / 60);
        $hours = (int)$num % 24;
        $num = (int)($num / 24);
        $days = (int)$num;
        return array(
            'days' => $days,
            'hours' => $hours,
            'mins' => $mins,
            'secs' => $secs,
        );
    }
    public static function getErrNameByCode($code) {
        if (0 === (int)$code) {
            return '';
        }
        $levels = array(
            \E_ALL => 'E_ALL',
            \E_USER_DEPRECATED => 'E_USER_DEPRECATED',
            \E_DEPRECATED => 'E_DEPRECATED',
            \E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            \E_STRICT => 'E_STRICT',
            \E_USER_NOTICE => 'E_USER_NOTICE',
            \E_USER_WARNING => 'E_USER_WARNING',
            \E_USER_ERROR => 'E_USER_ERROR',
            \E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            \E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            \E_CORE_WARNING => 'E_CORE_WARNING',
            \E_CORE_ERROR => 'E_CORE_ERROR',
            \E_NOTICE => 'E_NOTICE',
            \E_PARSE => 'E_PARSE',
            \E_WARNING => 'E_WARNING',
            \E_ERROR => 'E_ERROR',
        );
        $result = '';
        foreach ($levels as $number => $name) {
            if (($code & $number) == $number) {
                $result.= ('' != $result ? ', ' : '') . $name;
            }
        }
        return $result;
    }
    public static function isWin() {
        return \PHP_OS === 'WINNT';
    }
    public static function getClientIp() {
        $keys = array(
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        );
        foreach ($keys as $key) {
            if (!isset($_SERVER[$key])) {
                continue;
            }
            $ip = \array_filter(\explode(',', $_SERVER[$key]));
            $ip = \filter_var(\end($ip) , \FILTER_VALIDATE_IP);
            if ($ip) {
                return $ip;
            }
        }
        return '';
    }
    public static function getCpuUsage() {
        static $cpu = null;
        if (null !== $cpu) {
            return $cpu;
        }
        if (self::isWin()) {
            $cpu = self::getWinCpuUsage();
            return $cpu;
        }
        $filePath = ('/proc/stat');
        if (!@\is_readable($filePath)) {
            $cpu = array();
            return array(
                'user' => 0,
                'nice' => 0,
                'sys' => 0,
                'idle' => 100,
            );
        }
        $stat1 = \file($filePath);
        \sleep(1);
        $stat2 = \file($filePath);
        $info1 = \explode(' ', \preg_replace('!cpu +!', '', $stat1[0]));
        $info2 = \explode(' ', \preg_replace('!cpu +!', '', $stat2[0]));
        $dif = array();
        $dif['user'] = $info2[0] - $info1[0];
        $dif['nice'] = $info2[1] - $info1[1];
        $dif['sys'] = $info2[2] - $info1[2];
        $dif['idle'] = $info2[3] - $info1[3];
        $total = \array_sum($dif);
        $cpu = array();
        foreach ($dif as $x => $y) {
            $cpu[$x] = \round($y / $total * 100, 1);
        }
        return $cpu;
    }
    public static function getHumanCpuUsage() {
        $cpu = self::getCpuUsage();
        return $cpu ? : array();
    }
    public static function getSysLoadAvg() {
        if (self::isWin()) {
            return array(
                0,
                0,
                0
            );
        }
        return \array_map(function ($load) {
            return (float)\sprintf('%.2f', $load);
        }
        , \sys_getloadavg());
    }
    public static function getMemoryUsage($key) {
        $key = \ucfirst($key);
        if (self::isWin()) {
            return 0;
        }
        static $memInfo = null;
        if (null === $memInfo) {
            $memInfoFile = '/proc/meminfo';
            if (!@\is_readable($memInfoFile)) {
                $memInfo = 0;
                return 0;
            }
            $memInfo = \file_get_contents($memInfoFile);
            $memInfo = \str_replace(array(
                ' kB',
                '  ',
            ) , '', $memInfo);
            $lines = array();
            foreach (\explode("\n", $memInfo) as $line) {
                if (!$line) {
                    continue;
                }
                $line = \explode(':', $line);
                $lines[$line[0]] = (float)$line[1] * 1024;
            }
            $memInfo = $lines;
        }
        if (!isset($memInfo['MemTotal'])) {
            return 0;
        }
        switch ($key) {
            case 'MemRealUsage':
                if (isset($memInfo['MemAvailable'])) {
                    return $memInfo['MemTotal'] - $memInfo['MemAvailable'];
                }
                if (isset($memInfo['MemFree'])) {
                    if (isset($memInfo['Buffers'], $memInfo['Cached'])) {
                        return $memInfo['MemTotal'] - $memInfo['MemFree'] - $memInfo['Buffers'] - $memInfo['Cached'];
                    }
                    return $memInfo['MemTotal'] - $memInfo['Buffers'];
                }
                return 0;
            case 'MemUsage':
                return isset($memInfo['MemFree']) ? $memInfo['MemTotal'] - $memInfo['MemFree'] : 0;
            case 'SwapUsage':
                if (!isset($memInfo['SwapTotal']) || !isset($memInfo['SwapFree'])) {
                    return 0;
                }
                return $memInfo['SwapTotal'] - $memInfo['SwapFree'];
        }
        return isset($memInfo[$key]) ? $memInfo[$key] : 0;
    }
    public static function formatBytes($bytes, $precision = 2) {
        if (!$bytes) {
            return 0;
        }
        $base = \log($bytes, 1024);
        $suffixes = array(
            '',
            ' K',
            ' M',
            ' G',
            ' T'
        );
        return \round(\pow(1024, ($base - \floor($base))) , $precision) . $suffixes[\floor($base) ];
    }
    public static function getHumamMemUsage($key) {
        return self::formatBytes(self::getMemoryUsage($key));
    }
    public static function strcut($str, $len = 20) {
        if (\strlen($str) > $len) {
            return \mb_strcut($str, 0, $len) . '...';
        }
        return $str;
    }
}
namespace InnStudio\Prober\Components\Benchmark;
use InnStudio\Prober\Components\Events\EventsApi;
use InnStudio\Prober\Components\Restful\HttpStatus;
use InnStudio\Prober\Components\Restful\RestfulResponse;
class Benchmark extends BenchmarkApi {
    public function __construct() {
        EventsApi::on('init', array(
            $this,
            'filter'
        ));
        new FetchBefore();
    }
    public function filter($action) {
        if ('benchmark' !== $action) {
            return $action;
        }
        $this->display();
    }
    public function display() {
        $remainingSeconds = $this->getRemainingSeconds();
        $response = new RestfulResponse();
        if ($remainingSeconds) {
            $response->setStatus(HttpStatus::$TOO_MANY_REQUESTS);
            $response->setData(array(
                'seconds' => $remainingSeconds,
            ));
            $response->dieJson();
        }
        \set_time_limit(0);
        $this->setExpired();
        $this->setIsRunning(true);
        $marks = $this->getPoints();
        $this->setIsRunning(false);
        $response->setData(array(
            'marks' => $marks,
        ));
        $response->dieJson();
    }
}
namespace InnStudio\Prober\Components\Benchmark;
class BenchmarkApi {
    private $EXPIRED = 60;
    public function getTmpRecorderPath() {
        return \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'xproberBenchmarkTimer';
    }
    public function setRecorder(array $data) {
        return (bool)\file_put_contents($this->getTmpRecorderPath() , \json_encode(\array_merge($this->getRecorder() , $data)));
    }
    public function setExpired() {
        return (bool)$this->setRecorder(array(
            'expired' => (int)$_SERVER['REQUEST_TIME'] + $this->EXPIRED,
        ));
    }
    public function setIsRunning($isRunning) {
        return (bool)$this->setRecorder(array(
            'isRunning' => true === (bool)$isRunning ? 1 : 0,
        ));
    }
    public function isRunning() {
        $recorder = $this->getRecorder();
        return isset($recorder['isRunning']) ? 1 === (int)$recorder['isRunning'] : false;
    }
    public function getRemainingSeconds() {
        $recorder = $this->getRecorder();
        $expired = isset($recorder['expired']) ? (int)$recorder['expired'] : 0;
        if (!$expired) {
            return 0;
        }
        return $expired > (int)$_SERVER['REQUEST_TIME'] ? $expired - (int)$_SERVER['REQUEST_TIME'] : 0;
    }
    public function getPointsByTime($time) {
        return \pow(10, 3) - (int)($time * \pow(10, 3));
    }
    public function getHashPoints() {
        $data = 'inn-studio.com';
        $hash = array(
            'md5',
            'sha512',
            'sha256',
            'crc32'
        );
        $count = \pow(10, 5);
        $start = \microtime(true);
        for ($i = 0; $i < $count; ++$i) {
            foreach ($hash as $v) {
                \hash($v, $data);
            }
        }
        return $this->getPointsByTime(\microtime(true) - $start);
    }
    public function getIntLoopPoints() {
        $j = 0;
        $count = \pow(10, 7);
        $start = \microtime(true);
        for ($i = 0; $i < $count; ++$i) {
            ++$j;
        }
        return $this->getPointsByTime(\microtime(true) - $start);
    }
    public function getFloatLoopPoints() {
        $j = 1 / 3;
        $count = \pow(10, 7);
        $start = \microtime(true);
        for ($i = 0; $i < $count; ++$i) {
            ++$j;
        }
        return $this->getPointsByTime(\microtime(true) - $start);
    }
    public function getIoLoopPoints() {
        $tmpDir = \sys_get_temp_dir();
        if (!\is_writable($tmpDir)) {
            return 0;
        }
        $count = \pow(10, 4);
        $start = \microtime(true);
        for ($i = 0; $i < $count; ++$i) {
            $filePath = "{$tmpDir}/innStudioIoBenchmark:{$i}";
            \file_put_contents($filePath, $filePath);
            \unlink($filePath);
        }
        return $this->getPointsByTime(\microtime(true) - $start);
    }
    public function getPoints() {
        return array(
            'hash' => $this->getHashPoints() ,
            'intLoop' => $this->getIntLoopPoints() ,
            'floatLoop' => $this->getFloatLoopPoints() ,
            'ioLoop' => $this->getIoLoopPoints() ,
        );
    }
    private function getRecorder() {
        $path = $this->getTmpRecorderPath();
        $defaults = array(
            'expired' => 0,
            'running' => 0,
        );
        if (!@\is_readable($path)) {
            return $defaults;
        }
        $data = (string)\file_get_contents($path);
        if (!$data) {
            return $defaults;
        }
        $data = \json_decode($data, true);
        if (!$data) {
            return $defaults;
        }
        return \array_merge($defaults, $data);
    }
}
namespace InnStudio\Prober\Components\Benchmark;
use InnStudio\Prober\Components\Events\EventsApi;
class FetchBefore extends BenchmarkApi {
    public function __construct() {
        EventsApi::on('fetchBefore', array(
            $this,
            'filter'
        ));
    }
    public function filter() {
        while ($this->isRunning()) {
            \sleep(2);
        }
    }
}
namespace InnStudio\Prober\Components\PhpInfoDetail;
use InnStudio\Prober\Components\Events\EventsApi;
class PhpInfoDetail {
    public function __construct() {
        EventsApi::on('init', array(
            $this,
            'filter'
        ));
    }
    public function filter($action) {
        if ('phpInfo' !== $action) {
            return $action;
        }
        \phpinfo();
        die;
    }
}
namespace InnStudio\Prober\Components\Bootstrap;
use InnStudio\Prober\Components\Events\EventsApi;
class Action {
    public function __construct() {
        EventsApi::emit('init', (string)\filter_input(\INPUT_GET, 'action', \FILTER_SANITIZE_STRING));
    }
}
namespace InnStudio\Prober\Components\Bootstrap;
use InnStudio\Prober\Components\Config\ConfigApi;
use InnStudio\Prober\Components\Events\EventsApi;
class Conf extends BootstrapConstants {
    public function __construct() {
        EventsApi::on('conf', array(
            $this,
            'conf'
        ));
    }
    public function conf(array $conf) {
        $conf[$this->ID] = array(
            'isDev' => \XPROBER_IS_DEV,
            'version' => ConfigApi::$APP_VERSION,
            'appName' => ConfigApi::$APP_NAME,
            'appUrl' => ConfigApi::$APP_URL,
            'appConfigUrls' => ConfigApi::$APP_CONFIG_URLS,
            'appConfigUrlDev' => ConfigApi::$APP_CONFIG_URL_DEV,
            'authorUrl' => ConfigApi::$AUTHOR_URL,
            'authorName' => ConfigApi::$AUTHOR_NAME,
            'authorization' => isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '',
        );
        return $conf;
    }
}
namespace InnStudio\Prober\Components\Bootstrap;
use InnStudio\Prober\Components\Config\ConfigApi;
use InnStudio\Prober\Components\Events\EventsApi;
class Render {
    public function __construct() {
        $appName = ConfigApi::$APP_NAME;
        $version = ConfigApi::$APP_VERSION;
        $scriptConf = \json_encode(EventsApi::emit('conf', array()));
        $scriptUrl = \defined('\XPROBER_IS_DEV') && \XPROBER_IS_DEV ? 'app.js' : "?action=script&amp;v={$version}";
    }
}
namespace InnStudio\Prober\Components\Bootstrap;
class Bootstrap {
    public function __construct() {
        new Action();
        new Conf();
        new Render();
    }
}
namespace InnStudio\Prober\Components\Bootstrap;
class BootstrapConstants {
    protected $ID = 'bootstrap';
}
namespace InnStudio\Prober\Components\Database;
use InnStudio\Prober\Components\Events\EventsApi;
class Conf extends DatabaseConstants {
    public function __construct() {
        EventsApi::on('conf', array(
            $this,
            'conf'
        ));
    }
    public function conf(array $conf) {
        $sqlite3Version = \class_exists('\\SQLite3') ? \SQLite3::version() : false;
        $conf[$this->ID] = array(
            'sqlite3' => $sqlite3Version ? $sqlite3Version['versionString'] : false,
            'sqliteLibversion' => \function_exists('\\sqlite_libversion') ? \sqlite_libversion() : false,
            'mysqliClientVersion' => \function_exists('\\mysqli_get_client_version') ? \mysqli_get_client_version(null) : false,
            'mongo' => \class_exists('\\Mongo') ,
            'mongoDb' => \class_exists('\\MongoDB') ,
            'postgreSql' => \function_exists('\\pg_connect') ,
            'paradox' => \function_exists('\\px_new') ,
            'msSql' => \function_exists('\\sqlsrv_server_info') ,
            'filePro' => \function_exists('\\filepro') ,
            'maxDbClient' => \function_exists('\\maxdb_get_client_version') ? \maxdb_get_client_version() : false,
            'maxDbServer' => \function_exists('\\maxdb_get_server_version') ? \maxdb_get_server_version() : false,
        );
        return $conf;
    }
}
namespace InnStudio\Prober\Components\Database;
class DatabaseConstants {
    protected $ID = 'database';
}
namespace InnStudio\Prober\Components\Database;
class Database {
    public function __construct() {
        new Conf();
    }
}
namespace InnStudio\Prober\Components\PhpInfo;
class PhpInfoConstants {
    protected $ID = 'phpInfo';
}
namespace InnStudio\Prober\Components\PhpInfo;
use InnStudio\Prober\Components\Config\ConfigApi;
use InnStudio\Prober\Components\Events\EventsApi;
use InnStudio\Prober\Components\Restful\HttpStatus;
use InnStudio\Prober\Components\Restful\RestfulResponse;
class FetchLatestPhpVersion extends PhpInfoConstants {
    public function __construct() {
        EventsApi::on('init', array(
            $this,
            'filter'
        ));
    }
    public function filter($action) {
        if ('latest-php-version' !== $action) {
            return $action;
        }
        $response = new RestfulResponse();
        $content = \file_get_contents('https://www.php.net/releases/?json');
        if (!$content) {
            $response->setStatus(HttpStatus::$NOT_FOUND);
            $response->dieJson();
        }
        $versions = \json_decode($content, true);
        if (!$versions) {
            $response->setStatus(HttpStatus::$NOT_FOUND);
            $response->dieJson();
        }
        $version = isset($versions[ConfigApi::$LATEST_PHP_STABLE_VERSION]['version']) ? $versions[ConfigApi::$LATEST_PHP_STABLE_VERSION]['version'] : '';
        if (!$version) {
            $response->setStatus(HttpStatus::$NOT_FOUND);
            $response->dieJson();
        }
        $response->setData(array(
            'version' => $version,
            'date' => $versions[ConfigApi::$LATEST_PHP_STABLE_VERSION]['date'],
        ));
        $response->dieJson();
    }
}
namespace InnStudio\Prober\Components\PhpInfo;
class PhpInfo {
    public function __construct() {
        new Conf();
        new FetchLatestPhpVersion();
    }
}
namespace InnStudio\Prober\Components\PhpInfo;
use InnStudio\Prober\Components\Events\EventsApi;
class Conf extends PhpInfoConstants {
    public function __construct() {
        EventsApi::on('conf', array(
            $this,
            'conf'
        ));
    }
    public function conf(array $conf) {
        $conf[$this->ID] = array(
            'version' => \PHP_VERSION,
            'sapi' => \PHP_SAPI,
            'displayErrors' => (bool)\ini_get('display_errors') ,
            'errorReporting' => (int)\ini_get('error_reporting') ,
            'memoryLimit' => (string)\ini_get('memory_limit') ,
            'postMaxSize' => (string)\ini_get('post_max_size') ,
            'uploadMaxFilesize' => (string)\ini_get('upload_max_filesize') ,
            'maxInputVars' => (int)\ini_get('max_input_vars') ,
            'maxExecutionTime' => (int)\ini_get('max_execution_time') ,
            'defaultSocketTimeout' => (int)\ini_get('default_socket_timeout') ,
            'allowUrlFopen' => (bool)\ini_get('allow_url_fopen') ,
            'smtp' => (bool)\ini_get('SMTP') ,
            'disableFunctions' => \array_filter(\explode(',', (string)\ini_get('disable_functions'))) ,
            'disableClasses' => \array_filter(\explode(',', (string)\ini_get('disable_classes'))) ,
        );
        return $conf;
    }
}
namespace InnStudio\Prober\Components\Ping;
use InnStudio\Prober\Components\Events\EventsApi;
use InnStudio\Prober\Components\Restful\RestfulResponse;
class Ping {
    public function __construct() {
        EventsApi::on('init', array(
            $this,
            'filter'
        ));
    }
    public function filter($action) {
        if ('ping' !== $action) {
            return $action;
        }
        $response = new RestfulResponse(array(
            'time' => \microtime(true) - \XPROBER_TIMER,
        ));
        $response->dieJson();
    }
}
namespace InnStudio\Prober\Components\Script;
use InnStudio\Prober\Components\Events\EventsApi;
use InnStudio\Prober\Components\Helper\HelperApi;
class Script {
    private $ID = 'script';
    public function __construct() {
        EventsApi::on('init', array(
            $this,
            'filter'
        ));
    }
    public function filter($action) {
        if ('script' !== $action) {
            return $action;
        }
        $this->output();
    }
    private function output() {
        HelperApi::setFileCacheHeader();
        die;
    }
}
namespace InnStudio\Prober\Components\Events;
class EventsApi {
    private static $events = array();
    private static $PRIORITY_ID = 'priority';
    private static $CALLBACK_ID = 'callback';
    public static function on($name, $callback, $priority = 10) {
        if (!isset(self::$events[$name])) {
            self::$events[$name] = array();
        }
        self::$events[$name][] = array(
            self::$PRIORITY_ID => $priority,
            self::$CALLBACK_ID => $callback,
        );
    }
    public static function emit() {
        $args = \func_get_args();
        $name = $args[0];
        $return = isset($args[1]) ? $args[1] : null;
        unset($args[0], $args[1]);
        $events = isset(self::$events[$name]) ? self::$events[$name] : false;
        if (!$events) {
            return $return;
        }
        $sortArr = array();
        foreach ($events as $k => $filter) {
            $sortArr[$k] = $filter[self::$PRIORITY_ID];
        }
        \array_multisort($sortArr, $events);
        foreach ($events as $filter) {
            $return = \call_user_func_array($filter[self::$CALLBACK_ID], array(
                $return,
                $args
            ));
        }
        return $return;
    }
}
namespace InnStudio\Prober\Components\MyInfo;
class MyInfoConstants {
    protected $ID = 'myInfo';
}
namespace InnStudio\Prober\Components\MyInfo;
class MyInfo {
    public function __construct() {
        new Conf();
    }
}
namespace InnStudio\Prober\Components\MyInfo;
use InnStudio\Prober\Components\Events\EventsApi;
use InnStudio\Prober\Components\Helper\HelperApi;
class Conf extends MyInfoConstants {
    public function __construct() {
        EventsApi::on('conf', array(
            $this,
            'conf'
        ));
    }
    public function conf(array $conf) {
        $conf[$this->ID] = array(
            'ip' => HelperApi::getClientIp() ,
            'phpLanguage' => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '-',
        );
        return $conf;
    }
}
namespace InnStudio\Prober\Components\Timezone;
use InnStudio\Prober\Components\Events\EventsApi;
class Timezone {
    public function __construct() {
        EventsApi::on('init', array(
            $this,
            'filter'
        ) , 1);
    }
    public function filter($action) {
        if (!\ini_get('date.timezone')) {
            \date_default_timezone_set('GMT');
        }
        return $action;
    }
}
namespace InnStudio\Prober\Components\ServerStatus;
class ServerStatusConstants {
    protected $ID = 'serverStatus';
}
namespace InnStudio\Prober\Components\ServerStatus;
use InnStudio\Prober\Components\Events\EventsApi;
use InnStudio\Prober\Components\Helper\HelperApi;
class Fetch extends ServerStatusConstants {
    public function __construct() {
        EventsApi::on('fetch', array(
            $this,
            'filter'
        ));
    }
    public function filter(array $items) {
        $items[$this->ID] = array(
            'sysLoad' => HelperApi::getSysLoadAvg() ,
            'cpuUsage' => HelperApi::getCpuUsage() ,
            'memRealUsage' => array(
                'value' => HelperApi::getMemoryUsage('MemRealUsage') ,
                'max' => HelperApi::getMemoryUsage('MemTotal') ,
            ) ,
            'memBuffers' => array(
                'value' => HelperApi::getMemoryUsage('Buffers') ,
                'max' => HelperApi::getMemoryUsage('MemUsage') ,
            ) ,
            'memCached' => array(
                'value' => HelperApi::getMemoryUsage('Cached') ,
                'max' => HelperApi::getMemoryUsage('MemUsage') ,
            ) ,
            'swapUsage' => array(
                'value' => HelperApi::getMemoryUsage('SwapUsage') ,
                'max' => HelperApi::getMemoryUsage('SwapTotal') ,
            ) ,
            'swapCached' => array(
                'value' => HelperApi::getMemoryUsage('SwapCached') ,
                'max' => HelperApi::getMemoryUsage('SwapUsage') ,
            ) ,
        );
        return $items;
    }
}
namespace InnStudio\Prober\Components\ServerStatus;
use InnStudio\Prober\Components\Events\EventsApi;
use InnStudio\Prober\Components\Helper\HelperApi;
class Conf extends ServerStatusConstants {
    public function __construct() {
        EventsApi::on('conf', array(
            $this,
            'conf'
        ));
    }
    public function conf(array $conf) {
        $conf[$this->ID] = array(
            'sysLoad' => HelperApi::getSysLoadAvg() ,
            'memRealUsage' => array(
                'value' => HelperApi::getMemoryUsage('MemRealUsage') ,
                'max' => HelperApi::getMemoryUsage('MemTotal') ,
            ) ,
            'memBuffers' => array(
                'value' => HelperApi::getMemoryUsage('Buffers') ,
                'max' => HelperApi::getMemoryUsage('MemUsage') ,
            ) ,
            'memCached' => array(
                'value' => HelperApi::getMemoryUsage('Cached') ,
                'max' => HelperApi::getMemoryUsage('MemUsage') ,
            ) ,
            'swapUsage' => array(
                'value' => HelperApi::getMemoryUsage('SwapUsage') ,
                'max' => HelperApi::getMemoryUsage('SwapTotal') ,
            ) ,
            'swapCached' => array(
                'value' => HelperApi::getMemoryUsage('SwapCached') ,
                'max' => HelperApi::getMemoryUsage('SwapUsage') ,
            ) ,
        );
        return $conf;
    }
}
namespace InnStudio\Prober\Components\ServerStatus;
class ServerStatus {
    public function __construct() {
        new Conf();
        new Fetch();
    }
}
namespace InnStudio\Prober\Components\PhpExtensions;
use InnStudio\Prober\Components\Events\EventsApi;
class Conf extends PhpExtensionsConstants {
    public function __construct() {
        EventsApi::on('conf', array(
            $this,
            'conf'
        ));
    }
    public function conf(array $conf) {
        $conf[$this->ID] = array(
            'redis' => \extension_loaded('redis') && \class_exists('\\Redis') ,
            'sqlite3' => \extension_loaded('sqlite3') && \class_exists('\\Sqlite3') ,
            'memcache' => \extension_loaded('memcache') && \class_exists('\\Memcache') ,
            'memcached' => \extension_loaded('memcached') && \class_exists('\\Memcached') ,
            'opcache' => \function_exists('\opcache_get_configuration') ,
            'opcacheEnabled' => $this->isOpcEnabled() ,
            'swoole' => \extension_loaded('swoole') && \function_exists('\\swoole_version') ,
            'imagick' => \extension_loaded('imagick') && \class_exists('\\Imagick') ,
            'gmagick' => \extension_loaded('gmagick') ,
            'exif' => \extension_loaded('exif') && \function_exists('\\exif_imagetype') ,
            'fileinfo' => \extension_loaded('fileinfo') ,
            'simplexml' => \extension_loaded('simplexml') ,
            'sockets' => \extension_loaded('sockets') && \function_exists('\\socket_accept') ,
            'mysqli' => \extension_loaded('mysqli') && \class_exists('\\mysqli') ,
            'zip' => \extension_loaded('zip') && \class_exists('\\ZipArchive') ,
            'mbstring' => \extension_loaded('mbstring') && \function_exists('\\mb_substr') ,
            'phalcon' => \extension_loaded('phalcon') ,
            'xdebug' => \extension_loaded('xdebug') ,
            'zendOtimizer' => \function_exists('\\zend_optimizer_version') ,
            'ionCube' => \extension_loaded('ioncube loader') ,
            'sourceGuardian' => \extension_loaded('sourceguardian') ,
            'ldap' => \function_exists('\\ldap_connect') ,
            'curl' => \function_exists('\\curl_init') ,
            'loadedExtensions' => \get_loaded_extensions() ,
        );
        return $conf;
    }
    private function isOpcEnabled() {
        $isOpcEnabled = \function_exists('\\opcache_get_configuration');
        if ($isOpcEnabled) {
            $isOpcEnabled = \opcache_get_configuration();
            $isOpcEnabled = isset($isOpcEnabled['directives']['opcache.enable']) && true === $isOpcEnabled['directives']['opcache.enable'];
        }
        return $isOpcEnabled;
    }
}
namespace InnStudio\Prober\Components\PhpExtensions;
class PhpExtensions {
    public function __construct() {
        new Conf();
    }
}
namespace InnStudio\Prober\Components\PhpExtensions;
class PhpExtensionsConstants {
    protected $ID = 'phpExtensions';
}
namespace InnStudio\Prober\Components\Updater;
use InnStudio\Prober\Components\Config\ConfigApi;
use InnStudio\Prober\Components\Events\EventsApi;
use InnStudio\Prober\Components\Restful\HttpStatus;
use InnStudio\Prober\Components\Restful\RestfulResponse;
class Updater {
    public function __construct() {
        EventsApi::on('init', array(
            $this,
            'filter'
        ));
    }
    public function filter($action) {
        if ('update' !== $action) {
            return $action;
        }
        $response = new RestfulResponse();
        if (!\is_writable(__FILE__)) {
            $response->setStatus(HttpStatus::$INSUFFICIENT_STORAGE);
            $response->dieJson();
        }
        $code = '';
        foreach (ConfigApi::$UPDATE_PHP_URLS as $url) {
            $code = (string)\file_get_contents($url);
            if ('' !== \trim($code)) {
                break;
            }
        }
        if (!$code) {
            $response->setStatus(HttpStatus::$NOT_FOUND);
            $response->dieJson();
        }
        if (\XPROBER_IS_DEV) {
            $response->dieJson();
        }
        if ((bool)\file_put_contents(__FILE__, $code)) {
            if (\function_exists('\\opcache_compile_file')) {
                @\opcache_compile_file(__FILE__) || \opcache_reset();
            }
            $response->dieJson();
        }
        $response->setStatus(HttpStatus::$INTERNAL_SERVER_ERROR);
        $response->dieJson();
    }
}
namespace InnStudio\Prober\Components\NetworkStats;
use InnStudio\Prober\Components\Events\EventsApi;
use InnStudio\Prober\Components\Helper\HelperApi;
class Fetch extends NetworkStatsConstants {
    public function __construct() {
        HelperApi::isWin() || EventsApi::on('fetch', array(
            $this,
            'filter'
        ));
    }
    public function filter(array $items) {
        $items[$this->ID] = array(
            'networks' => HelperApi::getNetworkStats() ,
        );
        return $items;
    }
}
namespace InnStudio\Prober\Components\NetworkStats;
class NetworkStatsConstants {
    protected $ID = 'networkStats';
}
namespace InnStudio\Prober\Components\NetworkStats;
use InnStudio\Prober\Components\Events\EventsApi;
use InnStudio\Prober\Components\Helper\HelperApi;
class Conf extends NetworkStatsConstants {
    public function __construct() {
        HelperApi::isWin() || EventsApi::on('conf', array(
            $this,
            'conf'
        ));
    }
    public function conf(array $conf) {
        $conf[$this->ID] = array(
            'networks' => HelperApi::getNetworkStats() ,
        );
        return $conf;
    }
}
namespace InnStudio\Prober\Components\NetworkStats;
class NetworkStats {
    public function __construct() {
        new Conf();
        new Fetch();
    }
}
namespace InnStudio\Prober\Components\Config;
class ConfigApi {
    public static $APP_VERSION = '4.3';
    public static $APP_NAME = 'X Prober';
    public static $APP_URL = 'https://github.com/kmvan/x-prober';
    public static $APP_CONFIG_URLS = array(
        'https://raw.githubusercontent.com/kmvan/x-prober/master/AppConfig.json',
        'https://api.inn-studio.com/download/?id=xprober-config'
    );
    public static $APP_CONFIG_URL_DEV = 'http://localhost:8000/AppConfig.json';
    public static $APP_TEMPERATURE_SENSOR_URL = 'http://127.0.0.1';
    public static $APP_TEMPERATURE_SENSOR_PORTS = array(
        2048,
        4096
    );
    public static $AUTHOR_URL = 'https://inn-studio.com/prober';
    public static $UPDATE_PHP_URLS = array(
        'https://raw.githubusercontent.com/kmvan/x-prober/master/dist/prober.php',
        'https://api.inn-studio.com/download/?id=xprober'
    );
    public static $AUTHOR_NAME = 'INN STUDIO';
    public static $LATEST_PHP_STABLE_VERSION = '7';
    public static $LATEST_NGINX_STABLE_VERSION = '1.16.1';
}
namespace InnStudio\Prober\Components\TemperatureSensor;
use InnStudio\Prober\Components\Config\ConfigApi;
use InnStudio\Prober\Components\Events\EventsApi;
use InnStudio\Prober\Components\Restful\HttpStatus;
use InnStudio\Prober\Components\Restful\RestfulResponse;
class TemperatureSensor {
    public function __construct() {
        EventsApi::on('init', array(
            $this,
            'filter'
        ));
    }
    public function filter($action) {
        if ('temperature-sensor' !== $action) {
            return $action;
        }
        $response = new RestfulResponse();
        $items = $this->getItems();
        if ($items) {
            $response->setData($items)->dieJson();
        }
        $cpuTemp = $this->getCpuTemp();
        if (!$cpuTemp) {
            $response->setStatus(HttpStatus::$NO_CONTENT);
        }
        $items[] = array(
            'id' => 'cpu',
            'name' => 'CPU',
            'celsius' => \round((float)$cpuTemp / 1000, 2) ,
        );
        $response->setData($items)->dieJson();
    }
    private function curl($url) {
        if (!\function_exists('\\curl_init')) {
            return null;
        }
        $ch = \curl_init();
        \curl_setopt_array($ch, array(
            \CURLOPT_URL => $url,
            \CURLOPT_RETURNTRANSFER => true,
        ));
        $res = \curl_exec($ch);
        \curl_close($ch);
        return (string)$res;
    }
    private function getItems() {
        $items = array();
        foreach (ConfigApi::$APP_TEMPERATURE_SENSOR_PORTS as $port) {
            $res = $this->curl(ConfigApi::$APP_TEMPERATURE_SENSOR_URL . ":{$port}");
            if (!$res) {
                continue;
            }
            $item = \json_decode($res, true);
            if (!$item || !\is_array($item)) {
                continue;
            }
            $items = $item;
            break;
        }
        return $items;
    }
    private function getCpuTemp() {
        try {
            $path = '/sys/class/thermal/thermal_zone0/temp';
            return \file_exists($path) ? (int)\file_get_contents($path) : 0;
        }
        catch(\Exception $e) {
            return 0;
        }
    }
}
namespace InnStudio\Prober\Components\Restful;
class HttpStatus {
    public static $__default = 200;
    public static $CONTINUE = 100;
    public static $SWITCHING_PROTOCOLS = 101;
    public static $PROCESSING = 102;
    public static $OK = 200;
    public static $CREATED = 201;
    public static $ACCEPTED = 202;
    public static $NON_AUTHORITATIVE_INFORMATION = 203;
    public static $NO_CONTENT = 204;
    public static $RESET_CONTENT = 205;
    public static $PARTIAL_CONTENT = 206;
    public static $MULTI_STATUS = 207;
    public static $ALREADY_REPORTED = 208;
    public static $IM_USED = 226;
    public static $MULTIPLE_CHOICES = 300;
    public static $MOVED_PERMANENTLY = 301;
    public static $FOUND = 302;
    public static $SEE_OTHER = 303;
    public static $NOT_MODIFIED = 304;
    public static $USE_PROXY = 305;
    public static $SWITCH_PROXY = 306;
    public static $TEMPORARY_REDIRECT = 307;
    public static $PERMANENT_REDIRECT = 308;
    public static $BAD_REQUEST = 400;
    public static $UNAUTHORIZED = 401;
    public static $PAYMENT_REQUIRED = 402;
    public static $FORBIDDEN = 403;
    public static $NOT_FOUND = 404;
    public static $METHOD_NOT_ALLOWED = 405;
    public static $NOT_ACCEPTABLE = 406;
    public static $PROXY_AUTHENTICATION_REQUIRED = 407;
    public static $REQUEST_TIMEOUT = 408;
    public static $CONFLICT = 409;
    public static $GONE = 410;
    public static $LENGTH_REQUIRED = 411;
    public static $PRECONDITION_FAILED = 412;
    public static $REQUEST_ENTITY_TOO_LARGE = 413;
    public static $REQUEST_URI_TOO_LONG = 414;
    public static $UNSUPPORTED_MEDIA_TYPE = 415;
    public static $REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    public static $EXPECTATION_FAILED = 417;
    public static $I_AM_A_TEAPOT = 418;
    public static $AUTHENTICATION_TIMEOUT = 419;
    public static $ENHANCE_YOUR_CALM = 420;
    public static $METHOD_FAILURE = 420;
    public static $UNPROCESSABLE_ENTITY = 422;
    public static $LOCKED = 423;
    public static $FAILED_DEPENDENCY = 424;
    public static $UNORDERED_COLLECTION = 425;
    public static $UPGRADE_REQUIRED = 426;
    public static $PRECONDITION_REQUIRED = 428;
    public static $TOO_MANY_REQUESTS = 429;
    public static $REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    public static $NO_RESPONSE = 444;
    public static $RETRY_WITH = 449;
    public static $BLOCKED_BY_WINDOWS_PARENTAL_CONTROLS = 450;
    public static $REDIRECT = 451;
    public static $UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    public static $REQUEST_HEADER_TOO_LARGE = 494;
    public static $CERT_ERROR = 495;
    public static $NO_CERT = 496;
    public static $HTTP_TO_HTTPS = 497;
    public static $CLIENT_CLOSED_REQUEST = 499;
    public static $INTERNAL_SERVER_ERROR = 500;
    public static $NOT_IMPLEMENTED = 501;
    public static $BAD_GATEWAY = 502;
    public static $SERVICE_UNAVAILABLE = 503;
    public static $GATEWAY_TIMEOUT = 504;
    public static $HTTP_VERSION_NOT_SUPPORTED = 505;
    public static $VARIANT_ALSO_NEGOTIATES = 506;
    public static $INSUFFICIENT_STORAGE = 507;
    public static $LOOP_DETECTED = 508;
    public static $BANDWIDTH_LIMIT_EXCEEDED = 509;
    public static $NOT_EXTENDED = 510;
    public static $NETWORK_AUTHENTICATION_REQUIRED = 511;
    public static $NETWORK_READ_TIMEOUT_ERROR = 598;
    public static $NETWORK_CONNECT_TIMEOUT_ERROR = 599;
}
namespace InnStudio\Prober\Components\Restful;
class RestfulResponse {
    protected $data;
    protected $headers = array();
    protected $status = 200;
    public function __construct(array $data = null, $status = 200, array $headers = array()) {
        $this->setData($data);
        $this->setStatus($status);
        $this->setHeaders($headers);
    }
    public function setHeader($key, $value, $replace = true) {
        if ($replace || !isset($this->headers[$key])) {
            $this->headers[$key] = $value;
        } else {
            $this->headers[$key].= ", {$value}";
        }
    }
    public function setHeaders(array $headers) {
        $this->headers = $headers;
    }
    public function getHeaders() {
        return $this->headers;
    }
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }
    public function getStatus() {
        return $this->status;
    }
    public function setData($data) {
        $this->data = $data;
        return $this;
    }
    public function getData() {
        return $this->data;
    }
    public function toJson() {
        $data = $this->getData();
        if (null === $data) {
            return '';
        }
        return \json_encode($data);
    }
    public function dieJson() {
        $this->httpResponseCode($this->status);
        \header('Content-Type: application/json');
        \header('Expires: 0');
        \header('Last-Modified: ' . \gmdate('D, d M Y H:i:s') . ' GMT');
        \header('Cache-Control: no-store, no-cache, must-revalidate');
        \header('Pragma: no-cache');
        $json = $this->toJson();
        if ('' === $json) {
            die;
        }
        die($json);
    }
    private function httpResponseCode($code) {
        if (\function_exists('http_response_code')) {
            return \http_response_code($code);
        }
        $statusCode = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => "I'm a teapot",
            419 => 'Authentication Timeout',
            420 => 'Enhance Your Calm',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            424 => 'Method Failure',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            444 => 'No Response',
            449 => 'Retry With',
            450 => 'Blocked by Windows Parental Controls',
            451 => 'Unavailable For Legal Reasons',
            494 => 'Request Header Too Large',
            495 => 'Cert Error',
            496 => 'No Cert',
            497 => 'HTTP to HTTPS',
            499 => 'Client Closed Request',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop Detected',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
            598 => 'Network read timeout error',
            599 => 'Network connect timeout error',
        );
        $msg = isset($statusCode[$code]) ? $statusCode[$code] : 'Unknow error';
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        \header("{$protocol} {$code} {$msg}");
    }
}
namespace InnStudio\Prober\Components\Fetch;
use InnStudio\Prober\Components\Events\EventsApi;
use InnStudio\Prober\Components\Restful\RestfulResponse;
class Fetch {
    public function __construct() {
        EventsApi::on('init', array(
            $this,
            'filter'
        ) , 100);
    }
    public function filter($action) {
        if ('fetch' === $action) {
            EventsApi::emit('fetchBefore');
            $response = new RestfulResponse(EventsApi::emit('fetch', array()));
            $response->dieJson();
        }
        return $action;
    }
}
namespace InnStudio\Prober\Components\ServerInfo;
class ServerInfoConstants {
    protected $ID = 'serverInfo';
}
namespace InnStudio\Prober\Components\ServerInfo;
class ServerInfo {
    public function __construct() {
        new Conf();
        new Fetch();
    }
}
namespace InnStudio\Prober\Components\ServerInfo;
use InnStudio\Prober\Components\Events\EventsApi;
use InnStudio\Prober\Components\Helper\HelperApi;
class Fetch extends ServerInfoConstants {
    public function __construct() {
        EventsApi::on('fetch', array(
            $this,
            'filter'
        ));
    }
    public function filter(array $items) {
        $items[$this->ID] = array(
            'serverTime' => HelperApi::getServerTime() ,
            'serverUptime' => HelperApi::getServerUptime() ,
            'serverUtcTime' => HelperApi::getServerUtcTime() ,
            'diskUsage' => array(
                'value' => HelperApi::getDiskTotalSpace() - HelperApi::getDiskFreeSpace() ,
                'max' => HelperApi::getDiskTotalSpace() ,
            ) ,
        );
        return $items;
    }
}
namespace InnStudio\Prober\Components\ServerInfo;
use InnStudio\Prober\Components\Events\EventsApi;
use InnStudio\Prober\Components\Helper\HelperApi;
class Conf extends ServerInfoConstants {
    public function __construct() {
        EventsApi::on('conf', array(
            $this,
            'conf'
        ));
    }
    public function conf(array $conf) {
        $conf[$this->ID] = array(
            'serverName' => $this->getServerInfo('SERVER_NAME') ,
            'serverUtcTime' => HelperApi::getServerUtcTime() ,
            'serverTime' => HelperApi::getServerTime() ,
            'serverUptime' => HelperApi::getServerUptime() ,
            'serverIp' => $this->getServerInfo('SERVER_ADDR') ,
            'serverSoftware' => $this->getServerInfo('SERVER_SOFTWARE') ,
            'phpVersion' => \PHP_VERSION,
            'cpuModel' => HelperApi::getCpuModel() ,
            'serverOs' => \php_uname() ,
            'scriptPath' => __FILE__,
            'diskUsage' => array(
                'value' => HelperApi::getDiskTotalSpace() - HelperApi::getDiskFreeSpace() ,
                'max' => HelperApi::getDiskTotalSpace() ,
            ) ,
        );
        return $conf;
    }
    private function getServerInfo($key) {
        return isset($_SERVER[$key]) ? $_SERVER[$key] : '';
    }
}
namespace InnStudio\Prober\Components\Footer;
use InnStudio\Prober\Components\Events\EventsApi;
class Footer {
    private $ID = 'footer';
    public function __construct() {
        EventsApi::on('conf', array(
            $this,
            'conf'
        ) , \PHP_INT_MAX);
    }
    public function conf(array $conf) {
        $conf[$this->ID] = array(
            'memUsage' => \memory_get_usage() ,
            'time' => \microtime(true) - (\defined('\XPROBER_TIMER') ? \XPROBER_TIMER : 0) ,
        );
        return $conf;
    }
}
new \InnStudio\Prober\Components\Benchmark\Benchmark();
new \InnStudio\Prober\Components\Database\Database();
new \InnStudio\Prober\Components\Fetch\Fetch();
new \InnStudio\Prober\Components\Footer\Footer();
new \InnStudio\Prober\Components\MyInfo\MyInfo();
new \InnStudio\Prober\Components\NetworkStats\NetworkStats();
new \InnStudio\Prober\Components\PhpExtensions\PhpExtensions();
new \InnStudio\Prober\Components\PhpInfo\PhpInfo();
new \InnStudio\Prober\Components\PhpInfoDetail\PhpInfoDetail();
new \InnStudio\Prober\Components\Ping\Ping();
new \InnStudio\Prober\Components\Script\Script();
new \InnStudio\Prober\Components\ServerInfo\ServerInfo();
new \InnStudio\Prober\Components\ServerStatus\ServerStatus();
new \InnStudio\Prober\Components\TemperatureSensor\TemperatureSensor();
new \InnStudio\Prober\Components\Timezone\Timezone();
new \InnStudio\Prober\Components\Updater\Updater();
new \InnStudio\Prober\Components\Bootstrap\Bootstrap();
?>
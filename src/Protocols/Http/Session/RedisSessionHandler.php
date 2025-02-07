<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Workerman\Protocols\Http\Session;

use Workerman\Protocols\Http\Session;
use Workerman\Timer;
use RedisException;

/**
 * Class RedisSessionHandler
 * @package Workerman\Protocols\Http\Session
 */
class RedisSessionHandler implements SessionHandlerInterface
{

    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * @var array
     */
    protected $config;

    /**
     * RedisSessionHandler constructor.
     * @param array $config = [
     *  'host'     => '127.0.0.1',
     *  'port'     => 6379,
     *  'timeout'  => 2,
     *  'auth'     => '******',
     *  'database' => 2,
     *  'prefix'   => 'redis_session_',
     *  'ping'     => 55,
     * ]
     */
    public function __construct($config)
    {
        if (false === extension_loaded('redis')) {
            throw new \RuntimeException('Please install redis extension.');
        }

        if (!isset($config['timeout'])) {
            $config['timeout'] = 2;
        }

        $this->config = $config;

        $this->connect();

        Timer::add($config['ping'] ?? 55, function () {
            $this->redis->get('ping');
        });
    }

    public function connect()
    {
        $config = $this->config;

        $this->redis = new \Redis();
        if (false === $this->redis->connect($config['host'], $config['port'], $config['timeout'])) {
            throw new \RuntimeException("Redis connect {$config['host']}:{$config['port']} fail.");
        }
        if (!empty($config['auth'])) {
            $this->redis->auth($config['auth']);
        }
        if (!empty($config['database'])) {
            $this->redis->select($config['database']);
        }
        if (empty($config['prefix'])) {
            $config['prefix'] = 'redis_session_';
        }
        $this->redis->setOption(\Redis::OPT_PREFIX, $config['prefix']);
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $name)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        try {
            return $this->redis->get($sessionId);
        } catch (RedisException $e) {
            $msg = strtolower($e->getMessage());
            if ($msg === 'connection lost' || strpos($msg, 'went away')) {
                $this->connect();
                return $this->redis->get($sessionId);
            }
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $sessionData)
    {
        return true === $this->redis->setex($sessionId, Session::$lifetime, $sessionData);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($id, $data = "")
    {
        return true === $this->redis->expire($id, Session::$lifetime);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        $this->redis->del($sessionId);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return true;
    }
}

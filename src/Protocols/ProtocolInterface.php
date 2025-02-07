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

namespace Workerman\Protocols;

use Workerman\Connection\ConnectionInterface;

/**
 * Protocol interface
 */
interface ProtocolInterface
{
    /**
     * Check the integrity of the package.
     * Please return the length of package.
     * If length is unknow please return 0 that mean wating more data.
     * If the package has something wrong please return false the connection will be closed.
     *
     * @param string $recvBuffer
     * @param ConnectionInterface $connection
     * @return int|false
     */
    public static function input($recvBuffer, ConnectionInterface $connection);

    /**
     * Decode package and emit onMessage($message) callback, $message is the result that decode returned.
     *
     * @param string $recvBuffer
     * @param ConnectionInterface $connection
     * @return mixed
     */
    public static function decode($recvBuffer, ConnectionInterface $connection);

    /**
     * Encode package brefore sending to client.
     *
     * @param mixed $data
     * @param ConnectionInterface $connection
     * @return string
     */
    public static function encode($data, ConnectionInterface $connection);
}

<?php
/**
 * Sapphire
 * 
 * Licensed under the Massachusetts Institute of Technology
 * 
 * For full copyright and license information, please see the LICENSE file
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Lorne Wang < post@lorne.wang >
 * @copyright   Copyright (c) 2014 - 2015 , All rights reserved.
 * @link        http://lorne.wang/projects/sapphire
 * @license     http://lorne.wang/licenses/MIT
 */
namespace Sapphire\Queue;

interface QueueInterface
{
    // Producer
    public function push($key, $message);

    // Consumer
    public function pop($key);

    // Publisher
    public function publish($key, $message);

    // Subscribe
    public function subscribe($key);
}

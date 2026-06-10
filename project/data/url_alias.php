<?php

declare(strict_types=1);

/**
 * URL-aliases list
 * Example:
 *  '/index'  => 'sham:/home',
 *  '/home'   => 'aaa/111',
 *  '/home/*' => 'int:/index1',
 *  '/ttt2/*' => 'int:/ttt1/../ttt1/',
 * @version 1.0
 */
return [
    '/tform/*'     => 'sham:/test/form',
];

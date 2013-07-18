<?php
/**
 * Include graphite and indicate whether we have a valid database connection
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  Pulse
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA
 * @link     http://github.com/LoneFry/Pulse
 */

require dirname(__DIR__).'/^/includeme.php';
echo G::$M->open ? 'true' : 'false';

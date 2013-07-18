<?php
/**
 * File : /^Pulse/controllers/PulseController.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  Pulse
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA
 * @link     http://github.com/LoneFry/Pulse
 */

/**
 * Controller Pulse checking and being checked
 *
 * @category Graphite
 * @package  Pulse
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA
 * @link     http://github.com/LoneFry/Pulse
 * @see      Controller
 */
class PulseController extends Controller {
    /**
     * Set the default action
     */
    protected $action = 'check';

    /**
     * output 'true' and exit PHP to signal successful graphite request
     *
     * @param array $argv command arguments
     * 
     * @return void
     */
    public function do_graphite_test($argv) {
        die('true');
    }

    /**
     * Invoke tests and output JSON pack of results
     *
     * @param array $argv command arguments
     *                    1. Host to check
     * 
     * @return void
     */
    public function do_check($argv) {
        if (isset($argv[1])) {
            $host = $argv[1];
        } else {
            $host = $_SERVER['SERVER_NAME'];
        }
        
        $json = self::checkHost($host);
        die($json);
    }
    
    /**
     * Tests the specified host for four levels of functionality
     *
     * @param string $host the host to check
     *
     * @return string JSON packet of results
     */
    public static function checkHost($host) {
        $tests = array(
            'httpd'    => '/^Pulse/httpd_test.txt',
            'php'      => '/^Pulse/php_test.php',
            'mysqld'   => '/^Pulse/mysqld_test.php',
            'graphite' => '/Pulse/graphite_test',
        );
        
        foreach ($tests as $k => $path) {
            $url = 'http://'.$host.$path;
            $return = self::_curl($url);
            $tests[$k] = ('true' == $return) ? 1 : 0;
        }
        $json = json_encode($tests);
        
        if (isset(G::$G['Pulse']['contact'])
            && filter_var(G::$G['Pulse']['contact'], FILTER_VALIDATE_EMAIL)
            && count($tests) != array_sum($tests)
        ) {
            $mail = mail(G::$G['Pulse']['contact'], 
                        'Pulse', 
                        $host.' failure: '.$json, 
                        'From: "'.G::$G['VIEW']['_siteName'].'" <'.G::$G['siteEmail'].">"
                        );
            $tests['mail'] = $mail ? 1 : 0;
            $json = json_encode($tests);
        }          
        return $json;
    }
    
    /**
     * wrapper for cURL configured for Pulse
     *
     * @param string $url The URL to request
     *
     * @return string The response text
     */
    protected static function _curl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        $return = trim(curl_exec($ch));
        curl_close($ch);
        
        return $return;
    }
}

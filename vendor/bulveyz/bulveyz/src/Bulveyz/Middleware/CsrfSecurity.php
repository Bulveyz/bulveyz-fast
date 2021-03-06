<?php
/**
 * Created by PhpStorm.
 * User: ruslan
 * Date: 3/16/18
 * Time: 11:42 AM
 */

namespace Bulveyz\Middleware;

/*
 * CSRF Class
 *
 * Organizes the work of protection against CSRF attacks
 */

class CsrfSecurity
{
  public $errors = [];
  
  public function __construct()
  {
    session_start();
  }

  /*
   * Checks the request method, if it's POST, then it will require a CSRF token
   */
  public function methodWather()
  {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      if (!isset($_POST['csrf_token'])) {
        $this->errors[] = 'CSRF token not found!';
      } elseif (!hash_equals($_POST['csrf_token'], $_SESSION['csrf_token'])) {
        $this->errors[] = 'CSRF token not valid!';
      }
      if (!empty($this->errors)) {
        exit(array_shift($this->errors));
      }
    }
  }

  /*
   * Generates a CSRF template for a form field, without it, the POST method will not work
   */
  public static function generateCsrfToken()
  {
    if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } elseif(version_compare(PHP_VERSION, '7.0.0', '<')) {
      if (function_exists('mcrypt_create_iv')) {
        $_SESSION['csrf_token'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
      } else {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
      }
    }
    return "<input id='csrf_token' type='hidden' name='csrf_token' value='{$_SESSION['csrf_token']}'>";
  }
}
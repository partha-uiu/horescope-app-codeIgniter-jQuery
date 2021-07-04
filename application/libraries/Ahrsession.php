<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Ahrsession {

    public $instance = '';
    public $pre_fix = '';
    public $post_fix = '';

    public function __construct() {
        if (!session_id())
            session_start();
    }

    public function mk_name($name = '') {
        return $this->post_fix . $name . $this->post_fix;
    }

    public function resolveSerialize($data = '') {
        $usData = @unserialize($data);
        if ($usData === false)
            return $data;
        else
            return $usData;
    }

    public function get($name, $print = false) {
        if (isset($_SESSION[$this->mk_name($name)])) {
            //$us=  $this->resolveSerialize($_SESSION[$this->mk_name($name)]);
            $us = $_SESSION[$this->mk_name($name)];
            if ($print) {
                echo $us;
            } else {
                return $us;
            }
        } else {
            return false;
        }
    }

    public function set($name, $value = null) {
        if ($name == ('' || NULL))
            return false;
//		if(is_array($value) || is_object($value))
//			$_SESSION[$this->mk_name($name)]=serialize($value);
        else
            $_SESSION[$this->mk_name($name)] = $value;

        return isset($_SESSION[$this->mk_name($name)]) ? true : false;
    }

    public function delete($name) {
        $sessionunset = false;
        if (isset($_SESSION[$this->mk_name($name)])) {
            unset($_SESSION[$this->mk_name($name)]);
            $sessionunset = true;
        }
        return $sessionunset;
    }

    /*
      remove all session of the system
     */

    public function destroy() {
        session_destroy();
        return true;
    }

    /**
     * Used to set a session variable that can be used to output messages in the view.
     *
     * In your controller: $this->ahrsession->set_flash('This has been saved');
     *
     * Additional params below can be passed to customize the output, or the Message.[key].
     * You can also set additional parameters when rendering flash messages. 
     *
     * @param string $message Message to be flashed
     * @param string $element Element to wrap flash message in.
     * @param array $params Parameters to be sent to layout as view variables
     * @param string $key Message key, default is 'flash'
     * @return void
     */
    public function set_flash($message, $element = 'default', $params = array(), $key = 'flash') {
        return $this->set('Message.' . $key, compact('message', 'element', 'params'));
    }

    public function flash($key = 'flash', $attrs = array()) {
        $out = false;

        if ($flash = $this->get('Message.' . $key)) {
            $message = $flash['message'];
            unset($flash['message']);

            if (!empty($attrs)) {
                $flash = array_merge($flash, $attrs);
            }

            if ($flash['element'] === 'default') {
                $class = 'flush-message';
                if (!empty($flash['params']['class'])) {
                    $class = $flash['params']['class'];
                }
                $out = '<div id="' . $key . 'Message" class="' . $class . '">' . $message . '</div>';
            } elseif (!$flash['element']) {
                $out = $message;
            }
            $this->delete('Message.' . $key);
        }
        return $out;
    }

    function generate_token() {
        $token = md5(uniqid(rand(), true));
        $this->set('token', $token);
        return $token;
    }

}
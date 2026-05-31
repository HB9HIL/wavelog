<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Modeswitch extends MY_Controller {

    public function desktop() {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        setcookie('wl_force_desktop', '1', [
            'expires'  => time() + 31536000,
            'path'     => '/',
            'samesite' => 'Lax',
            'secure'   => $secure,
        ]);
        redirect('/');
    }

    public function mobile() {
        setcookie('wl_force_desktop', '', ['expires' => time() - 3600, 'path' => '/']);
        redirect('/mobile');
    }
}

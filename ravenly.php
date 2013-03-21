<?php
namespace Ravenly;

class Ravenly {
    protected static $is_logged_in = false;
    public static $salt = '4sil42dtkazgiun7a05ex6p2cgq0oj32';

    public static function login() {
        if(!Ravenly::is_logged_in) {
            $webauth = new \Ravenly\Lib\Ucam_Webauth(array(
                'key_dir'       => Bundle::path('Ravenly').'keys',
                'cookie_key'    => 'ravenly_k',
                'cookie_name'   => 'ravenly',
                'hostname'      => $_SERVER['HTTP_HOST']
            ));
            $auth = $webauth->authenticate();
            if(!$auth) return false;

            if($webauth->success()) {
                Ravenly::is_logged_in = true;
            }

            Session::set('ucam_webauth_crsid', $webauth->principal());
        }

        return $this->authenticate(Ravenly::getUser());
    }

    public static function logout() {

    }

    public static function authenticate($user) {

    }

    public static function getUser() {
        static $user = null;

        if(is_null($user)) {
            $class = Config::get('ravenly::auth.model') || '\Ravenly\Models\RavenUser';
            $user = new $class(Session::get('ucam_webauth_crsid'));
        }
        
        return $user;
    }
}
?>
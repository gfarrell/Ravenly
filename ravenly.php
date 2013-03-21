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

    public static function authenticate($user, $conditions = array()) {
        // If no user, then fail auth
        if(!$user) return false;

        // Get auth conditions
        $c = Config::get('ravenly::auth.conditions');
        if(!is_array($c)) {
            $c = array_merge($c, $conditions);
        }

        // Check crsid conditions
        if(array_key_exists('crsid', $c) && is_array($c['crsid'])) {
            if(!in_array($user->crsid, $c['crsid'])) return false;
        }

        // Check College conditions
        if(array_key_exists('collegecode', $c) && is_array($c['collegecode'])) {
            if(!in_array($user->collegecode, $c['collegecode'])) return false;
        }

        // Check if in the DB (if necessary)
        if(array_key_exists('force_db', $c)) {
            if(!$user->exists && $c['force_db']) return false;
        }

        // Check user group conditions
        if(array_key_exists('group', $c) && is_array($c['group'])) {
            return $user->inGroups($c['group']));
        }

        // If nothing fails, then all is good
        return true;
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
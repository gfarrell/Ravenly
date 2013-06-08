<?php
namespace Ravenly;

use Log;
use Config;
use Session;
use Bundle;
use Response;
use Ravenly\Errors\AuthException;
use Ravenly\Lib\UcamWebauth;

class Ravenly {
    public static function login() {
        Log::info('Ravenly: logging in.');
        if(!Ravenly::loggedIn()) {
            Log::info('Ravenly: - Instantiating Ucam_Webauth object.');
            $webauth = new \Ravenly\Lib\UcamWebauth(array(
                'key_dir'       => Bundle::path('ravenly').'keys',
                'cookie_key'    => 'ravenly_k',
                'cookie_name'   => 'ravenly',
                'hostname'      => $_SERVER['HTTP_HOST']
            ));

            $auth = $webauth->authenticate();

            if($auth !== true) return $auth;

            if($webauth->success()) {
                Log::info('Ravenly: - webauth authentication successful.');
                Ravenly::loggedIn(true);
            }

            Session::put('ucam_webauth_crsid', $webauth->principal());
        }


        return Ravenly::authenticate(Ravenly::getUser());
    }

    public static function loggedIn($li = null) {
        static $logged_in;

        if(is_null($logged_in)) $logged_in = false;

        if(!is_null($li)) {
            $logged_in = $li;
        }

        return $logged_in;
    }

    public static function logout() {

    }

    public static function authenticate($user, $conditions = array()) {
        Log::info('Ravenly: authenticating.');

        $status = true;

        // If no user, then fail auth
        if(!$user) $status = false;

        // Get auth conditions
        $c = Config::get('ravenly::auth.conditions');
        if(!is_array($c)) {
            $c = array_merge($c, $conditions);
        }

        Log::info('Ravenly: - checking conditions.');
        // Check crsid conditions
        if(array_key_exists('crsid', $c) && is_array($c['crsid'])) {
            if(!in_array($user->crsid, $c['crsid'])) {
                Log::info('Ravenly: ! failed crsid condition.');
                $status = false;
            }
        }

        // Check College conditions
        if(array_key_exists('collegecode', $c) && is_array($c['collegecode'])) {
            if(!in_array($user->collegecode, $c['collegecode'])) {
                Log::info('Ravenly: ! failed college condition.');
                $status = false;
            }
        }

        // Check if in the DB (if necessary)
        if(array_key_exists('force_db', $c)) {
            if(!$user->exists && $c['force_db']) {
                Log::info('Ravenly: ! failed force_db condition.');
                $status = false;
            }
        }

        // Check user group conditions
        if(array_key_exists('group', $c) && is_array($c['group'])) {
            if(!$user->inGroups($c['group'])) {
                Log::info('Ravenly: ! failed group condition.');
                $status = false;
            }
        }

        if($status) {
            Log::info('Ravenly: - authentication successful.');
        } else {
            Log::info('Ravenly: - authentication failed.');
            return Response::error(403);
        }
    }

    public static function getUser() {
        Log::info('Ravenly: fetching user.');
        static $user;

        if(is_null($user)) {
            Log::info('Ravenly: - user not previously set, creating.');
            $class = Config::get('ravenly::auth.model') or 'Models\RavenUser';
            $crsid = Session::get('ucam_webauth_crsid');
            
            // Now we see if we should create a new user, or fetch an old one
            $exists = call_user_func($class.'::where_crsid', $crsid)->count() > 0;
            if(!$exists) {
                Log::info('Ravenly: - user not in database, creating new object.');
                $user = new $class(array('crsid'=>$crsid), false);

                if(Config::get('ravenly::auth.autocreate')) {
                    Log::info('Ravenly: - autocreate set, so saving user.');
                    $user->save();
                }
            } else {
                Log::info('Ravenly: - user exists in database, retrieving.');
                $user = call_user_func($class.'::where_crsid', $crsid)->first();
            }

            $user->fillFromLookup();
        }
        
        return $user;
    }
}
?>
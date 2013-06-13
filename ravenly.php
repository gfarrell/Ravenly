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
    /**
     * Stores the logged in state.
     * @var boolean
     */
    private static $logged_in = false;

    /**
     * Triggers Raven authentication process and then triggers authentication rules.
     * @return boolean whether user is authenticated both on Raven and according to the defined rules.
     */
    public static function login($conditions = array()) {
        static $webauth;
        Log::info('Ravenly: logging in.');

        if(is_null($webauth)) {
            Log::info('Ravenly: - Instantiating Ucam_Webauth object.');
            $webauth = new UcamWebauth(array(
                'key_dir'       => Bundle::path('ravenly').'keys',
                'cookie_key'    => 'Ravenly_Cookie',
                'cookie_name'   => 'Ravenly_Cookie',
                'hostname'      => $_SERVER['HTTP_HOST']
            ));
        }

        $auth = $webauth->authenticate();
        if(!is_bool($auth)) {
            return $auth;
        } else {
            if(!$auth) {
                throw new AuthException($webauth->status() . " " . $webauth->msg());
            }

            if($webauth->success()) {
                Log::info('Ravenly: - webauth authentication successful.');
                Ravenly::setLoggedIn(true);
                Session::put('ucam_webauth_crsid', $webauth->principal());
            } else {
                throw new AuthException('Raven authentication not completed: ' . $webauth->status() . ' ' . $webauth->msg());
            }

            return true;
        }
    }

    /**
     * Returns the logged in state.
     * @return boolean if the user is logged in or not.
     */
    public static function loggedIn() {
        return Ravenly::$logged_in;
    }

    /**
     * Sets the logged in state.
     * @param boolean $li logged in state
     */
    private static function setLoggedIn($li) {
        Ravenly::$logged_in = !!$li;
    }

    /**
     * Triggers the logout process, purges Cookies.
     */
    public static function logout() {

    }

    /**
     * Authenticates the user according to a defined ruleset.
     * @param  RavenUser $user       the user
     * @param  array  $conditions [description]
     * @return [type]             [description]
     */
    public static function authenticate($user, $conditions = array()) {
        Log::info('Ravenly: authenticating.');

        $status = true;

        // If no user, then fail auth
        if(!$user) $status = false;

        // Get auth conditions
        $c = Config::get('ravenly::auth.conditions');
        if(is_array($c)) {
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
            if(!$user->inGroup($c['group'])) {
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

    public static function user() {
        Log::info('Ravenly: fetching user.');

        static $user;

        // first try checking the session for user data
        $sesh_user = Session::get('Ravenly.user');
        if(!is_null($sesh_user)) {
            Log::info('Ravenly: - User found in session, retrieving.');
            $user = $sesh_user;
        }

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

            Log::info('Ravenly: - fetching user details from LDAP.');
            $user->fillFromLookup();

            Session::put('Ravenly.user', $user);
        }
        
        return $user;
    }
}
?>
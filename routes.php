<?php
namespace Ravenly;

use Route;
use Log;

/**
 * Raven login filter.
 * Requires Raven Login and authenticates against default conditions.
 *
 * e.g. $this->filter('before', 'raven');
 */
Route::filter('raven', function() {
    Log::info('Ravenly: raven filter initiated.');
    if(Ravenly::loggedIn()) {
        Log::info('Ravenly: - user already logged in, authenticating.');
    } else {
        Log::info('Ravenly: - user not logged in, logging in.');
        $l_status = Ravenly::login();

        if(!is_bool($l_status)) {
            return $l_status;
        }
        if($l_status === false) {
            Log::info('Ravenly: [!] login failed.');
            return Response::error(403);
        }
    }

    $status = Ravenly::authenticate(Ravenly::user());

    if($status === false) {
        Log::info('Ravenly: [!] not authorised.');
        return Response::error(403);
    } else {
        return $status;
    }
});

/**
 * Raven authentication filter for group requirement.
 * Used if only a particular group should access.
 * Login filter must be called first.
 *
 * e.g. $this->filter('before', 'raven:group', array('admin', 'committee'));
 */
Route::filter('raven:group', function() {
    Log::info('Ravenly: group filter intiated.');
    $groups = func_get_args();

    $status = Ravenly::authenticate(Ravenly::user(), array('group'=>$groups));

    if($status === false) {
        Log::info('Ravenly: [!] not authorised, user not in group(s).');
        return Response::error(403);
    } else {
        return $status;
    }
});
?>
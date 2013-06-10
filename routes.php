<?php
Route::filter('raven', function($conditions = array()) {
    Log::info('Ravenly: raven filter initiated.');
    if(Ravenly\Ravenly::loggedIn()) {
        Log::info('Ravenly: - user already logged in, authenticating.');
    } else {
        Log::info('Ravenly: - user not logged in, logging in.');
        $l_status = Ravenly\Ravenly::login();

        if(!is_bool($l_status)) {
            return $l_status;
        }
        if($l_status === false) {
            Log::info('Ravenly: [!] login failed.');
            return Response::error(403);
        }
    }

    $status = Ravenly\Ravenly::authenticate(Ravenly\Ravenly::user(), $conditions);

    if($status === false) {
        Log::info('Ravenly: [!] not authorised.');
        return Response::error(403);
    } else {
        return $status;
    }
});
?>
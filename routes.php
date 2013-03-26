<?php
Route::filter('raven', function($conditions = array()) {
    Log::info('Ravenly: raven filter initiated.');
    if(Ravenly\Ravenly::loggedIn()) {
        Log::info('Ravenly: - user already logged in, authenticating.');
        $status = Ravenly\Ravenly::authenticate(Ravenly\Ravenly::getUser(), $conditions);
    } else {
        Log::info('Ravenly: - user not logged in, logging in.');
        $status = Ravenly\Ravenly::login();
    }

    if($status === false) {
        Log::info('Ravenly: [!] not authorised.');
        return Response::error(403);
    } else {
        return $status;
    }
});
?>
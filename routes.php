<?php
Route::filter('raven', function($conditions = array()) {
    if(!Ravenly\Ravenly::loggedIn()) {
    Log::info('Ravenly: raven filter initiated.');
        Log::info('Ravenly: - user already logged in, authenticating.');
        $status = Ravenly\Ravenly::authenticate(Ravenly\Ravenly::getUser(), $conditions);
    } else {
        Log::info('Ravenly: - user not logged in, logging in.');
        $status = Ravenly\Ravenly::login();
    }

    if(!$status) {
        Log::info('Ravenly: [!] not authorised.');
        return Response::error(403);
    }
});
?>
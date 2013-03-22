<?php
Route::filter('raven', function($conditions = array()) {
    if(!Ravenly\Ravenly::loggedIn()) {
        $status = Ravenly\Ravenly::authenticate(Ravenly\Ravenly::getUser(), $conditions);
    } else {
        $status = Ravenly\Ravenly::login();
    }

    if(!$status) {
        return Response::error(403);
    }
});
?>
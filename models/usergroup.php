<?php
namespace Ravenly\Models;

use Eloquent;

class UserGroup extends Eloquent {
    public static $table = 'user_groups';

    public function users()
    {
        return $this->has_many_and_belongs_to('Ravenly\Models\User');
    }
}
?>
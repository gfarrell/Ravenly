<?php
namespace Ravenly\Models;

use Eloquent;
use PhpLib\Set;
use Ravenly\Lib\LDAP;

class User extends Eloquent{
    public static $table = 'users';

    /* --- Instance Methods --- */

    /**
     * Returns user's groups
     * @return Relationship the users groups
     */
    public function groups() {
        return $this->has_many_and_belongs_to('Ravenly\Models\UserGroup', 'user_usergroup', 'user_id');
    }

    /**
     * Checks if a user is in a group or groups
     * @param  string|array $groups group name or an array of possible group names
     * @return boolean
     */
    public function inGroup($group) {
        if(!is_array($group)) $group = array($group);
        $gg = Set::extract($this->groups()->get(), '*.attributes.name');

        return (count(array_intersect($gg, $group)) > 0);
    }

    /**
     * Fills fields from LDAP lookup
     * @return void
     */
    public function fillFromLookup() {
        $lookup = User::lookup($this->crsid);
        if(is_array($lookup)) {
            foreach($lookup as $field => $value) {
                $this->$field = $value;
            }
        }
    }

    /* --- Class Methods --- */

    /**
     * Looks up a user by crsid
     * @param  string $crsid user crsid
     * @return array         user properties from ldap
     */
    public static function lookup($crsid) {
        $u = LDAP::search($crsid, 'uid');
        return count($u) > 0 ? $u[0] : null;
    }
}
?>
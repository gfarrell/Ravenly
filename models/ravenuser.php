<?php
namespace Ravenly\Models;

use Eloquent;

class RavenUser extends Eloquent{
    public static $table = 'users';

    /* --- Instance Methods --- */

    /**
     * Returns user's groups
     * @return Relationship the users groups
     */
    public function group() {
        return $this->has_many_and_belongs_to('UserGroup');
    }

    /**
     * Checks if a user is in a group or groups
     * @param  string|array $groups group name or an array of possible group names
     * @return boolean
     */
    public function inGroup($group) {
        if(!is_array($group)) $group = array($group);

        $gg = Set::extract('name', $this->groups()->get());

        return (count(array_intersect($gg, $group)) > 0);
    }

    /**
     * Fills fields from LDAP lookup
     * @return void
     */
    public function fillFromLookup() {
        $lookup = RavenUser::lookup($this->crsid);
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
        $u = \Ravenly\Lib\LDAP::search($crsid, 'uid');
        return count($u) > 0 ? $u[0] : null;
    }
}
?>
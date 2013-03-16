<?php
namespace Ravenly\Models;

class RavenUser extends Eloquent{
    protected function fillFromLookup() {
        $lookup = RavenUser::lookup($crsid);
        if(is_array($lookup)) {
            foreach($lookup as $field => $value) {
                $this->$field = $value;
            }
        }
    }

    public static function lookup($crsid) {
        $u = \Ravenly\Lib\LDAP::search($crsid, 'uid');
        return count($u) > 0 ? $u[0] : null;
    }
}
?>
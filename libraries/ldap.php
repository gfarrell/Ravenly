<?php
namespace Ravenly\Lib;

class LDAP {
    public static function config() {
        return Config::get('ravenly::ldap');
    }
    public static function connect() {
        static $con = null;
        $cfg = LDAP::config();

        if(is_null($con)) {
            $con = ldap_connect($cfg['server']);
        }

        return $con;
    }
    public static function search($query, $field='givenname') {
        $ds = LDAP::connect();
        $cfg = LDAP::config();

        $result = array();

        if ($ds) {
            $resource = ldap_bind($ds);
            $search   = ldap_search($ds, $cfg['base'], "($field=$query)");
            $entries  = ldap_get_entries($ds, $search);
            $prop_map = array(
                'uid'           =>  'crsid',
                'sn'            =>  'surname',
                'cn'            =>  'name',
                'ou'            =>  'college',
                'instid'        =>  'collegecode',
                'displayname'   =>  'display'
            );

            foreach($entries as $i => $r) {
                if(!is_integer($i)) { continue; }
                array_push($result, array_map(function($a) { return $a[0]; }, Set::getMapped($r, $prop_map)));
            }
        } else {
            throw new Exception('Unable to connect to LDAP server: '+ldap_error($ds));
        }

        return $result;
    }
}
?>
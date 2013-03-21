<?php
return array(
    // Authentication conditions
    'conditions'        =>  array(
        // Does a user have to belong to a list of crsids (default: no)
        'crsid'             =>  false,
        
        // Which colleges to allow (e.g. King's Undergraduates)
        'collegecode'       =>  array('KINGSUG'),

        // Does a user have to exist in the database (default: no)
        'force_db'          =>  false,

        // Does the user have to be in a particular user group (default: no)
        'group'             =>  false,
    ),

    // What model class to use
    'model'             =>  '\Ravenly\Models\RavenUser',

    // Automatically create a new User if one isn't found?
    'autocreate'        =>  false
);
?>
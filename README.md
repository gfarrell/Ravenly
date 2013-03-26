Ravenly
=======

Raven (ucam-webauth) authentication for Laravel.

Installation
------------

### Activation

Activate the bundle in `bundles.php`:

    'ravenly' => array(
        'autoloads' => array(
            'namespaces'    =>  array(
                'Ravenly'           =>'(:bundle)',
                'Ravenly\Models'    =>'(:bundle)/models'
            ),
            'directories'   =>  array(
                '(:bundle)/models',
                '(:bundle)/libraries'
            )
        ),
        'auto'  => true
    )

### Configuration

The bundle settings are in `config/auth.php`, and are documented in there.
<?php

return array(
    'doctrine' => array(
        'connection' => array(
            'odm_default' => array(
                'server'           => '10.3.18.48',
                'port'             => '27017',
                'user'             => 'usercuad16',
                'password'         => 'GyAlMy9OKfYpnOQ9tPZUaiPmXF',
                'dbname'           => 'admin',
            ),
        ),

        'configuration' => array(
            'odm_default' => array(
//              'metadata_cache'     => 'array',
                'driver'             => 'odm_default',
                'generate_proxies'   => true,
                'proxy_dir'          => 'data/DoctrineMongoODMModule/Proxy',
                'proxy_namespace'    => 'DoctrineMongoODMModule\Proxy',
                'generate_hydrators' => true,
                'hydrator_dir'       => 'data/DoctrineMongoODMModule/Hydrator',
                'hydrator_namespace' => 'DoctrineMongoODMModule\Hydrator',
//              'default_db'         => test,
//               'filters'            => array(),  // array('filterName' => 'BSON\Filter\Class'),
//               'logger'             => null // 'DoctrineMongoODMModule\Logging\DebugStack'
            )
        ),

        'driver' => array(
            'odm_driver' => array(
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'paths' => array(__DIR__ . '/../../module/Application/src/Application/Document')
            ),
            'odm_default' => array(
                'drivers' => array(
                    'Application\Document' => 'odm_driver'
                )
            ),
        ),
        
        'documentmanager' => array(
            'odm_default' => array(
//                'connection'    => 'odm_default',
//                'configuration' => 'odm_default',
//                'eventmanager' => 'odm_default'
            )
        ),

        'eventmanager' => array(
            'odm_default' => array(
                'subscribers' => array()
            )
        ),
    ),
);
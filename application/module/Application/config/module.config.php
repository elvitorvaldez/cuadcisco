<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            
           
            
            
            
            'login' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/login',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Users',
                        'action' => 'login',
                    ),
                ),
            ),
            

            
            'logout' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/logout',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Users',
                        'action' => 'logout',
                    ),
                ),
            ),
            
            
            'controlpanel' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/controlpanel',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Users',
                        'action' => 'controlpanel',
                    ),
                ),
            ),
            
            
                  'getuserapps' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/getuserapps',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Users',
                        'action' => 'getuserapps',
                    ),
                ),
            ),
            
            
            'set-email' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/setEmail',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Users',
                        'action' => 'setEmail',
                    ),
                ),
            ),
            
            
            
             'save-user-apps' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/saveUserApps',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Users',
                        'action' => 'saveUserApps',
                    ),
                ),
            ),
            
            
            
            'change-password' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/changePassword',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Users',
                        'action' => 'changePassword',
                    ),
                ),
            ),
            
                
            'reset-pass' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/resetPassword',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Users',
                        'action' => 'resetPassword',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'apply-reset' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/[:token]',
                            'constraints' => array(
                                'token' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$'
                            ),
                            'defaults' => array(
                                'action' => 'applyReset',
                            ),
                        )
                    )
                )
            ),
            
            'error' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/error',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'error',
                    ),
                ),
            ),


             'gotoapp' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/functions/[:action][/:app]',
                    'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                         'app'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Functions',
                        'action' => 'gotoapp',
                    ),
                ),
            ),
            
            'gotodash' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/functions/[:action][/:app]',
                    'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                         'app'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                     ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Functions',
                        'action' => 'gotodash',
                    ),
                ),
            ),
 
            
            'reports' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/reports',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Reports',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route' => '/[:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'default' => array(
                            )
                        ),
                    ),
                ),
            ),
        ),
    ),
    
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'factories' => array(
            'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory',
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
            'Users' => 'Application\ControllerFactory\UsersFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'factories' => array(
            'Usuarios' => 'Application\ControllerFactory\UsuariosFactory',			
		) ,
        
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\Crons' => 'Application\Controller\CronsController',
            'Application\Controller\Reports' => 'Application\Controller\ReportsController',
            'Application\Controller\Users' => 'Application\Controller\UsersController',
            'Application\Controller\Functions' => 'Application\Controller\FunctionsController'
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'             => __DIR__ . '/../view/layout/layout.phtml',
            'layout/form'              => __DIR__ . '/../view/layout/layout_form.phtml',
            'layout_tables/form'              => __DIR__ . '/../view/layout/layout_tables.phtml',
            'application/index/index'   => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'                 => __DIR__ . '/../view/error/404.phtml',
            'error/index'               => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        )
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
                'remove-xlsx' => array(
                    'options' => array(
                        'route' => 'remove xlsx <token>',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Crons',
                            'action' => 'removeXLSX'
                        )
                    )
                ),
                'clean-reset-pwd' => array(
                    'options' => array(
                        'route' => 'clean resetpwd <token>',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Crons',
                            'action' => 'cleanRequestOfResetPassword'
                        )
                    )
                ),
                'notify-reset-pwd' => array(
                    'options' => array(
                        'route' => 'notify resetpwd <token>',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Crons',
                            'action' => 'sendNotifyOfResetPassword'
                        )
                    )
                ),
            ),
        ),
    ),
);

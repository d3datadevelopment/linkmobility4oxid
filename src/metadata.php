<?php
/**
 * Metadata version
 */

use D3\Linkmobility4OXID\Application\Controller\Admin\AdminUser;

$sMetadataVersion = '2.1';
$sModuleId        = 'd3linkmobility';
$sD3Logo          = '<img src="https://logos.oxidmodule.com/d3logo.svg" alt="(D3)" style="height:1em;width:1em"> ';

/**
 * Module information
 */
$aModule = [
    'id'           => $sModuleId,
    'title'        => $sD3Logo . ' Linkmobility',
    'description'  => [
        'de'    =>  'Anbiundung an die Linkmobility API <ul><li>Nachrichtenversand per SMS</li></ul>',
        'en'    =>  '',
    ],
    'thumbnail'    => 'picture.png',
    'version'      => '0.1',
    'author'       => 'D&sup3; Data Development (Inh.: Thomas Dartsch)',
    'email'        => 'support@shopmodule.com',
    'url'          => 'https://www.oxidmodule.com/',
    'extend'       => [
        \OxidEsales\Eshop\Application\Controller\StartController::class => \D3\Linkmobility4OXID\Modules\Application\Controller\StartController::class,
        \OxidEsales\Eshop\Application\Controller\ContactController::class   => \D3\Linkmobility4OXID\Modules\Application\Controller\ContactController::class
    ],
    'controllers'  => [
        'd3linkmobility_user'   => AdminUser::class
    ],
    'templates'    => [
        'd3adminuser.tpl'       => 'd3/linkmobility/Application/views/admin/tpl/adminuser.tpl'
    ],
    'events'       => [],
    'settings'     => [
        [
            'group'     => $sModuleId.'_general',
            'name'      => $sModuleId.'_debug',
            'type'      => 'bool',
            'value'     => false
        ],
        [
            'group'     => $sModuleId.'_general',
            'name'      => $sModuleId.'_apitoken',
            'type'      => 'str',
            'value'     => false
        ],
        [
            'group'     => $sModuleId.'_general',
            'name'      => $sModuleId.'_smsSenderNumber',
            'type'      => 'str',
            'value'     => false
        ],
        [
            'group'     => $sModuleId.'_general',
            'name'      => $sModuleId.'_smsSenderCountry',
            'type'      => 'str',
            'value'     => 'DE'
        ]
    ]
];
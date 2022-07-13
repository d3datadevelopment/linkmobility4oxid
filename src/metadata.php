<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * https://www.d3data.de
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link      https://www.oxidmodule.com
 */

declare(strict_types=1);

use D3\Linkmobility4OXID\Application\Controller\Admin\AdminOrder;
use D3\Linkmobility4OXID\Application\Controller\Admin\AdminUser;
use D3\Linkmobility4OXID\Modules\Application\Model\OrderModel;
use D3\Linkmobility4OXID\Modules\Core\EmailCore;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Email;

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
        'de'    =>  'Anbindung an die Linkmobility API <ul><li>Nachrichtenversand per SMS</li></ul>',
        'en'    =>  '',
    ],
    'thumbnail'    => 'picture.png',
    'version'      => '0.1',
    'author'       => 'D&sup3; Data Development (Inh.: Thomas Dartsch)',
    'email'        => 'support@shopmodule.com',
    'url'          => 'https://www.oxidmodule.com/',
    'extend'       => [
        Email::class => EmailCore::class,
        Order::class => OrderModel::class
    ],
    'controllers'  => [
        'd3linkmobility_user'   => AdminUser::class,
        'd3linkmobility_order'  => AdminOrder::class
    ],
    'templates'    => [
        'd3adminuser.tpl'           => 'd3/linkmobility/Application/views/admin/tpl/adminuser.tpl',
        'd3adminorder.tpl'          => 'd3/linkmobility/Application/views/admin/tpl/adminuser.tpl',
        'd3sms_ordercust.tpl'       => 'd3/linkmobility/Application/views/tpl/SMS/order_cust.tpl',
        'd3sms_sendednow.tpl'       => 'd3/linkmobility/Application/views/tpl/SMS/sendednow.tpl',
        'd3sms_ordercanceled.tpl'   => 'd3/linkmobility/Application/views/tpl/SMS/ordercanceled.tpl',
    ],
    'events'       => [],
    'blocks'        => [
        [
            'template'  => 'order_remark.tpl',
            'block'     => 'admin_order_remark_type',
            'file'      => 'Application/views/blocks/admin/admin_user_remark_type.tpl',
        ],
        [
            'template'  => 'user_remark.tpl',
            'block'     => 'admin_user_remark_type',
            'file'      => 'Application/views/blocks/admin/admin_user_remark_type.tpl',
        ]
    ],
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
            'group'     => $sModuleId.'_sms',
            'name'      => $sModuleId.'_smsSenderNumber',
            'type'      => 'str',
            'value'     => false
        ],
        [
            'group'     => $sModuleId.'_sms',
            'name'      => $sModuleId.'_smsSenderCountry',
            'type'      => 'str',
            'value'     => 'DE'
        ],
        [
            'group'     => $sModuleId.'_sms',
            'name'      => $sModuleId.'_smsUserRecipientsFields',
            'type'      => 'arr',
            'value'     => [],
        ],
        [
            'group'     => $sModuleId.'_sms',
            'name'      => $sModuleId.'_smsOrderRecipientsFields',
            'type'      => 'aarr',
            'value'     => [],
        ],
        [
            'group'     => $sModuleId.'_trigger',
            'name'      => $sModuleId.'_orderActive',
            'type'      => 'bool',
            'value'     => false
        ],
        [
            'group'     => $sModuleId.'_trigger',
            'name'      => $sModuleId.'_sendedNowActive',
            'type'      => 'bool',
            'value'     => false
        ],
        [
            'group'     => $sModuleId.'_trigger',
            'name'      => $sModuleId.'_cancelOrderActive',
            'type'      => 'bool',
            'value'     => false
        ]
    ]
];

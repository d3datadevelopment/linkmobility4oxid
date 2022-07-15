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

$sLangName = "English";
// -------------------------------
// RESOURCE IDENTITFIER = STRING
// -------------------------------
$aLang = [

//Navigation
    'charset'                                           => 'UTF-8',

    'SHOP_MODULE_GROUP_d3linkmobility_general'          => 'Basic settings',
    'SHOP_MODULE_d3linkmobility_debug'                  => 'Test mode',
    'HELP_SHOP_MODULE_d3linkmobility_debug'             => 'With test mode enabled, Linkmobility will not send the messages. However, the request is processed and returns a system response.',
    'SHOP_MODULE_d3linkmobility_apitoken'               => 'API token',
    'HELP_SHOP_MODULE_d3linkmobility_apitoken'          => 'Please generate the API token in your Linkmobility account.',

    'SHOP_MODULE_GROUP_d3linkmobility_sms'              => 'Sending SMS',
    'SHOP_MODULE_d3linkmobility_smsSenderNumber'        => 'Sender number',
    'HELP_SHOP_MODULE_d3linkmobility_smsSenderNumber'   => 'Your mobile phone number, which will be used as the sender. Please have the desired telephone number stored in your account. If only one number is stored there, you do not need to enter anything here. In this case, the telephone number from your account will be used automatically.',
    'SHOP_MODULE_d3linkmobility_smsSenderCountry'       => 'Country code',
    'HELP_SHOP_MODULE_d3linkmobility_smsSenderCountry'  => 'Enter the country code (ISO alpha-2, e.g. DE, AT, FR) to which your mobile phone number is assigned. This information is only necessary if you have entered a sender number.',
    'SHOP_MODULE_d3linkmobility_smsUserRecipientsFields'=> 'User account fields checked for valid mobile numbers',
    'HELP_SHOP_MODULE_d3linkmobility_smsUserRecipientsFields'   => 'The fields are checked in this order and the first valid occurrence is used to send the message. Without (valid) field specification, the fields "oxmobfon", "oxfon" and "oxprivfon" are checked.',
    'SHOP_MODULE_d3linkmobility_smsOrderRecipientsFields'=> 'Fields of the order that are checked for valid mobile phone numbers and associated country identifiers',
    'HELP_SHOP_MODULE_d3linkmobility_smsOrderRecipientsFields'  => 'The fields are checked in this order and the first valid occurrence is used to send the message. Without (valid) field specification, the fields "oxdelfon" and "oxbillfon" are checked. For the telephone number field, the corresponding country ID field is required. Individual specifications are given in this format:<br><div>Phone number field => country ID field</div><div>e.g.: "oxdelfon => oxdelcountryid"</div>',

    'SHOP_MODULE_GROUP_d3linkmobility_trigger'          => 'Sending messages with ...',
    'SHOP_MODULE_d3linkmobility_orderActive'            => 'finished order',
    'HELP_SHOP_MODULE_d3linkmobility_orderActive'       => 'If an order was successfully completed in the frontend, the SMS message is also sent to the customer to send the order confirmation mail. The content of the SMS notification can be found in the template "Application/views/tpl/SMS/order_cust.tpl".',
    'SHOP_MODULE_d3linkmobility_sendedNowActive'        => 'shipping notification',
    'HELP_SHOP_MODULE_d3linkmobility_sendedNowActive'   => 'If the delivery date is set in the shop backend and this is communicated to the customer by e-mail (separate checkbox), the information is also sent via SMS. The content of the SMS notification can be found in the template "Application/views/tpl/SMS/sendednow.tpl".',
    'SHOP_MODULE_d3linkmobility_cancelOrderActive'      => 'Order cancellation',
    'HELP_SHOP_MODULE_d3linkmobility_cancelOrderActive' => 'When cancelling the order, the customer receives an information by SMS. The content of the SMS notification can be found in the template "Application/views/tpl/SMS/ordercanceled.tpl".',

    'D3LM_ADMIN_USER_RECIPIENT'                         => 'Recipient number',
    'D3LM_ADMIN_USER_MESSAGE'                           => 'Message',
    'D3LM_ADMIN_SEND'                                   => 'submit',

    'D3LM_EXC_MESSAGE_NO_LENGTH'                        => 'The message must have content',
    'D3LM_EXC_SMS_SUCC_SENT'                            => 'The message was sent successfully. (%1$s message(s) used)',
    'D3LM_EXC_MESSAGE_UNEXPECTED_ERR_SEND'              => 'This error occurred while sending the message(s): %1$s',
    'D3LM_EXC_NO_RECIPIENT_SET'                         => 'No (usable) recipient number is set.',

    'D3LM_REMARK_SMS'                                   => 'SMS message',

    'tbcluser_linkmobility'                             => 'sending SMS',
    'tbclorder_linkmobility'                            => 'sending SMS'
];

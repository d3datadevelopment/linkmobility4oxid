<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * https://www.d3data.de
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <info@shopmodule.com>
 * @link      https://www.oxidmodule.com
 */

declare(strict_types=1);

$sLangName = "Deutsch";
// -------------------------------
// RESOURCE IDENTITFIER = STRING
// -------------------------------
$aLang = [

//Navigation
    'charset'                                           => 'UTF-8',

    'SHOP_MODULE_GROUP_d3linkmobility_general'          => 'Grundeinstellungen',
    'SHOP_MODULE_d3linkmobility_debug'                  => 'Test-Modus',
    'HELP_SHOP_MODULE_d3linkmobility_debug'             => 'Mit aktiviertem Test-Modus wird Linkmobility die Nachrichten nicht versenden. Die Anfrage wird jedoch verarbeitet und liefert eine Systemantwort zurück.',
    'SHOP_MODULE_d3linkmobility_apitoken'               => 'API-Token',
    'HELP_SHOP_MODULE_d3linkmobility_apitoken'          => 'Den API-Token generieren Sie sich bitte in Ihrem Linkmobility-Konto.',

    'SHOP_MODULE_GROUP_d3linkmobility_sms'              => 'SMS-Versand',
    'SHOP_MODULE_d3linkmobility_smsSenderNumber'        => 'Sendernummer',
    'HELP_SHOP_MODULE_d3linkmobility_smsSenderNumber'   => 'Ihre Mobilfunknummer, die als Antwortziel verwendet werden kann., Bitte lassen Sie die gewünschte Telefonnummer in Ihrem Konto hinterlegen.',
    'SHOP_MODULE_d3linkmobility_smsSenderCountry'       => 'Landeskürzel',
    'HELP_SHOP_MODULE_d3linkmobility_smsSenderCountry'  => 'Geben Sie hier das Landeskürzel (ISO-Alpha-2, z.B. DE, AT, FR) an, zu dem Ihre Mobilfunknummer zugeordnet ist.',
    'SHOP_MODULE_d3linkmobility_smsUserRecipientsFields'=> 'Felder des Benutzerkontos, die auf gültige Mobilfunknummern geprüft werden',
    'HELP_SHOP_MODULE_d3linkmobility_smsUserRecipientsFields'   => 'Die Felder werden in dieser Reihenfolge geprüft und das erste valide Vorkommen wird zum Senden der Nachricht verwendet. Ohne (gültige) Feldangabe werden die Felder "oxmobfon", "oxfon" und "oxprivfon" geprüft.',
    'SHOP_MODULE_d3linkmobility_smsOrderRecipientsFields'=> 'Felder der Bestellung, die auf gültige Mobilfunknummern und zugehörige Länderident geprüft werden',
    'HELP_SHOP_MODULE_d3linkmobility_smsOrderRecipientsFields'  => 'Die Felder werden in dieser Reihenfolge geprüft und das erste valide Vorkommen wird zum Senden der Nachricht verwendet. Ohne (gültige) Feldangabe werden die Felder "oxdelfon", und "oxbillfon" geprüft. Zum Telefonnummernfeld ist die Angabe des dazugehörigen Landesidentfeldes erforderlich. Individuelle Angaben erfolgen in diesem Format:<br><div>Telefonnummernfeld => LänderIdFeld</div><div>Bsp.: "oxdelfon => oxdelcuntryid"</div>',

    'SHOP_MODULE_GROUP_d3linkmobility_trigger'          => 'Nachrichtenversand bei ...',
    'SHOP_MODULE_d3linkmobility_orderActive'            => 'abgeschlossener Bestellung',
    'HELP_SHOP_MODULE_d3linkmobility_orderActive'       => 'Wurde eine Bestellung im Frontend erfolgreich abgeschlossen, wird zum Versand der Bestellbestätigungsmail an den Kunden auch die SMS-Nachricht verschickt. Den Inhalt der SMS-Benachrichtigung finden Sie im Template "Application/views/tpl/SMS/order_cust.tpl".',
    'SHOP_MODULE_d3linkmobility_sendedNowActive'        => 'Benachrichtigung über Versand',
    'HELP_SHOP_MODULE_d3linkmobility_sendedNowActive'   => 'Wird im Shopbackend das Versanddatum gesetzt und dieses per Mail an den Kunden mitgeteilt (separate Checkbox), erfolgt gleichermaßen die Information via SMS. Den Inhalt der SMS-Benachrichtigung finden Sie im Template "Application/views/tpl/SMS/sendednow.tpl".',
    'SHOP_MODULE_d3linkmobility_cancelOrderActive'      => 'Bestellstornierung',
    'HELP_SHOP_MODULE_d3linkmobility_cancelOrderActive' => 'Beim Stornieren der Bestellung erhält der Kunde eine Information per SMS. Den Inhalt der SMS-Benachrichtigung finden Sie im Template "Application/views/tpl/SMS/ordercanceled.tpl".',

    'D3LM_ADMIN_USER_RECIPIENT'                         => 'Empfängernummer',
    'D3LM_ADMIN_USER_MESSAGE'                           => 'Nachricht',
    'D3LM_ADMIN_SEND'                                   => 'versenden',

    'D3LM_EXC_MESSAGE_NO_LENGTH'                        => 'Die Mitteilung muss Inhalt haben',
    'D3LM_EXC_SMS_SUCC_SENT'                            => 'Die Mitteilung wurde erfolgreich versendet. (%1$s Nachricht(en) verwendet)',
    'D3LM_EXC_MESSAGE_UNEXPECTED_ERR_SEND'              => 'Beim Versenden der Nachricht(en) ist dieser Fehler aufgetreten: %1$s',
    'D3LM_EXC_NO_RECIPIENT_SET'                         => 'Kein (verwendbarer) Empfänger gesetzt.',

    'D3LM_REMARK_SMS'                                   => 'SMS-Nachr.',

    'tbcluser_linkmobility'                             => 'SMS-Versand',
    'tbclorder_linkmobility'                            => 'SMS-Versand'
];

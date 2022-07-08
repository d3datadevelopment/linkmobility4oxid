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
    'SHOP_MODULE_d3linkmobility_debug'                  => 'Debug-Modus',
    'HELP_SHOP_MODULE_d3linkmobility_debug'             => 'Mit aktiviertem Test-Modus wird Linkmobility die Nachrichten nicht versenden. Die Anfrage wird jedoch verarbeitet und liefert eine Systemantwort zurück.',
    'SHOP_MODULE_d3linkmobility_apitoken'               => 'API-Token',
    'HELP_SHOP_MODULE_d3linkmobility_apitoken'          => 'Den API-Token generieren Sie sich bitte in Ihrem Linkmobility-Konto.',

    'SHOP_MODULE_GROUP_d3linkmobility_sms'              => 'SMS-Versand',
    'SHOP_MODULE_d3linkmobility_smsSenderNumber'        => 'Sendernummer',
    'HELP_SHOP_MODULE_d3linkmobility_smsSenderNumber'   => 'Ihre Mobilfunknummer, die als Antwortziel verwendet werden kann.',
    'SHOP_MODULE_d3linkmobility_smsSenderCountry'       => 'Landeskürzel',
    'HELP_SHOP_MODULE_d3linkmobility_smsSenderCountry'  => 'Geben Sie hier das Landeskürzel (ISO-Alpha-2, z.B. DE, AT, FR) an, zu dem Ihre Mobilfunknummer zugeordnet ist.',
    'SHOP_MODULE_d3linkmobility_smsUserRecipientsFields'=> 'Felder des Benutzerkontos, die auf gültige Mobilfunknummern geprüft werden',
    'HELP_SHOP_MODULE_d3linkmobility_smsUserRecipientsFields'   => 'Die Felder werden in dieser Reihenfolge geprüft und das erste valide Vorkommen wird zum Senden der Nachricht verwendet. Ohne (gültige) Feldangabe werden die Felder "oxmobfon", "oxfon" und "oxprivfon" geprüft.',
    'SHOP_MODULE_d3linkmobility_smsOrderRecipientsFields'=> 'Felder der Bestellung, die auf gültige Mobilfunknummern und zugehörige Länderident geprüft werden',
    'HELP_SHOP_MODULE_d3linkmobility_smsOrderRecipientsFields'  => 'Die Felder werden in dieser Reihenfolge geprüft und das erste valide Vorkommen wird zum Senden der Nachricht verwendet. Ohne (gültige) Feldangabe werden die Felder "oxdelfon", und "oxbillfon" geprüft. Zum Telefonnummernfeld ist die Angabe des dazugehörigen Landesidentfeldes erforderlich. Individuelle Angaben erfolgen in diesem Format:<br><div>Telefonnummernfeld => LänderIdFeld</div><div>Bsp.: "oxdelfon => oxdelcuntryid"</div>',

    'SHOP_MODULE_GROUP_d3linkmobility_trigger'          => 'Nachrichtenversand bei ...',
    'SHOP_MODULE_d3linkmobility_orderActive'            => 'abgeschlossener Bestellung',

    'D3LM_ADMIN_USER_RECIPIENT'                         => 'Empfängernummer',
    'D3LM_ADMIN_USER_MESSAGE'                           => 'Nachricht',
    'D3LM_ADMIN_SEND'                                   => 'versenden',

    'D3LM_EXC_MESSAGE_NO_LENGTH'                        => 'Die Mitteilung muss Inhalt haben',
    'D3LM_EXC_SMS_SUCC_SENT'                            => 'Die Mitteilung wurde erfolgreich versendet. (%1$s Nachricht(en) verwendet)',
    'D3LM_EXC_MESSAGE_UNEXPECTED_ERR_SEND'              => 'Beim Versenden der Nachricht(en) ist ein unerwarteter Fehler aufgetreten.',
    'D3LM_EXC_NO_RECIPIENT_SET'                         => 'Kein (verwendbarer) Empfänger gesetzt.',

    'D3LM_REMARK_SMS'                                   => 'SMS-Nachr.',

    'tbcluser_linkmobility'                             => 'SMS-Versand',
    'tbclorder_linkmobility'                            => 'SMS-Versand'
];

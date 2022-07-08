[{assign var="shop" value=$oEmailView->getShop()}]

Hallo [{$order->getFieldData('oxbillfname')}] [{$order->getFieldData('oxbilllname')}],

Ihre Bestellung [{$order->oxorder__oxordernr->value}] wurde eben versendet. [{if $order->getFieldData('oxtrackcode')}]Der Trackingcode dazu ist: [{$order->getFieldData('oxtrackcode')}].[{/if}]

Ihr Team von [{$shop->getFieldData('oxname')}].
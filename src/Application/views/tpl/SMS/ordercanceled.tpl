[{assign var="shop" value=$oEmailView->getShop()}]

Hallo [{$order->getFieldData('oxbillfname')}] [{$order->getFieldData('oxbilllname')}],

Ihre Bestellung [{$order->oxorder__oxordernr->value}] wurde storniert.

Ihr Team von [{$shop->getFieldData('oxname')}].


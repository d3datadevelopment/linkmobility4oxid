[{assign var="shop" value=$oEmailView->getShop()}]

Hallo [{$order->getFieldData('oxbillfname')}] [{$order->getFieldData('oxbilllname')}],

vielen Dank für Ihre Bestellung. Wir haben diese unter der Bestellnummer [{$order->oxorder__oxordernr->value}] angelegt und werden diese schnellstmöglich bearbeiten.

Ihr Team von [{$shop->getFieldData('oxname')}].
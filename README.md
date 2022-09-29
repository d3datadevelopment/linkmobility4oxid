[![deutsche Version](https://logos.oxidmodule.com/de2_xs.svg)](README.md)
[![english version](https://logos.oxidmodule.com/en2_xs.svg)](README.en.md)

# Integration des LINK Mobility Dienstes in den OXID eShop

[LINK Mobility](https://www.linkmobility.de/) stellt einen Service zum Versenden von mobilen Nachrichten (SMS, Whatsapp, RCS, Chatbot, ...) zur Verfügung.

Dieses Paket stellt den Service innerhalb der Shopfunktionen zur Verfügung. 

Bitte beachten Sie, dass zur Verwendung des Moduls ein LINK Mobility Konto nötig ist.

## Features

Bei folgenden Aktionen kann der Nachrichtenversand (derzeit SMS) einzeln aktiviert werden:

- Bestellabschluss, Versand der Bestellbestätigungsnachricht
- Versendebenachrichtigung
- Stornierbenachrichtigung

- Versenden individueller Nachricht aus dem Adminbereich des Shops an Kontaktdaten aus dem Kundenkonto
- Versenden individueller Nachricht aus dem Adminbereich des Shops an Kontaktdaten aus bestehender Bestellung

## Systemanforderungen

Dieses Paket erfordert einen mit Composer installierten OXID eShop in einer der folgenden Versionen:

- 6.2.4 oder höher
- 6.3.x
- 6.4.x
- 6.5.x

und dessen Anforderungen.

## Erste Schritte

```
composer require d3/linkmobility4oxid
```

Aktivieren Sie das Modul im Shopadmin unter "Erweiterungen -> Module".

Die nötige Konfiguration finden Sie im selben Bereich im Tab "Einstell.".

## Changelog

Siehe [CHANGELOG](CHANGELOG.md) für weitere Informationen.

## Beitragen

Wenn Sie eine Verbesserungsvorschlag haben, legen Sie einen Fork des Repositories an und erstellen Sie einen Pull Request. Alternativ können Sie einfach ein Issue erstellen. Fügen Sie das Projekt zu Ihren Favoriten hinzu. Vielen Dank.

- Erstellen Sie einen Fork des Projekts
- Erstellen Sie einen Feature Branch (git checkout -b feature/AmazingFeature)
- Fügen Sie Ihre Änderungen hinzu (git commit -m 'Add some AmazingFeature')
- Übertragen Sie den Branch (git push origin feature/AmazingFeature)
- Öffnen Sie einen Pull Request

## Support

Bei Fragen zum *Messaging Service* und dessen *Verträgen* kontaktieren Sie bitte das [LINK Mobility Team](https://www.linkmobility.de/kontakt).

Zu *technischen Anfragen* finden Sie die Kontaktmöglichkeiten in der [composer.json](composer.json).

## Lizenz
(Stand: 13.07.2022)

Vertrieben unter der GPLv3 Lizenz.

```
Copyright (c) D3 Data Development (Inh. Thomas Dartsch)

Diese Software wird unter der GNU GENERAL PUBLIC LICENSE Version 3 vertrieben.
```

Die vollständigen Copyright- und Lizenzinformationen entnehmen Sie bitte der [LICENSE](LICENSE.md)-Datei, die mit diesem Quellcode verteilt wurde.
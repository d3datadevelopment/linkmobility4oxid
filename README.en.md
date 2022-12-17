[![deutsche Version](https://logos.oxidmodule.com/de2_xs.svg)](README.md)
[![english version](https://logos.oxidmodule.com/en2_xs.svg)](README.en.md)

# Integration of the LINK Mobility service into the OXID eShop

[LINK Mobility](https://www.linkmobility.de/) provides a service for sending mobile messages (SMS, Whatsapp, RCS, Chatbot, ...).

This package provides the service within the shop functions.

Please note that a LINK Mobility account is required to use the module.

## Features

Message dispatch (currently SMS) can be activated individually for the following actions:

- Order completion, sending order confirmation message.
- Sended now message
- Storno message

- Sending individual message from the admin area of the shop to contact data from the customer account
- Sending individual message from the admin area of the shop to contact data from existing order

## System requirements

This package requires an OXID eShop installed with Composer in one of the following versions:

- 6.2.4 or above
- 6.3.1 or above
- 6.4.x
- 6.5.x

and its requirements.

## Getting Started

```
composer require d3/linkmobility4oxid
```

Activate the module in the admin area of the shop in "Extensions -> Modules".

The necessary configuration can be found in the same area in the "Settings" tab.

## Changelog

See [CHANGELOG](CHANGELOG.md) for further informations.

## Contributing

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue. Don't forget to give the project a star! Thanks again!

- Fork the Project
- Create your Feature Branch (git checkout -b feature/AmazingFeature)
- Commit your Changes (git commit -m 'Add some AmazingFeature')
- Push to the Branch (git push origin feature/AmazingFeature)
- Open a Pull Request

## Support

If you have any questions about the *messaging service* and its *contracts*, please contact the [LINK Mobility Team](https://www.linkmobility.de/kontakt).

For *technical inquiries* you will find the contact options in the [composer.json](composer.json).

## License
(status: 2022-07-13)

Distributed under the GPLv3 license.

```
Copyright (c) D3 Data Development (Inh. Thomas Dartsch)

This software is distributed under the GNU GENERAL PUBLIC LICENSE version 3.
```

For full copyright and licensing information, please see the [LICENSE](LICENSE.md) file distributed with this source code.
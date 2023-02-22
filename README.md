## About PrestaShop-modules

This repository is used by several tools as a source of truth where the PrestaShop project modules are listed.

The composer.json used by [prestashop/prestashop](https://github.com/prestashop/prestashop) does not contain all of them, for example autoupgrade module.

One usage example is the Add-ons API (addons.prestashop.com), it uses this list to monitor project modules releases and distribute them. This API was used by PrestaShop 1.6 and 1.7 to distribute module releases, before being replaced by [the Distribution API](https://github.com/prestashop/distribution-api/) in PrestaShop 8

### Warning

Do not remove a module from here unless you know exactly what you're doing. It would be a good idea to ask the Add-ons team before going forward with deletion.
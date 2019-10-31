# Classic-models 

## Installeer Magento

Indien je problemen hebt met Apache/Nginx, ga als volgt te werk:
Check je DNS (https://devdns.xsarus.net/lijst) met je IP adres (settings\network). Indien je DNS niet goed staat, aanpassen. Ga vervolgens naar de database van je project naar de tabel core_config_data en zoek naar web/secure/base_url en web/secure/base_url . Pas hier de inhoud van kolom value aan naar {{base_url}}. vervolgens cache cleanen en exact de juiste url invullen in je webbrowser. 

### Instellen docker-dev-env

docker-dev-env stelt verschillende docker containers in om gebruik te kunnen maken van de omgeving.  (https://git.xsarus.net/xsa/docker-dev-env) 
Let op dat de Magento nginx erbij geinstalleerd moet worden.

### Je hosts file aanpassen

Bash 
* /etc/hosts
* 127.0.0.1   classic-models.nql-72.at.xsar.us

### Shell script maken om installatie uit te voeren

Via Text Editor een file maken met als inhoud (deze magento wordt op php versie 7.2 gemaakt via een command vanuit docker-dev-env.)


Bash:
* chmod +x magento2.sh
* ./magento2.sh

php72 bin/magento setup:install 
--base-url=https://classic-models.nql-72.at.xsar.us/ 
--db-host=mysql57-2 
--db-name=classic-models 
--db-user=xsarus 
--db-password=xsarus 
--admin-firstname=admin 
--admin-lastname=admin 
--admin-email=admin@hotmail.com 
--admin-user=admin 
--admin-password=Heineken!!1 
--language=en_US 
--currency=USD 
--timezone=America/Chicago 
--use-rewrites=1

### Basis inrichting van de shop goedzetten 




### Env files opzetten

* Bash cp /app/etc/env.php  env.php.local 
* Bash cp /app/etc/env.php  env.php.production 
* Bash cp /app/etc/env.php  env.php.staging

## Maak een eigen thema 
https://devdocs.magento.com/guides/v2.3/frontend-dev-guide/themes/theme-create.html 

Creëer de volgende directories:
* app/frontend/Xsarus/classicmodels/registration.php
* app/frontend/Xsarus/classicmodels/theme.xml
* etc/view.xml
* Magento_Theme/layout/default.xml (logo)
* web/images/preview-image.jpg

Ga naar je Admin pagina en kies voor Content/Design/Configuration/Edit/Applied Theme/classicmodels en druk op Save Configuration

### Zorg via Less / Css dat de kleuren van het thema passen bij het logo
https://magento.stackexchange.com/questions/108685/how-to-add-a-custom-css-file-in-magento-2

Zorg dat de volgende files goed staan: 
* cp grunt-config.json.sample grunt-config.json
* cp gruntfile.js.sample gruntfile.js
* cp package.json.sample package.json

Pas het pad aan in grunt-config.json naar: "app/design/frontend/Xsarus/classicmodels/themes.js"

Creëer de volgende file:
* app/design/frontend/Xsarus/classicmodels/themes.js

Vul deze file a.h.v. een andere webshop(dlk/rol/tsf)

Creëer de volgende directories:
* app/frontend/Xsarus/classicmodels/web/css/source/_theme.less

Vul deze file met css/less code.

Zorg dat npm en grunt het op je directory doen.

## API voor ERP maken
Indien postman het niet doet op je magento installatie, ga in het programma Postman naar file /settings en schakel SSL certificate verification uit.

## Catalogus voorbereiden

### Maak een Magento module Xsarus_ERP
https://devdocs.magento.com/videos/fundamentals/create-a-new-module/ 

Maak de volgende directory aan:
app/code/Xsarus/XsarusERP/etc/module.xml
app/code/Xsarus/XsarusERP/registration.php

Draai de volgende commando
* php72 bin/magento setup:upgrade

In je lijst van modules zal je nu Xsarus_ERP zien staan.

### Maak via data patches de benodigde product attributen aan
https://devdocs.magento.com/guides/v2.3/extension-dev-guide/attributes.html 
https://markshust.com/2019/02/19/create-product-attribute-data-patch-magento-2.3-declarative-schema/
https://devdocs.magento.com/guides/v2.3/extension-dev-guide/attributes.html 

Voeg de missende attributen van producten toe:
- productScale
- productVendor

Maak hiervoor de volgende bestanden aan:
* app/code/Xsarus/XsarusERP/Setup/Patch/Data/AddProductScaleAttribute.php
* app/code/Xsarus/XsarusERP/Setup/Patch/Data/AddProductVendorAttribute.php

draai na het juist vullen de volgende commando:

* php72 bin/magento setup:upgrade

Controleer in de database bij patch_list of je patch is toegevoegd. Controleer daarna bij de Magento admin site bij catalog/products/{product}/add_attribute of je attribuut is toegevoegd.



## Producten importeren

### Console command maken
* composer require guzzlehttp/guzzle

https://devdocs.magento.com/guides/v2.3/extension-dev-guide/cli-cmds/cli-howto.html 
https://www.mageplaza.com/magento-2-module-development/magento-2-how-to-add-command-line-console-cli.html 

Creeër de volgende bestanden:
* app/code/Xsarus/XsarusERP/Console/Command/ConsoleCommand.php
* app/code/Xsarus/XsarusERP/etc/di.xml


Draai de volgende commando's
* php72 bin/magento setup:di:compile && php72 bin/magento setup:upgrade && php72 bin/magento cache:flush

Indien je nu de command php72 bin/magento draait, zie je de nieuwe command die zojuist gemaakt is. Voor deze opdracht heet de command: products:import

### Service maken
https://belvg.com/blog/shortly-about-magento-2-0-web-apis.html

Maak de volgende bestanden:
* app/code/Xsarus/XsarusERP/Service/{php file}








~ F I N ~

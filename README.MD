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
* Magento_Theme/layout/default.xml
* web/images/preview-image.jpg

Ga naar je Admin pagina en kies voor Content/Design/Configuration/Edit/Applied Theme/classicmodels en druk op Save Configuration

### Zorg via Less / Css dat de kleuren van het thema passen bij het logo
https://magento.stackexchange.com/questions/108685/how-to-add-a-custom-css-file-in-magento-2

~ F I N ~

Simone
======

Prerequisites
------------

* Have a dedicated domain name
* Choose an app name for your simone


Installation
------------

* Install git nginx php7.0-fpm
* Create a dedicated nginx configuration
  * You can use conf/ngxin.conf and save it as /etc/nginx/conf.d/__DOMAIN__.conf
  * Update the nginx configuration file replacing
    * __DOMAIN__ by your dedicated domain name
    * __APP__ by your app name 
* Create a dedicated php-fpm configuration
  * You can use conf/php-fpm.conf and save it as /etc/php/7.0/fpm/pool.d/__DOMAIN__.conf
  * Update the php-fpm configuration file replacing
    * __APP__ by your app name
* Restart nginx `service nginx reload`
* Create a letsencrypt configuration
  * You can use letsencrypt.ini and save it as /etc/letsencrypt/conf.ini
  * Update the letsencrypt configuration file replacing
    * __DOMAIN__ by your dedicated domain name
* Install a letencrypt certificate
  * `cd ~/`
  * `git clone https://github.com/letsencrypt/letsencrypt`
  * `cd letsencrypt/`
  * `sudo ./letsencrypt-auto certonly --config /etc/letsencrypt/conf.ini -d your.domaine.name`
  * if the certificate is properly generated, modify /etc/nginx/conf.d/__DOMAIN__.conf removing the two # before ssl_certificate and ssl_certificate_key
* Restart nginx `service nginx reload`
* Clone Simone in /var/www/__APP__
* Git init in /var/www/__APP__/_pages
* Chown -R www-data /var/www/__APP__

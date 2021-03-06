Login Cidadão
=============

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/redelivre/login-cidadao/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/redelivre/login-cidadao/?branch=master)
[![Join the chat at https://gitter.im/PROCERGS/login-cidadao](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/PROCERGS/login-cidadao?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

This is the source code for the 'Login Cidadão' (Citizen's Login) project.

This project's main objective is to provide a way for citizens to authenticate against official online services, eliminating the need to create and maintain several credentials on several services.

It also allows government agencies to better understand its citizen's needs and learn how to interact more effectively with them.

*Note*: Since this project is just on it's initial stages, it's not recommended to fork it just yet.

Dependencies
============

 * PHP >=5.4
 * composer
 * node.js
 * memcached

 PHP Extensions
  * php5-curl
  * php5-intl
  * php5-mysql or php5-pgsql or your preferred driver
  * php5-memcache (you can use php5-memcached instead, just remember to change the `Memcache` classes to `Memcached`)
 
 System Configuration
  * php timezone (date.timezone = America/Sao_Paulo)
  * write permission to app/cache app/logs web/uploads

Docs
====

[ Read the docs ](app/Resources/doc/index.md)

Setup - Development
===================

Setting up on Linux
-------------------

### Requirements
 * Sudoer user
 * PHP CLI
 * ACL-enabled filesystem
 * Composer

### Before you start
It's highly recommended to create your `app/config/parameters.yml` before installing to avoid database connection problems.

You can start by using `app/config/parameters.yml.dist` as a template by simply copying it to the same folder but naming it as `parameters.yml`, then edit the default values.

### Running the script
Check if your environment meets Symfony's prerequesites:
    `php app/check.php`
Just execute the `install.sh` script and follow instructions in case of errors or warnings.
Run:
	`php app/console server:run`
Test on default: http://localhost:8000

### Using Vagrant
## Requirements
* virtualbox
* [vagrant](https://www.vagrantup.com/)
* vagrant plugin [vagrant-vbguest](https://github.com/dotless-de/vagrant-vbguest) for port forward

## Before you start
It's highly recommended to create your `app/config/parameters.yml` before installing to avoid database connection problems.

You can start by using `app/config/parameters.yml.vagrant` as a template by simply copying it to the same folder but naming it as `parameters.yml`, then edit the default values. Do not edit database values if you want to use the default vm database.

## Run
	`vagrant up`
	
Setting up on Windows
---------------------
Currently we do not have a setup script for Windows, but it should be pretty straightforward to convert the install.sh to be Windows compatible.

General Steps for Installation
------------------------------

1. Make sure the following directories are writeable by your http/PHP user via ACL permissions ([you can read more here](http://symfony.com/doc/current/book/installation.html)):
  * app/cache
  * app/logs
  * web/uploads
2. Make sure you have all dependencies and needed PHP extensions installed.
3. Check if your environment meets Symfony's prerequesites:

    `$ php app/check.php`

4. Run `$ composer install`
5. Create the database if you didn't do it yet:

    `$ php app/console doctrine:database:create`

6. Create the schema:

    `$ php app/console doctrine:schema:create`

7. Point your server's Document Root to the /web folder and make sure app.php is your index. Symfony already comes with .htaccess to do it for you on Apache.

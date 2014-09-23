
# Sydney installation guide #

  * [Requirement](#requirement)
  * [Installation](#installation)
    * [Apache configuration](#apache-configuration)
      * [If you have virtualhost access](#if-you-have-virtualhost-access) 
      * [.htaccess configuration](#htaccess-configuration)
    * [DB configuration](#db-configuration)
    * [Sydney environment deployment](#sydney-environment-deployment)
      * [Finish the install with the installer](#finish-the-install-with-the-installer)
      * [Finish the install without the installer](#finish-the-install-without-the-installer)
        * [Generate DB structure](#generate-db-structure) 
        * [Load basic datas](#load-basic-datas)
        * [Create a webinstance entry](#create-a-webinstance-entry)
        * [Lock file](#lock-file)
        * [Create an admin user](#create-an-admin-user)
        * [Link the user to the instance](#link-the-user-to-the-instance)
        * [Add fake content](#add-fake-content)


## Requirement #

  * PHP 5.3+
  * MySQL
  * Imagick
  * mod_rewrite enabled in Apache

This document will present you how to install Sydney on your webserver.  
We will presume that your domain is `acme.tld` and the files will be locate in `/srv/www/sydney`.  

Your website will be accesible at [http://acme.tld](http://acme.tld)  
The administration panel will be accessible at [http://acme.tld/admin](http://acme.tld/admin)  


## Installation #

  1. Download the '[core](https://github.com/Antidot-be/sydney-core/archive/master.zip)', unzip it and put its content in `/srv/www/sydney/core/`. 
  2. You will have to create a webinstance. You can download a sample webinstance [here](https://github.com/Antidot-be/sydney-sample-instance) (with the installer). Once you have download the sample webinstance you will have to place its content in `/srv/www/sydney/webinstances/acme/`

The 'core' folder is the kernel for all your (future) websites. It will contains all global functionalities and administration ...  
The 'webinstances' folder contains all your website's projects. You can put from 1 to infinite websites there.


You should have a file tree like :

    /srv/www/sydney/
    ├── core
    │   ├── application
    │   ├── doc
    │   ├── library
    │   └── ...
    └── webinstances
        └── acme
            ├── config
            ├── html
            ├── layouts
            └── var
        ├── another-site
        └── ...



### Apache configuration  ###

In this part we will configure how "sydneyassets" will be served and also the domain name configuration.  

#### If you have virtualhost access  ####

If you don't have access to your virtualhost jump to the next section.

##### For Apache 2.4+ : ######

    <VirtualHost *:80>
	    ServerName acme.tld
	    ServerAdmin webmaster@acme.tld

	    Alias /sydneyassets /srv/www/sydney/core/webinstances/sydney/html/sydneyassets
	    <Directory /srv/www/sydney/core/webinstances/sydney/html/sydneyassets>
		    Require all granted # Used for global assets on the admin
	    </Directory>

	    DocumentRoot /srv/www/sydney/webinstances/acme/html
	    <Directory /srv/www/sydney/webinstances/acme/html>
		    Allowoverride all
	    </Directory>
    </VirtualHost>

##### For previous versions of Apache : #####

    <VirtualHost *:80>
	    ServerName acme.tld
	    ServerAdmin webmaster@macme.tld

        Alias /sydneyassets /srv/www/sydney/core/webinstances/sydney/html/sydneyassets
        <Directory /srv/www/sydney/core/webinstances/sydney/html/sydneyassets>
            Order deny,allow
            Allow from all
        </Directory>

	    DocumentRoot /srv/www/sydney/webinstances/acme/html
	    <Directory /srv/www/sydney/webinstances/acme/html>
		    Allowoverride all
	    </Directory>
    </VirtualHost>


   Once this was achieved, reload Apache with admin rights (eg sudo) : `service apache reload`

#### .htaccess configuration ####

If you don't have a virtualhost access you have to put a .htaccess on your root main folder as : `/srv/www/sydney/.htaccess`.

Add these following codes to the .htaccess.

    RewriteEngine On

    Options -Indexes

    RewriteRule ^sydneyassets/(.+)$  /core/webinstances/sydney/html/sydneyassets/$1 [L]

    RewriteCond %{REQUEST_URI} !^/core/webinstances/sydney/html/sydneyassets/
    RewriteRule ^(.*) /webinstances/acme/html/$1


### DB configuration ###

The `/srv/www/sydney/webinstances/acme/config/parameters.ini` file needs to be completed: 

    db.params.host=
    db.params.username=
    db.params.password=
    db.params.dbname=

For instance, a localhost with a DB sydney_database linked to a user "my_name" and the password "pass" would be filled like this :  

    db.params.host=localhost
    db.params.username=my_name
    db.params.password=pass
    db.params.dbname=sydney_database

### Sydney environment deployment ###


The `/srv/www/sydney/webinstances/acme/config/config.ini` file have to be modified.
For example : 

    general.baseUrl=http://acme.tld/
    general.cdn=http://acme.tld/


#### Finish the install with the installer ####

Complete the installation by going to the url :[http://acme.tld/install/index.php](http://acme.tld/install/index.php).  
The installation launcher will explain how to proceed (if you don't like graphical installer see the section bellow).

Once install is done don't forget to remove the "html/*install*" directory

Sydney is now ready to be used!


#### Finish the install without the installer ####

*Don't pay attention if you have already install Sydney!*  

You will need to get all the data to insert in the DB.  
Those datas are locate in "install" directory in the [sample instance](https://github.com//Antidot-be/sydney-sample-instance)

##### Generate DB structure #####

The DB structure is in the `db-structure.sql` file

##### Load basic datas #####

Those datas are available in the `db-data.sql` file

##### Create a webinstance entry #####

You can change the languages_id (look at the languages table)

    INSERT INTO `safinstances` (`label`, `domain`, `description`, `languages_id`, `creationdate`, `active`, `offlinemessage`, `metakeywords`)
    VALUES ('My website', 'http://acme.tld', 'Here a description', '1', NOW(), '1', 'Website is offline', '');

You will need to retrieve the instance id for the next steps.  

##### Lock file #####

You will have to add in the `/srv/www/sydney/webinstance/acme/config/` folder of your instance a `instance.ini.lock` file.  
This file will never change.  

    [general]
    db.safinstances_id=1

##### Create an admin user #####

Just change the default instance id (here safinstance_id = 999999999).  
This user will be admin:password (md5).

    INSERT INTO `users` (`login`, `password`, `usersgroups_id`, `valid`, `mdname`, `lname`, `cell`, `active`, `safinstances_id`, `subscribedate`, `unsubscribedate`, `modifieddate`, `ip`, `creatoridentity`, `avatar`, `pagorder`, `lastlogindate`, `timeValidityPassword`)
    VALUES ('admin', '5f4dcc3b5aa765d61d8327deb882cf99', '7', '1', 'Admin', 'User', '', '1', '999999999', NOW(), '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', '', '0', NOW(), '0');


##### Link the user to the instance #####

123 = id of the instance previously created.  
456 = id of the user previously created (to replace with the correct one ;))  

    INSERT INTO `safinstances_users` (`safinstances_id`, `users_id`) VALUES ('123', '456');

##### Add fake content #####

You can additionnaly add some fake content. This fake content is locate in the `db-example-page.sql` file.  
All `{instanceid}` will have to be replace by the correct instance_id (created previously)


And it's done!

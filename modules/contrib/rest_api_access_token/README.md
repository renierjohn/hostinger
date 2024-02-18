CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

The REST API Access Token module provides a Drupal authentication provider 
that uses tokens (in headers) as the primary factor of authentication.
Additionally module provide signature verification for requests 
and response cache.


REQUIREMENTS
------------

min. PHP 7.0 version


INSTALLATION
------------

Install the REST API Access Token module as you would normally install 
a contributed Drupal module.
Enable module in admin panel.


CONFIGURATION
-------------

1. Navigate to Administration > Extend and enable the module.
2. Navigate to Administration > Configuration > REST API Access Token 
and enable 'Enable signature verification' 
if you need signature verification for each request.
3. Navigate to Administration > Configuration > REST API Access Token 
and enable 'Enable cache endpoints 
by REQUEST-ID (in header)' if you need cacheable endpoints.
4. Navigate to Administration > Configuration > REST API Access Token 
and set value of 'Set lifetime of cache endpoints in seconds.'


MAINTAINERS
-----------
* Marcin Kazmierski (marcinkazmierski) - 
https://www.drupal.org/u/marcinkazmierski
* https://pareview.sh/pareview/https-git.drupal.org-project-rest_api_access_token.git-8.x-1.x
* https://git.drupalcode.org/project/rest_api_access_token


TODO
-----------
* unit tests
* disable auth for specific endpoints / controllers

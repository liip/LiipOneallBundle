Introduction
============

**This is still a work in progress!**

This Bundle enables integration of the Oneall.com into Symfony2.
The code is based on https://github.com/FriendsOfSymfony/FOSFacebookBundle

Please also refer to the Oneall documentation:
http://docs.oneall.com/plugins/

Please also refer to the official documentation of the SecurityBundle, especially
for details on the configuration:
http://symfony.com/doc/current/book/security.html

[![Build Status](https://secure.travis-ci.org/Liip/LiipOneallBundle.png?branch=master)](http://travis-ci.org/Liip/LiipOneallBundle)

Prerequisites
============

This version requires Symfony 2.1

Installation
============

  1. Add the following lines in your composer.json:
  ```
{
    "require": {
        "liip/oneall-bundle": "dev-master"
    }
}
  ```
  
  2. Run the composer to download the bundle
  ``` bash
  $ php composer.phar update liip/oneall-bundle
  ```
  
  3. Add this bundle to your application's kernel:
  ``` php
          // app/ApplicationKernel.php
          public function registerBundles()
          {
              return array(
                  // ...
                  new Liip\OneallBundle\LiipOneallBundle(),
                  // ...
              );
          }
  ```        
  4. Add the following routes to your application and point them at actual controller actions
  ``` yaml
          #application/config/routing.yml
          _security_check:
              pattern:  /login_check
          _security_logout:
              pattern:  /logout
  ```
  ``` xml
          #application/config/routing.xml
          <route id="_security_check" pattern="/login_check" />
          <route id="_security_logout" pattern="/logout" />     
  ```
  5. Configure the `oneall` service in your config:
  ``` yaml
          # application/config/config.yml
          liip_oneall:
              site_subdomain: my_subdomain
              site_public_key: my_not_so_secret_key
              site_private_key: my_s3cr3t_key
              social_links: [linkedin, facebook, github, twitter]
              default_firewall_name: main
              callback_path: /foo
  ```
  ``` xml
          # application/config/config.xml
          <liip_oneall:api
              site_subdomain="my_subdomain"
              site_public_key="my_not_so_secret_key"
              site_private_key="my_s3cr3t_key"
              default_firewall_name="main"
              callback_path="/foo"
          >
                <social-links>linkedin</social-links>
                <social-links>facebook</social-links>
                <social-links>github</social-links>
                <social-links>twitter</social-links>
          </liip_oneall:api>
  ```

     Note you only need to specify either ``default_firewall_name`` or ``callback_path``.
     The ``callback_path`` can either be a relative path or a route name.

  6. Add this configuration if you want to use the `security component`:
  ```
          # application/config/config.yml
          security:
              firewalls:
                  public:
                      # since anonymous is allowed users will not be forced to login
                      pattern:   ^/.*
                      liip_oneall:
                          check_path: /oneall
                      anonymous: true
                      logout:
                          handlers: ["liip_oneall.logout_handler"]

              access_control:
                  - { path: ^/secured/.*, role: [IS_AUTHENTICATED_FULLY] } # This is the route secured with liip_oneall
                  - { path: ^/.*, role: [IS_AUTHENTICATED_ANONYMOUSLY] }
  ```
     You have to add `/secured/` in your routing for this to work. An example would be...
  ```
              _oneall_secured:
                  pattern: /secured/
                  defaults: { _controller: AcmeDemoBundle:Welcome:index }
  ```

  7. Optionally define a custom user provider class and use it as the provider or define path for login
  ```
          # application/config/config.yml
          security:
              providers:
                  # choose the provider name freely
                  my_liip_oneall_provider:
                      id: my.oneall.user   # see "Example Custom User Provider using the FOS\UserBundle" chapter further down

              firewalls:
                  public:
                      pattern:   ^/.*
                      liip_oneall:
                          login_path: /login
                          check_path: /login_check
                          default_target_path: /
                          provider: my_liip_oneall_provider
                      anonymous: true
                      logout:
                          handlers: ["liip_oneall.logout_handler"]
  ```

  8. Optionally use access control to secure specific URLs
  ```
          # application/config/config.yml
          security:
              # ...
              
              access_control:
                  - { path: ^/oneall/,           role: [ROLE_ONEALL] }
                  - { path: ^/.*,                role: [IS_AUTHENTICATED_ANONYMOUSLY] }
   ```

    The role `ROLE_ONEALL` has to be added in your User class (see Acme\MyBundle\Entity\User::setFBData() below).
    > Note that the order of access control rules matters!

Setting up the JavaScript SDK
-----------------------------

A templating Twig extension is included for loading the Oneall JavaScript SDK and
initializing it with parameters from your service container. To setup the
Oneall JavaScript environment, add the following to your layout just after
the opening `body` tag:
```html+jinja
<!-- inside a twig template -->
{{ oneall_initialize() }}
```

Include the login button in your templates
------------------------------------------

Just add the following code in one of your templates:
```html+jinja
<!-- inside a twig template -->
{{ oneall_login_button({'login_container_id': 'some_tag_id'}) }}
```
Note that ``login_container_id`` is optional and defaults to ``oa_social_login_container``.
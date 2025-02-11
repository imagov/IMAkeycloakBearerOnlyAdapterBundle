IMAkeycloakBearerOnlyAdapterBundle
===================================

This Symfony bundle is an adapter that allows securing API using keycloak Bearer Only clients.

## Installation

> Befor installing the bundle, automatic packages configuration can be activated with the following command:
> ```
> composer config extra.symfony.allow-contrib true
> ```

With composer:

```
composer require ima/keycloak-bearer-only-adapter-bundle
```

## Configuration

If you want to set up keycloak locally you can download it [here](https://www.keycloak.org/downloads) and follow instructions from [the official documentation](https://www.keycloak.org/docs/latest/server_installation/index.html).

### Bundle configuration

#### Via a recipe (Automatic)
This bundle hase a Symfony recipe that allow the automation of configuration via the Symfony Flex Composer plugin.
To enable recipe for your project, run the following command:

```
composer config extra.symfony.allow-contrib true
```

#### Manual
Having a running keycloak locally or in Docker and already configured a client with **Access Type = bearer-only**
here is the configuration to use:

```yaml
# config/packages/ima_keycloak_bearer_only_adapter.yaml
ima_keycloak_bearer_only_adapter:
    issuer: '%env(OAUTH_KEYCLOAK_ISSUER)%' # your accessible keycloak url
    realm: '%env(OAUTH_KEYCLOAK_REALM)%' # your keycloak realm name
    client_id: '%env(OAUTH_KEYCLOAK_CLIENT_ID)%' # your keycloak client id
    client_secret: '%env(OAUTH_KEYCLOAK_CLIENT_SECRET)%' # your keycloak client secret
    #ssl_verification: False # by default ssl_verification is set to False
```
The best practice is to load your configuration from **.env** file.

```
# .env
...
###> Ima_keycloak_bearer_only_adapter ###
OAUTH_KEYCLOAK_ISSUER=keycloak:8080
OAUTH_KEYCLOAK_REALM=my_realm
OAUTH_KEYCLOAK_CLIENT_ID=my_bearer_client
OAUTH_KEYCLOAK_CLIENT_SECRET=my_bearer_client_secret
###< Ima_keycloak_bearer_only_adapter ###
...
```
> Since Keycloak 17 the default distribution is now powered by **Quarkus**, while the legacy **WildFly** powered distribution will still be around until June 2022 <br>
> The new distribution introduces a number of breaking changes, including: <br>
> - `/auth` removed from the default context path <br>
> ⚠️ **If you are using a legacy version make sure to include /auth in OAUTH_KEYCLOAK_ISSUER** <br>
> Example: `keycloak:8080/auth`

In case of using Keycloak with Docker locally replace **issuer** value with your keycloak container reference in the network

For example, you can use the service name, or container IPAdresse that you can get using this command:

```
docker inspect <container id> | grep "IPAddress"
```
### Symfony security configuration

To secure your API with Keycloak you must change the default security configuration in symfony.

Here is a simple configuration that restrict access to ```/api/*``` routes only to user with role **ROLE_USER** :

```yaml
# config/packages/security.yaml
security:
    providers:
        keycloak_bearer_user_provider:
            id: IMA\Bundle\keycloakBearerOnlyAdapterBundle\Security\User\KeycloakBearerUserProvider
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        api:
            pattern: ^/api/
            provider: keycloak_bearer_user_provider
            custom_authenticators:
              - IMA\Bundle\keycloakBearerOnlyAdapterBundle\Security\Authenticator\KeycloakBearerAuthenticator
            stateless: true
    access_control:
        - { path: ^/api/, roles: [ROLE_USER] }
```
> :information_source: Referring to Symfony [documentation](https://symfony.com/doc/5.3/security.html#roles), roles must start with **ROLE_** (otherwise, things won't work as expected)
### Keycloak configuration

To configure keycloak to work with this bundle, [here](./Resources/docs/keycloak-config-guide.md) is a step by step documentation for a basic configuration of keycloak.

### Compatibility


| Bundle Version                                        | Symfony Version    |
| ------------------------------------------------------|--------------------|
| V1.0.0                                                | >=6.4.0            |
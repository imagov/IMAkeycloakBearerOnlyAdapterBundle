<?php


namespace IMA\Bundle\keycloakBearerOnlyAdapterBundle\Security\User;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;


class KeycloakBearerUserProvider implements UserProviderInterface{

    /**
     * @var string
     */
    private $issuer;
    /**
     * @var string
     */
    private $realm;
    /**
     * @var string
     */
    private $client_id;
    /**
     * @var string
     */
    private $client_secret;
    /**
     * @var mixed
     */
    private $sslVerification;

    /**
     * KeycloakBearerUserProvider constructor.
     * @param string $issuer
     * @param string $realm
     * @param string $client_id
     * @param string $client_secret
     */
    public function __construct(string $issuer, string $realm, string $client_id, string $client_secret, $sslVerification)
    {
        $this->issuer = $issuer;
        $this->realm = $realm;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->sslVerification = $sslVerification;
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API, which is our case), this
     * method is not called. But it is implement it anyway.
     *
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof KeycloakBearerUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user = $this->loadUserByIdentifier($user->getAccessToken());

        if (!$user) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass(string $class): bool
    {
        return KeycloakBearerUser::class === $class || is_subclass_of($class, KeycloakBearerUser::class);
    }



    /**
     * @param string $accessToken
     * @return UserInterface
     */
    public function loadUserByIdentifier(string $accessToken): UserInterface
    {
        $httpClient = HttpClient::create(['base_uri' => $this->issuer]);
    
        $response = $httpClient->request('POST', '/auth/realms/'.$this->realm.'/protocol/openid-connect/token/introspect', [
            'auth_basic' => [$this->client_id, $this->client_secret],
            'body' => [
                'token' => $accessToken,
            ],
            'verify_peer' => $this->sslVerification,
        ]);

        $jwt = json_decode($response->getContent(), true);

        if (!$jwt || !isset($jwt['active'])) {
            throw new CustomUserMessageAuthenticationException('The token does not exist or is not valid anymore');
        }

        if (!isset($jwt['resource_access'][$this->client_id])) {
            throw new CustomUserMessageAuthenticationException('The token does not have the necessary permissions!');
        }

        return new KeycloakBearerUser(
            $jwt['sub'],
            $jwt['name'] ?? '',
            $jwt['email'] ?? '',
            $jwt['given_name'] ?? '',
            $jwt['family_name'] ?? '',
            $jwt['preferred_username'],
            $jwt['resource_access'][$this->client_id]['roles'],
            $accessToken
        );
    }

    /**
     * @param string $username
     * @return UserInterface
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * Retrieves user info from Keycloak using the userinfo endpoint.
     *
     * @param string $accessToken
     * @return array
     */
    public function getUserInfo(string $accessToken): array
    {
        $httpClient = HttpClient::create(['base_uri' => $this->issuer]);

        $response = $httpClient->request('GET', '/realms/'.$this->realm.'/protocol/openid-connect/userinfo', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'verify_peer' => $this->sslVerification,
        ]);

        $userInfo = json_decode($response->getContent(), true);

        if (isset($userInfo['error'])) {
            throw new CustomUserMessageAuthenticationException('Failed to retrieve user info: ' . $userInfo['error_description']);
        }

        return $userInfo;
    }
}

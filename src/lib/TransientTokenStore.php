<?php
/**
 * SocialStreams\TransientTokenStore
 *
 * @package wp_social_streams
 * @author Stuart Laverick
 */
namespace SocialStreams;

use OAuth\Common\Storage\Exception\AuthorizationStateNotFoundException;
use OAuth\Common\Storage\Exception\StorageException;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Storage\TokenStorageInterface;

defined('ABSPATH') or die( 'No script kiddies please!' );

class TransientTokenStore implements TokenStorageInterface
{
    /**
     * Active service tokens
     *
     * @var array
     **/
    private $tokens = [];

    /**
     * Active service states
     *
     * @var array
     **/
    private $states = [];

    /**
     * Transient Prefix
     *
     * @var string
     **/
    private $transientPrefix = 'social_streams_';

    /**
     * Default lifetime for session transients (4 weeks)
     *
     * @var integer
     **/
    protected $transientLifetime = 2419200;

    public function __construct()
    {
        $this->tokens = get_transient($this->transientPrefix . 'tokens') ? : [];
        $this->states = get_transient($this->transientPrefix . 'states') ? : [];
    }

    /**
     * @param string $service
     * @return TokenInterface
     * @throws TokenNotFoundException
     */
    public function retrieveAccessToken($service)
    {
        $service = $this->normaliseServiceName($service);
        if ($this->hasAccessToken($service)) {
            return $this->tokens[ $service ];
        }

        throw new TokenNotFoundException('Token not stored');
    }

    /**
     * @param string $service
     * @param TokenInterface $token
     * @return TokenStorageInterface
     */
    public function storeAccessToken($service, TokenInterface $token)
    {
        $this->tokens[ $this->normaliseServiceName($service) ] = $token;
        $this->updateTransient('tokens');

        // allow chaining
        return $this;
    }

    /**
     * @param string $service
     * @return bool
     */
    public function hasAccessToken($service)
    {
        $service = $this->normaliseServiceName($service);
        return isset($this->tokens[ $service ]) && $this->tokens[ $service ] instanceof TokenInterface;
    }

    /**
     * Delete the users token. Aka, log out.
     *
     * @param string $service
     * @return TokenStorageInterface
     */
    public function clearToken($service)
    {
        $service = $this->normaliseServiceName($service);
        if (array_key_exists($service, $this->tokens)) {
            unset($this->tokens[ $service ]);
            $this->updateTransient('tokens');
        }

        // allow chaining
        return $this;
    }

    /**
     * Delete *ALL* user tokens. Use with care. Most of the time you will likely
     * want to use clearToken() instead.
     *
     * @return TokenStorageInterface
     */
    public function clearAllTokens()
    {
        $this->tokens = [];
        $this->updateTransient('tokens');

        // allow chaining
        return $this;
    }

    /**
     * Retrieve the authorization state for a given service
     *
     * @param string $service
     * @return string
     */
    public function retrieveAuthorizationState($service)
    {
        if ($this->hasAuthorizationState($service)) {
            return $this->states[ $this->normaliseServiceName($service) ];
        }
        throw new AuthorizationStateNotFoundException('State not stored');
    }

    /**
     * Store the authorization state related to a given service
     *
     * @param string $service
     * @param string $state
     * @return TokenStorageInterface
     */
    public function storeAuthorizationState($service, $state)
    {
        $this->states[ $this->normaliseServiceName($service) ] = $state;
        $this->updateTransient('states');

        // allow chaining
        return $this;
    }

    /**
     * Check if an authorization state for a given service exists
     *
     * @param string $service
     * @return bool
     */
    public function hasAuthorizationState($service)
    {
        $service = $this->normaliseServiceName($service);
        return isset($this->states[ $service ]) && null !== $this->states[ $service ];
    }

    /**
     * Clear the authorization state of a given service
     *
     * @param string $service
     * @return TokenStorageInterface
     */
    public function clearAuthorizationState($service)
    {
        if (array_key_exists($service, $this->states)) {
            unset($this->states[ $this->normaliseServiceName($service) ]);
            $this->updateTransient('states');
        }

        // allow chaining
        return $this;
    }

    /**
     * Delete *ALL* user authorization states. Use with care. Most of the time you will likely
     * want to use clearAuthorization() instead.
     *
     * @return TokenStorageInterface
     */
    public function clearAllAuthorizationStates()
    {
        $this->states = [];
        $this->updateTransient('states');

        // allow chaining
        return $this;
    }

    /**
     * Write the changes to the states or tokens to the relevant transient
     *
     * @param string tokens|states
     * @return bool success
     * @author 
     **/
    private function updateTransient($name)
    {
        return set_transient($this->transientPrefix . $name, $this->$name, $this->transientLifetime);
    }

    /**
     * Returns a normalised key string
     *
     * @return string
     * @author Stuart Laverick
     **/
    private function normaliseServiceName($name)
    {
        return trim(strtolower($name));
    }
}

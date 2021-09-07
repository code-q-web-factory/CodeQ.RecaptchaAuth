<?php
namespace CodeQ\RecaptchaAuth\Token;

/*
 * This script is an adopted version from the Flow package "Flowpack.OAuth2.Client".
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Security\Authentication\Token\AbstractToken;
use Neos\Flow\Security\Authentication\TokenInterface;
use Psr\Log\LoggerInterface;

/**
 */
class RecaptchaToken extends AbstractToken
{
    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $systemLogger;

    /**
     * @var string
     */
    protected $credentials = null;

    /**
     * Updates the authentication credentials, the authentication manager needs to authenticate this token.
     * This could be a username/password from a login controller.
     * This method is called while initializing the security context. By returning TRUE you
     * make sure that the authentication manager will (re-)authenticate the tokens with the current credentials.
     * Note: You should not persist the credentials!
     *
     * @param ActionRequest $actionRequest The current request instance
     * @throws \InvalidArgumentException
     * @return boolean TRUE if this token needs to be (re-)authenticated
     */
    public function updateCredentials(ActionRequest $actionRequest)
    {
        if ($actionRequest->hasArgument('reCaptchaToken')) {
            $this->credentials = $actionRequest->getArgument('reCaptchaToken');
            $this->setAuthenticationStatus(TokenInterface::AUTHENTICATION_NEEDED);
        }
    }

    /**
     * @param string $authenticationProviderName
     */
    public function setAuthenticationProviderName($authenticationProviderName)
    {
        parent::setAuthenticationProviderName($authenticationProviderName);
    }
}

<?php
namespace CodeQ\RecaptchaAuth\Provider;

use Neos\Flow\Security\Authentication\Provider\AbstractProvider;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\Authentication\TokenInterface;
use Neos\Flow\Security\Exception\UnsupportedAuthenticationTokenException;
use Neos\Flow\Security\Policy\PolicyService;
use CodeQ\RecaptchaAuth\Token\RecaptchaToken;
use Psr\Log\LoggerInterface;

/**
 */
class RecaptchaProvider extends AbstractProvider
{
    /**
     * @Flow\InjectConfiguration(package="Neos.Flow", path="security.authentication.providers.RecaptchaProvider.providerOptions")
     * @var array
     */
    protected $settings;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $systemLogger;

    /**
     * @Flow\Inject
     * @var PolicyService
     */
    protected $policyService;

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Security\Context
     */
    protected $securityContext;

    /**
     * Tries to authenticate the given token. Sets isAuthenticated to TRUE if authentication succeeded.
     *
     * @param TokenInterface $authenticationToken The token to be authenticated
     * @throws \Neos\Flow\Security\Exception\UnsupportedAuthenticationTokenException
     * @return void
     */
    public function authenticate(TokenInterface $authenticationToken)
    {
        if (!($authenticationToken instanceof RecaptchaToken)) {
            throw new UnsupportedAuthenticationTokenException('This provider cannot authenticate the given token.', 1383754993);
        }

        $recaptchaToken = $authenticationToken->getCredentials();

        $recaptcha = new \ReCaptcha\ReCaptcha($this->settings['secretKey']);
        $resp = $recaptcha->verify($recaptchaToken, $_SERVER['REMOTE_ADDR']);
        if ($resp->isSuccess() === false) {
            $authenticationToken->setAuthenticationStatus(TokenInterface::WRONG_CREDENTIALS);
            $this->systemLogger->notice('The captcha was not answered correctly.');
            return;
        }
        $authenticationToken->setAuthenticationStatus(TokenInterface::AUTHENTICATION_SUCCESSFUL);

        /** @var $account \Neos\Flow\Security\Account */
        $providerName = $this->name;
        $account = new Account();
        $account->setAccountIdentifier(sha1($recaptchaToken));
        $account->setAuthenticationProviderName($providerName);

        $roles = [];
        foreach ($this->options['authenticateRoles'] as $roleIdentifier) {
            $roles[] = $this->policyService->getRole($roleIdentifier);
        }
        $account->setRoles($roles);
        $authenticationToken->setAccount($account);

        $account->setCredentialsSource($recaptchaToken);
        $account->authenticationAttempted(TokenInterface::AUTHENTICATION_SUCCESSFUL);
    }

    /**
     * Returns the class names of the tokens this provider is responsible for.
     *
     * @return array The class name of the token this provider is responsible for
     */
    public function getTokenClassNames()
    {
        return ['CodeQ\RecaptchaAuth\Token\RecaptchaToken'];
    }
}

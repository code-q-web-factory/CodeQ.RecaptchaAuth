[![Latest Stable Version](https://poser.pugx.org/codeq/recaptchaauth/v/stable)](https://packagist.org/packages/codeq/recaptchaauth)
[![License](https://poser.pugx.org/codeq/recaptchaauth/license)](LICENSE)

# Auth provider based on invisible recaptcha for Neos CMS

## WORK IN PROGRESS: This might not work as a general use case package yet.

This package allows visitors to automatically per authenticated via Google Invisible Recaptcha. This can be useful e.g.
for an open chat or wall where visitors can upload images, and you want to prevent spam.

We for example currently use if for a thumb-up like feature for all website visitors.

*The development and the public-releases of this package are generously sponsored by [Code Q Web Factory](http://codeq.at).*

## Installation (AFTER THE FIRST RELEASE)

CodeQ.RecaptchaAuth is available via packagist. `"codeq/recaptchaauth" : "~1.0"` to the `require? section of the
composer.json or run:

```bash
composer require codeq/recaptchaauth
```

We use semantic-versioning so every breaking change will increase the major-version number.

## Usage

First, configure your Recaptcha identifier and secret, like this:

```
Neos:
  Flow:
    security:
      authentication:
        providers:
          GoogleOAuth2Provider:
            providerOptions:
              redirectionEndpointUri: 'http://localhost:8081/'
              clientIdentifier: xxx
              clientSecret: 'xxx'
              partyCreation: false
              authenticateRoles:
                - CodeQ.RecaptchaAuth:IdentifiedVisitor
    session:
      inactivityTimeout: 0
      cookie:
        lifetime: 0
Neos:
  Flow:
    security:
      authentication:
        providers:
          RecaptchaProvider:
            # see https://flowframework.readthedocs.io/en/stable/TheDefinitiveGuide/PartIII/Security.html?highlight=webredirect#authentication-entry-points
            entryPoint: 'WebRedirect'
            entryPointOptions:
              uri: '/recaptcha-validation-failed'
```

Then, in your Policy.yaml use the role to allow additional things, e.g.

```
privilegeTargets:
  'Neos\Flow\Security\Authorization\Privilege\Method\MethodPrivilege':
    CodeQ_UserGeneratedPosts_PostCreate:
      matcher: method(CodeQ\UserGeneratedPosts\Controller\UserGeneratedPostsController->(create)Action())

roles:
  'CodeQ.RecaptchaAuth:IdentifiedVisitor':
    privileges:
      -
        privilegeTarget: CodeQ_UserGeneratedPosts_PostCreate
        permission: GRANT

  'Neos.Neos:AbstractEditor':
    privileges:
      -
        privilegeTarget: CodeQ_UserGeneratedPosts_PostCreate
        permission: GRANT

```

If you want Racaptcha to be only loaded on specific pages, configure it like this:

```
prototype(Neos.Neos:Page) {
    body.javascripts.reCaptcha = Neos.Fusion:Tag {
    	@if.isUsed = ${q(documentNode).children('main').find('[instanceof CodeQ.Site:MySpecialContentNodeWhichNeedsAuth]').count() > 0}
    }
}
```

## License

Licensed under MIT, see [LICENSE](LICENSE)

## Contribution

We will gladly accept contributions. Please send us pull requests.


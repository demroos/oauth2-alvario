# Alvario Provider for OAuth 2.0 Client

This package provides Alvario OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```sh
composer require demroos/oauth2-alvario
```

## Usage with Symfony

### Install client bundle

```sh
composer require knpuniversity/oauth2-client-bundle
```

### Create controller

```php
<?php

namespace App\Controller;

use Alvario\OAuth2\AlvarioUser;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlvarioController extends AbstractController
{
    /**
     * @Route("/connect/alvario", name="connect_alvario_start")
     * @param ClientRegistry $clientRegistry
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function connectAction(ClientRegistry $clientRegistry)
    {
        $response = $clientRegistry
            ->getClient('alvario')
            ->redirect([
                'public_profile'
            ]);

        return $response;
    }

    /**
     * @Route("/connect/alvario/check", name="connect_alvario_check")
     * @param Request $request
     * @param ClientRegistry $clientRegistry
     * @return Response
     * @throws \Exception
     */
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
        return Response::create('OK');
    }
}
```

### Create Authenticator

```php
<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AlvarioAuthenticator extends SocialAuthenticator
{
    /** @var ClientRegistry  */
    private $clientRegistry;

    /** @var EntityManagerInterface  */
    private $em;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            '/connect/alvario', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'connect_alvario_check';
    }

    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getAlvarioClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var AlvarioUser $alvarioUser */
        $alvarioUser = $this->getAlvarioClient()
            ->fetchUserFromToken($credentials);

        $user = $this->em->getRepository(User::class)
            ->findByOauthId($alvarioUser->getId());

        if ($user instanceof UserInterface) {
            return $user;
        }

        // create new local user
        $user = new User();
        $user->setOauthId($alvarioUser->getId());
        $user->setFirstName($alvarioUser->getFirstName());
        $user->setLastName($alvarioUser->getLastName());

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        // or redirect to other page
        return null;
    }

    private function getAlvarioClient()
    {
        return $this->clientRegistry
            ->getClient('alvario');
    }
}
```

### Register your authenticator in `security.yaml`, under the `guard` section:

```yaml
security:
    # ...
    firewalls:
    	# ...
        main:
	    # ...
+            guard:
+                authenticators:
+                    - App\Security\AlvarioAuthenticator
```

### Configure client bundle

```yaml
knpu_oauth2_client:
    clients:
        # configure your clients as described here: https://github.com/knpuniversity/oauth2-client-bundle#configuration
        alvario:
            type: generic
            provider_class: Alvario\OAuth2\AlvarioAuthProvider
            # optional: a class that extends OAuth2Client
            # client_class: Some\Custom\Client

            # optional: if your provider has custom constructor options
            provider_options: {}

            # now, all the normal options!
            client_id: '%env(ALVARIO_AUTH_CLIENT_ID)%'
            client_secret: '%env(ALVARIO_AUTH_CLIENT_SECRET)%'
            redirect_route: connect_alvario_check
            redirect_params: {}
```
### Add to .env
```dotenv
ALVARIO_AUTH_CLIENT_ID=clien_id
ALVARIO_AUTH_CLIENT_SECRET=client_secret
```

## Testing

Tests can be run with:

```sh
composer test
```

## License

The MIT License (MIT). Please see [License File](https://github.com/demroos/oauth2-alvario/blob/master/LICENSE) for more information.

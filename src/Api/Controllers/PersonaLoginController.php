<?php

namespace Dhtml\Persona\Api\Controllers;


use Exception;
use Flarum\Http\RememberAccessToken;
use Flarum\Http\RequestUtil;
use Flarum\Http\SessionAccessToken;
use Flarum\Http\SessionAuthenticator;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\AuthToken;
use Flarum\User\User;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PersonaLoginController extends PersonaBaseController
{
    protected SettingsRepositoryInterface $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        parent::__construct($settings);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);

        if ($actor->isGuest()) {
            return new JsonResponse(['error' => 'Access forbidden'], 401);
        }

        $email = $actor->email;

        $matchPattern = $this->doesEmailMatchPatterns($actor->email, $this->patterns);

        $isModerator = $this->checkIfUserIsModerator($actor->id);

        if (!$matchPattern && !$isModerator) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $this->invalidateUser($actor->id);


        $username = $request->getParsedBody()['username'];

        $session = $request->getAttribute('session');

        $sess = $this->authenticateById($username, $session);


        return new JsonResponse(["success" => $sess], 200);
    }

}

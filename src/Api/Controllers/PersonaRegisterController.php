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
use Psr\Http\Server\RequestHandlerInterface;

class PersonaLoginController implements RequestHandlerInterface
{
    protected SettingsRepositoryInterface $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;

        $this->patterns = ["pattern1" => $this->settings->get('dhtml-persona.pattern1'), "pattern2" => $this->settings->get('dhtml-persona.pattern2'), "pattern3" => $this->settings->get('dhtml-persona.pattern3'),];
    }

    function doesEmailMatchPatterns($email, $patterns)
    {
        foreach ($patterns as $pattern) {
            if (!empty($pattern) && strpos($email, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    function checkIfUserIsModerator($userId) {
        // Retrieve the user by ID
        $user = User::findOrFail($userId);

        // Get all groups the user belongs to
        $groupIds = $user->groups()->pluck('id')->toArray();

        // Define the moderator group IDs
        $moderatorGroupIds = ['1', '4']; // Adjust as per your actual group IDs

        // Check if the user belongs to any moderator group
        $isModerator = !empty(array_intersect($groupIds, $moderatorGroupIds));

        return $isModerator;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);

        if ($actor->isGuest()) {
            return new JsonResponse(['error' => 'Access forbidden'], 401);
        }

        $email = $actor->email;

        $matchPattern = $this->doesEmailMatchPatterns($actor->email,$this->patterns);

        $isModerator = $this->checkIfUserIsModerator($actor->id);

        if (!$matchPattern && !$isModerator) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }


        $username = $request->getParsedBody()['username'];

        $session = $request->getAttribute('session');

        $sess = $this->authenticateById($username, $session);


        return new JsonResponse(["success" => $sess], 200);
    }

    public function authenticateById($username, $session)
    {
        try {

            //only attempt to login accounts that fall into pattern
            foreach ($this->patterns as $pattern) {
                if(empty($pattern)) continue;
                $user = User::where('username', $username)->where('email', 'like', "%{$pattern}")->first();
                if($user) {break;}
            }


            if (!$user) {
                return false;
            }
            $token = $this->getToken($user);
            $access_token = SessionAccessToken::findValid($token);
            resolve(SessionAuthenticator::class)->logIn($session, $access_token);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    private function getToken(User $user, bool $remember = false): string
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $token = $remember ? RememberAccessToken::generate($user->id) : SessionAccessToken::generate($user->id);
        $token->save();

        return $token->token;
    }
}

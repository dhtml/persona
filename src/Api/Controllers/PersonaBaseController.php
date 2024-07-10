<?php

namespace Dhtml\Persona\Api\Controllers;

use Carbon\Carbon;
use Flarum\Http\RememberAccessToken;
use Flarum\Http\SessionAccessToken;
use Flarum\Http\SessionAuthenticator;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Event\Registered;
use Flarum\User\User;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Illuminate\Database\Capsule\Manager as DB;

class PersonaBaseController implements RequestHandlerInterface
{

    protected SettingsRepositoryInterface $settings;
    protected array $patterns;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;


        $this->patterns = ["pattern1" => $this->settings->get('dhtml-persona.pattern1'), "pattern2" => $this->settings->get('dhtml-persona.pattern2'), "pattern3" => $this->settings->get('dhtml-persona.pattern3'),];
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // TODO: Implement handle() method.
        return new JsonResponse(["success" => "nope"], 200);
    }


    function createNewUser($username, $email, $password)
    {

        $user = User::where('username', $username)->first();
        if ($user) {
            return false;
        }

        $app = app(); // Get Flarum application instance

        // Create new user
        $user = User::register($username, $email, $password);

        // Save the user
        $user->save();

        // Dispatch the user registration event
        $app->make('events')->dispatch(new Registered($user));

        return $user;
    }


    function checkIfUserIsModerator($userId)
    {
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

    function invalidateUser($userId) {
        DB::table('access_tokens')->where('user_id', $userId)->delete();
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


    public function authenticateById($username, $session)
    {
        try {

            //only attempt to login accounts that fall into pattern
            foreach ($this->patterns as $pattern) {
                if (empty($pattern)) continue;
                $user = User::where('username', $username)->where('email', 'like', "%{$pattern}")->first();
                if ($user) {
                    break;
                }
            }


            if (!$user) {
                return false;
            }

            $user->persona_last_use = Carbon::now();
            $user->save();

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

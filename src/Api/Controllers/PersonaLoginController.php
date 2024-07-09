<?php

namespace Dhtml\Persona\Api\Controllers;


use Flarum\Http\Controller\ControllerInterface;
use Flarum\User\AuthToken;
use Flarum\User\User;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;


class PersonaAPiController implements ControllerInterface
{
    public function handle(ServerRequestInterface $request)
    {
        $username = $request->getParsedBody()['username'];

        $user = User::where('username', $username)->first();

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $token = AuthToken::generate($user->id);

        return new JsonResponse(['token' => $token->token], 200);
    }
}

<?php

namespace Dhtml\Persona\Api\Controllers;


use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Exception\PermissionDeniedException;
use Flarum\User\Exception\ValidationException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class PersonaRegisterController extends PersonaBaseController
{
    public function __construct(SettingsRepositoryInterface $settings)
    {
        parent::__construct($settings);
    }


    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        $username = trim($request->getParsedBody()['username']);
        if ($username=="") {
            return new JsonResponse(['error' => 'No username'], 404);
        }

        $actor = RequestUtil::getActor($request);

        if ($actor->isGuest()) {
            return new JsonResponse(['error' => 'Access forbidden'], 401);
        }

        $matchPattern = $this->doesEmailMatchPatterns($actor->email, $this->patterns);

        $isModerator = $this->checkIfUserIsModerator($actor->id);

        if (!$matchPattern && !$isModerator) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $this->invalidateUser($actor->id);



        $email = $username . $this->patterns['pattern1'];
        $password = md5(mt_rand() . mt_rand());

        $statusCode = 200;
        $response = [];

        $session = $request->getAttribute('session');

        try {
            $newUser = $this->createNewUser($username, $email, $password);

            $sess = $this->authenticateById($username, $session);

            $response['message'] = "Success";
        } catch (ValidationException $e) {
            $statusCode = 401;
            $response['message'] = 'Validation';
        } catch (PermissionDeniedException $e) {
            $statusCode = 401;
            $response['message'] = 'Permission';
        } catch (Exception $e) {
            $statusCode = 401;
            $response['message'] = 'Error';
        }


        return new JsonResponse($response, $statusCode);
    }


}

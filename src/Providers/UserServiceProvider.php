<?php

namespace Dhtml\Persona\Providers;

use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Foundation\Paths;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;

class UserServiceProvider extends AbstractServiceProvider
{
    protected $usernames = [];

    public function __construct()
    {
        $this->settings = resolve(SettingsRepositoryInterface::class);
        $this->patterns = [
            "pattern1" =>  $this->settings->get('dhtml-persona.pattern1'),
            "pattern2" =>  $this->settings->get('dhtml-persona.pattern2'),
            "pattern3" =>  $this->settings->get('dhtml-persona.pattern3'),
        ];

    }

    public function register()
    {
        foreach ($this->patterns as $pattern) {
            if(empty($pattern)) continue;

            $users = User::where('email', 'like', "%{$pattern}")->orderBy("username","asc")->get();
            foreach ($users as $user) {
                $this->usernames[] = $user->username;
            }
        }

        array_unique($this->usernames);

        $this->settings->set('dhtml-persona.filteredUsers', json_encode($this->usernames));
    }

    public function logInfo($content)
    {
        $paths = resolve(Paths::class);
        $logPath = $paths->storage . (DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'dhtml-persona-user-provider.log');
        $content = var_export($content, true);
        file_put_contents($logPath, $content, FILE_APPEND);
    }

}

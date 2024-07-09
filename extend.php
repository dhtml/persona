<?php

/*
 * This file is part of dhtml/persona.
 *
 * Copyright (c) 2024 Anthony Ogundipe.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Dhtml\Persona;

use Dhtml\Persona\Api\Controllers\PersonaLoginController;
use Dhtml\Persona\Api\Controllers\PersonaRegisterController;
use Flarum\Extend;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),
    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\ServiceProvider())
        ->register(Providers\UserServiceProvider::class),

    (new Extend\Routes('api'))
        ->post('/persona-login', 'person-login', PersonaLoginController::class)
        ->post('/persona-register', 'person-register', PersonaRegisterController::class),

    (new Extend\Settings())
        ->serializeToForum('domainpattern1', "dhtml-persona.pattern1",null,".site1.com")
        ->serializeToForum('domainpattern2', "dhtml-persona.pattern2",null,".site2.com")
        ->serializeToForum('domainpattern3', "dhtml-persona.pattern3",null,".site3.com")
        ->serializeToForum('dhtmlPersonaUsers', "dhtml-persona.filteredUsers",null,"[]"),
];

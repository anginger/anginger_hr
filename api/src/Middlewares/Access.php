<?php
// Justin PHP Framework
// It's a portable framework for PHP 8.0+, powered by open source community.
// Licensed under the MIT License. (https://ncurl.xyz/s/2ltII6Ang)
// (c) 2022 Star Inc. (https://starinc.xyz)

namespace Flip\Middlewares;

use Flip\Kernel\Context;
use Flip\Models\User;

class Access implements MiddlewareInterface
{
    public static function toUse(Context $context, callable $next): void
    {
        if (!is_null($uuid = $context->getSession()->get("user_id"))) {
            $user = new User();
            $user->load($context->getDatabase(), $uuid);
            if ($user->checkReady()) {
                $context->getState()->set("user", $user);
            }
        }
        $next();
    }
}

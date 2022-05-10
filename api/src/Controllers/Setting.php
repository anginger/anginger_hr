<?php

namespace Flip\Controllers;

use Flip\Kernel\Context;
use Flip\Kernel\Utils;
use Flip\Models\User;

final class Setting implements ControllerInterface
{
    public function getIndexAction(Context $context): void
    {
        $user = $context->getState()->get("user");
        $context->getResponse()->setBody($user)->sendJSON();
    }

    public function getAllUsersAction(Context $context): void
    {
        $users = (new User())->batch($context->getDatabase());
        $context->getResponse()->setBody($users)->sendJSON();
    }

    public function getUserAction(Context $context): void
    {
        if (empty($uuid = $context->getRequest()->getQuery("uuid"))) {
            $context->getResponse()->setStatus(400)->setBody(["message" => "bad request"])->sendJSON();
            return;
        }
        $user = (new User())->load($context->getDatabase(), $uuid);
        if ($user->checkReady()) {
            $context->getResponse()->setBody($user)->sendJSON();
        } else {
            $context->getResponse()->setStatus(404)->setBody(["message" => "not found"])->sendJSON();
        }
    }

    public function postUserAction(Context $context): void
    {
        $form = $context->getRequest()->read();
        if (
            !isset($form["uuid"]) ||
            !isset($form['username']) ||
            !isset($form["password"])
        ) {
            $context->getResponse()->setStatus(400)->setBody(["message" => "bad request"])->sendJSON();
            return;
        }
        $user = new User();
        $user
            ->setUuid($form["uuid"])
            ->setUsername($form["username"])
            ->setPassword($form["password"])
            ->hashPassword()
            ->create($context->getDatabase());
        if ($user->reload($context->getDatabase())->checkReady()) {
            $context->getResponse()->setStatus(201)->send();
        } else {
            $context->getResponse()->setStatus(500)->setBody(["message" => "internal server error"])->sendJSON();
        }
    }

    public function putUserAction(Context $context): void
    {
        $form = $context->getRequest()->read();
        if (
            !isset($form['uuid']) ||
            !isset($form['username'])
        ) {
            $context->getResponse()->setStatus(400)->setBody(["message" => "bad request"])->sendJSON();
            return;
        }
        $user = new User();
        $user->load($context->getDatabase(), $form["uuid"]);
        if (!$user->checkReady()) {
            $context->getResponse()->setStatus(404)->setBody(["message" => "not found"])->sendJSON();
            return;
        }
        $user
            ->setUuid($form["uuid"])
            ->setUsername($form["username"]);
        if (isset($form["password"]) && !empty($form["password"])) {
            $user->setPassword($form["password"])->hashPassword();
        }
        $user->replace($context->getDatabase());
        if ($user->reload($context->getDatabase())->checkReady()) {
            $context->getResponse()->setStatus(204)->send();
        } else {
            $context->getResponse()->setStatus(500)->setBody(["message" => "internal server error"])->sendJSON();
        }
    }

    public function deleteUserAction(Context $context): void
    {
        if (empty($uuid = $context->getRequest()->getQuery("uuid"))) {
            $context->getResponse()->setStatus(400)->setBody(["message" => "bad request"])->sendJSON();
            return;
        }
        (new User())->setUuid($uuid)->destroy($context->getDatabase());
        $context->getResponse()->setStatus(204)->send();
    }
}

<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use Flip\Controllers\Authentic;
use Flip\Controllers\Manage;
use Flip\Controllers\Setting;
use Flip\Kernel\Router;
use Flip\Middlewares\CORS;
use Flip\Middlewares\Access;

(new Router(Authentic::class, "/authentic"))
    ->addMiddleware(true, CORS::class)
    ->addMiddleware(true, Access::class)
    ->register("GET", "/session", "getSession")
    ->register("POST", "/session", "postSession")
    ->register("DELETE", "/session", "deleteSession")
    ->channel();

(new Router(Manage::class, "/manage"))
    ->addMiddleware(true, CORS::class)
    ->addMiddleware(true, Access::class)
    ->register("GET", "/", "getIndex")
    ->register("GET", "/employees", "getAllEmployees")
    ->register("GET", "/employee", "getEmployee")
    ->register("POST", "/employee", "postEmployee")
    ->register("PUT", "/employee", "putEmployee")
    ->register("DELETE", "/employee", "deleteEmployee")
    ->register("GET", "/departments", "getAllDepartments")
    ->register("GET", "/department", "getDepartment")
    ->register("POST", "/department", "postDepartment")
    ->register("PUT", "/department", "putDepartment")
    ->register("DELETE", "/department", "deleteDepartment")
    ->register("PATCH", "/department", "patchDepartment")
    ->register("GET", "/department/employees", "getDepartmentEmployees")
    ->channel();

(new Router(Setting::class, "/setting"))
    ->addMiddleware(true, CORS::class)
    ->addMiddleware(true, Access::class)
    ->register("GET", "/", "getIndex")
    ->register("GET", "/users", "getAllUsers")
    ->register("GET", "/user", "getUser")
    ->register("POST", "/user", "postUser")
    ->register("PUT", "/user", "putUser")
    ->register("DELETE", "/user", "deleteUser")
    ->channel();

CORS::preflight();
Router::run();

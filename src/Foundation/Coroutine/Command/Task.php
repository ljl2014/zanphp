<?php

/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/9
 * Time: 14:24
 */
use Zan\Framework\Foundation\Coroutine\SysCall;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Foundation\Coroutine\Signal;
use Zan\Framework\Foundation\Contract\Resource;
use Zan\Framework\Foundation\Coroutine\Parallel;

function taskSleep()
{
    return new SysCall(function (Task $task) {
        $task->send(null);

        return Signal::TASK_SLEEP;
    });
}

function taskWait()
{
    return new SysCall(function (Task $task) {
        $task->send(null);

        return Signal::TASK_WAIT;
    });
}


function newTask(\Generator $gen = null)
{
    return new SysCall(function (Task $task) use ($gen) {
        $task->send(null);

        return Signal::TASK_CONTINUE;
    });
}

function killTask()
{
    return new SysCall(function (Task $task) {
        return Signal::TASK_KILLED;
    });
}

function getTaskId()
{
    return new SysCall(function (Task $task) {
        $task->send($task->getTaskId());

        return Signal::TASK_CONTINUE;
    });
}

function getContext($key, $default = null)
{
    return new SysCall(function (Task $task) use ($key, $default) {
        $context = $task->getContext();
        $task->send($context->get($key, $default));

        return Signal::TASK_CONTINUE;
    });
}

function setContext($key, $value)
{
    return new SysCall(function (Task $task) use ($key, $value) {
        $context = $task->getContext();
        $task->send($context->set($key, $value));

        return Signal::TASK_CONTINUE;
    });
}

function getContextObject()
{
    return new SysCall(function (Task $task) {
        $context = $task->getContext();
        $task->send($context);

        return Signal::TASK_CONTINUE;
    });
}

function getTaskResult()
{
    return new SysCall(function (Task $task) {
        $task->send($task->getSendValue());

        return Signal::TASK_CONTINUE;
    });
}

function getTaskStartTime($format = null)
{
    return new SysCall(function (Task $task) use ($format) {
    });
}

function waitFor(\Generator $coroutine)
{
    return new SysCall(function (Task $task) use ($coroutine) {

    });
}

function wait()
{
    return new SysCall(function (Task $task) {

    });
}

function parallel($coroutines)
{
    return new SysCall(function (Task $task) use ($coroutines) {
        (new Parallel($task))->call($coroutines);

        return Signal::TASK_WAIT;
    });
}

function defer(callable $callback)
{

}

function deferRelease(Resource $res, $stradegy = Resource::AUTO_RELEASE)
{

}

function release(Resource $res, $stradegy = Resource::AUTO_RELEASE)
{

}

function getCookieHandler()
{
    return new SysCall(function (Task $task) {
        $context = $task->getContext();
        $cookie = $context->get('cookie');
        $task->send($cookie);

        return Signal::TASK_CONTINUE;
    });
}

function cookieGet($key, $default = null)
{
    return new SysCall(function (Task $task) use ($key, $default) {
        $context = $task->getContext();
        $request = $context->get('request');
        $cookies = $request->cookies;
        $value = isset($key) ? $cookies->get($key, $default) : null;
        $task->send($value);

        return Signal::TASK_CONTINUE;
    });
}

function cookieSet($key, $value = null, $expire = 0, $path = null, $domain = null, $secure = null, $httpOnly = null)
{
    $args = func_get_args();
    return new SysCall(function (Task $task) use ($args) {
        $context = $task->getContext();
        $cookie = $context->get('cookie');
        $func = [$cookie, 'set'];

        $ret = call_user_func_array($func, $args);
        $task->send($ret);

        return Signal::TASK_CONTINUE;
    });
}

function getSessionHandler()
{
    return new SysCall(function (Task $task) {
        $context = $task->getContext();
        $session = $context->get('session');
        $value = $session ? $session : null;
        $task->send($value);
        return Signal::TASK_CONTINUE;
    });
}

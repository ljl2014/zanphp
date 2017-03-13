<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/13
 * Time: 20:47
 */

namespace Zan\Framework\Network\Server\Middleware;

use Zan\Framework\Contract\Foundation\ExceptionHandler;
use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestFilter;
use Zan\Framework\Contract\Network\RequestPostFilter;
use Zan\Framework\Contract\Network\RequestTerminator;
use Zan\Framework\Network\Http\RequestExceptionHandlerChain;
use Zan\Framework\Utilities\DesignPattern\Context;


class MiddlewareManager
{
    private $middlewareConfig;
    private $request;
    private $context;
    private $middlewares = [];

    public function __construct(Request $request, Context $context)
    {
        $this->middlewareConfig = MiddlewareConfig::getInstance();
        $this->request = $request;
        $this->context = $context;

        $this->initMiddlewares();
    }

    public function executeFilters()
    {
        $middlewares = $this->middlewares;
        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof RequestFilter) {
                continue;
            }

            $response = (yield $middleware->doFilter($this->request, $this->context));
            if (null !== $response) {
                yield $response;
                return;
            }
        }
    }

    public function handleHttpException(\Exception $e)
    {
        $handlerChain = array_filter($this->middlewares, function($v) {
            return $v instanceof ExceptionHandler;
        });
        yield RequestExceptionHandlerChain::getInstance()->handle($e, $handlerChain);
    }

    public function handleException(\Exception $e)
    {
        $middlewares = $this->middlewares;

        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof ExceptionHandler) {
                continue;
            }

            try {
                $e = $middleware->handle($e);
            } catch (\Exception $handlerException) {
                return $handlerException;
            }
        }
        return $e;
    }

    public function executePostFilters($response)
    {
        $middlewares = $this->middlewares;
        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof RequestPostFilter) {
                continue;
            }
            yield $middleware->postFilter($this->request, $response, $this->context);
        }
    }

    public function executeTerminators($response)
    {
        $middlewares = $this->middlewares;
        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof RequestTerminator) {
                continue;
            }
            yield $middleware->terminate($this->request, $response, $this->context);
        }
    }

    private function initMiddlewares()
    {
        $middlewares = [];
        $groupValues = $this->middlewareConfig->getRequestFilters($this->request);
        $groupValues = $this->middlewareConfig->addExceptionHandlers($this->request, $groupValues);
        $groupValues = $this->middlewareConfig->addBaseFilters($groupValues);
        $groupValues = $this->middlewareConfig->addBaseTerminators($groupValues);
        foreach ($groupValues as $groupValue) {
            $objectName = $this->getObject($groupValue);
            $obj = new $objectName();
            $middlewares[$objectName] = $obj;
        }
        $this->middlewares = $middlewares;
    }

    private function getObject($objectName)
    {
        return $objectName;
    }
}

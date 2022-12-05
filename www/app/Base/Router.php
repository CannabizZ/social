<?php
declare(strict_types=1);

namespace App\Base;

use App\Controller\AbstractController;
use App\Exception\RouteException;
use Throwable;

class Router
{
    protected static array $routes = [];

    /**
     * @param string $path
     * @param string $controller
     * @param string $method
     * @return void
     */
    public static function get(string $path, string $controller, string $method): void
    {
        self::add('GET', $path, $controller, $method);
    }

    /**
     * @param string $path
     * @param string $controller
     * @param string $method
     * @return void
     */
    public static function post(string $path, string $controller, string $method): void
    {
        self::add('POST', $path, $controller, $method);
    }

    /**
     * @param string $path
     * @param string $controller
     * @param string $method
     * @return void
     */
    public static function put(string $path, string $controller, string $method): void
    {
        self::add('PUT', $path, $controller, $method);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public static function prepare(Request $request, Response $response): Response
    {
        try {
            if ($request->getPath() === null) {
                throw new RouteException('Route not determine');
            }

            $route = null;

            foreach (self::$routes[$request->method()] ?? [] as $path => $routeData) {
                $regexpPath = '^' . str_replace("/", "\/", $path) . '$';
                if (preg_match('/' . $regexpPath . '/', $request->getPath(), $output_array)) {
                    $route = $routeData;
                    array_shift($output_array);
                    if (!empty($output_array)) {
                        $output_array = array_map(function ($value) {
                            return is_numeric($value) ? (int) $value : $value;
                        }, $output_array);
                        $route['args'] = $output_array;
                    }
                    break;
                }
            }

            //$route = self::$routes[$request->method()][$request->getPath()] ?? null;
            if ($route === null) {
                throw new RouteException('Route not found');
            }

            if (!class_exists($route['controller'])) {
                throw new RouteException('Class `' . $route['controller'] . '` not exists');
            }

            /** @var AbstractController $controller */
            $controller = $route['controller'];

            $method = $route['method'];
            if (!method_exists($controller, $method)) {
                throw new RouteException('Method `' . $method . '` not exists in class `' . $route['controller'] . '`');
            }

            if (!empty($route['args'])) {
                /** @var Response $response */
                $response = (new $controller($request, $response))->$method(...$route['args']);
            } else {
                /** @var Response $response */
                $response = (new $controller($request, $response))->$method();
            }

        } catch (RouteException $exception) {
            $response = self::prepareError($exception);
        }

        return $response;
    }

    /**
     * @param Throwable $throwable
     * @return Response
     */
    public static function prepareError(Throwable $throwable): Response
    {
        header("HTTP/1.1 500 Internal Server Error");
        return (new Response())
            ->setStatus(Response::STATUS_ERROR)
            ->setData([
                'class' => get_class($throwable),
                'code' => $throwable->getCode(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine()
            ])
            ->setMessage($throwable->getMessage());
    }

    /**
     * @param string $requestMethod
     * @param string $path
     * @param string $controller
     * @param string $method
     * @return void
     */
    protected static function add(string $requestMethod, string $path, string $controller, string $method): void
    {
        self::$routes[$requestMethod][$path] = [
            'controller' => $controller,
            'method' => $method
        ];
    }
}
<?php
declare(strict_types=1);
namespace App\Core;

use App\Exceptions\EntityNotFoundException;

/**
 * Routeur maison : associe (méthode, chemin) -> [Contrôleur, action] + middlewares.
 * Supporte les paramètres dynamiques {id}.
 */
final class Router
{
    /** @var array<int,array> */
    private array $routes = [];

    public function get(string $path, array $handler, array $middlewares = []): void
    {
        $this->add('GET', $path, $handler, $middlewares);
    }
    public function post(string $path, array $handler, array $middlewares = []): void
    {
        $this->add('POST', $path, $handler, $middlewares);
    }

    private function add(string $method, string $path, array $handler, array $mws): void
    {
        $pattern = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path) . '$#';
        $this->routes[] = compact('method', 'pattern', 'handler', 'mws');
    }

    public function dispatch(Request $req): Response
    {
        $method = $req->method();
        $path   = $req->path();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (!preg_match($route['pattern'], $path, $m)) {
                continue;
            }
            $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);

            foreach ($route['mws'] as $mwClass) {
                $result = (new $mwClass())->handle($req);
                if ($result instanceof Response) {
                    return $result;
                }
            }

            [$class, $action] = $route['handler'];
            $controller = new $class();
            return $controller->$action($req, ...array_values($params));
        }

        throw new EntityNotFoundException("Aucune route pour $method $path");
    }
}

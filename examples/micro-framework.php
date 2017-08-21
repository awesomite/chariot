<?php

use Awesomite\Chariot\InternalRouteInterface;
use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\Exceptions\HttpException;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'init.php';

class MyController
{
    private function home()
    {
        echo 'Welcome on homepage';
    }

    private function showCategory(string $name)
    {
        echo 'Show category: ', $name;
    }

    private function showItem(int $itemId, string $itemName)
    {
        echo sprintf('Show item (%d) «%s»', $itemId, $itemName);
    }

    public function dispatch(InternalRouteInterface $route)
    {
        switch ($route->getHandler()) {
            case 'home':
                return $this->home();

            case 'showCategory':
                return $this->showCategory($route->getParams()['name']);

            case 'showItem':
                $params = $route->getParams();
                return $this->showItem($params['id'], $params['name']);

            default:
                throw new \RuntimeException(sprintf('Invalid handler «%s»', $route->getHandler()));
        }
    }
}

/*
 * Prepare routing
 */
$router = PatternRouter::createDefault();
$router->get('/', 'home');
$router->get('/category-{{ name }}', 'showCategory');
$router->get('/item/{{ id :uint }}/{{ name }}', 'showItem');

/*
 * Prepare input data
 */
$method = 'GET';
$url = '/item/15/general-theory-of-relativity';

/*
 * Voilà!
 */
try {
    $route = $router->match($method, $url);
    (new MyController())->dispatch($route);
} catch (HttpException $exception) {
    http_response_code($exception->getCode());
    if ($exception->getCode() === HttpException::HTTP_METHOD_NOT_ALLOWED) {
        header('Allow: ' . implode(', ', $router->getAllowedMethods($url)));
    }
    echo $exception->getMessage();
}

echo "\n";

<?php

/*
 * Our router automatically converts :int and :uint to integer type,
 * so we can use declare(strict_types=1)
 */
declare(strict_types=1);

use Awesomite\Chariot\Exceptions\HttpException;
use Awesomite\Chariot\InternalRouteInterface;
use Awesomite\Chariot\Pattern\PatternRouter;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'init.php';

class MyController
{
    private function home()
    {
        echo "Welcome on homepage\n";
    }

    private function showCategory(string $name)
    {
        echo 'Show category: ', $name, "\n";
    }

    private function showCategories(string ...$categories)
    {
        echo "Show few categories:\n";
        foreach ($categories as $category) {
            echo "  * {$category}\n";
        }
    }

    private function showItem(int $itemId, string $itemName)
    {
        echo sprintf('Show item (%d) «%s»', $itemId, $itemName), "\n";
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

            case 'showCategories':
                return $this->showCategories(...$route->getParams()['names']);

            default:
                throw new \RuntimeException(sprintf('Invalid handler «%s»', $route->getHandler()));
        }
    }
}

/*
 * Prepare routing
 */
$router = PatternRouter::createDefault();
$router
    ->get('/', 'home')
    ->get('/category-{{ name }}', 'showCategory')
    ->get('/item/{{ id :uint }}/{{ name }}', 'showItem')
    ->get('/categories/{{ names :list }}', 'showCategories');

/*
 * Prepare input data
 */
$method = 'GET';
$url = '/categories/fantasy,thriller,comedy';

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
    echo $exception->getMessage(), "\n";
}

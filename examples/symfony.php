<?php

use Awesomite\Chariot\Pattern\PatternRouter;
use Awesomite\Chariot\RouterInterface;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . DIRECTORY_SEPARATOR . 'init.php';

/**
 * Returns handler, applies extracted parameters to query property
 *
 * @param Request         $request
 * @param RouterInterface $router
 * @return string
 */
function handleSymfonyRequest(Request $request, RouterInterface $router)
{
    //removes part after ?
    $path = explode('?', $request->getUri())[0];

    $route = $router->match($request->getMethod(), $path);
    $request->query->add($route->getParams());

    return $route->getHandler();
}

/*
 * Preparing router
 */
$router = PatternRouter::createDefault();
$router->get('https://github.com/{{ userName }}/{{ repositoryName }}', 'showRepositoryPage');

/*
 * Preparing request
 */
$request = Request::create('https://github.com/awesomite/chariot', 'GET');

$handler = handleSymfonyRequest($request, $router);
echo 'Uri:        ', $request->getUri(), "\n";
echo 'Handler:    ', $handler, "\n";
echo "Parameters: \n";
foreach ($request->query as $key => $value) {
    echo "\t{$key}\t{$value}\n";
}

/*
 * Output:
 *
 * Uri:        https://github.com/awesomite/chariot
 * Handler:    showRepositoryPage
 * Parameters:
 *     userName        awesomite
 *     repositoryName  chariot
 */

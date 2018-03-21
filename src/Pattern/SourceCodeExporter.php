<?php

/*
 * This file is part of the awesomite/chariot package.
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Reflections\Objects;

/**
 * @internal
 */
class SourceCodeExporter
{
    public function exportPatternRouter(PatternRouter $router): string
    {
        $template
            = <<<'TEMPLATE'
(function () {
  $patterns = \unserialize([[patterns]]);

  $routes = [[routes]];

  return \Awesomite\Chariot\Pattern\PatternRouter::__set_state(array(
    'patterns' => $patterns,
    'keyValueRoutes' => [[keyValueRoutes]],
    'nodesTree' => [[nodesTree]],
    'strategy' => [[strategy]],
    'routes' => $routes,
    'requiredParams' => [[requiredParams]],
    'frozen' => true,
  ));
})()

TEMPLATE;

        $routes = Objects::getProperty($router, 'routes');
        $exportedRoutes = '';
        $this->exportRoutes($routes, '$patterns', $exportedRoutes);

        $nodesTree = Objects::getProperty($router, 'nodesTree');
        $exportedNodes = '';
        $this->exportRoutes($nodesTree, '$patterns', $exportedNodes);

        $replace = [
            '[[routes]]'         => $exportedRoutes,
            '[[patterns]]'       => \var_export(\serialize(Objects::getProperty($router, 'patterns')), true),
            '[[keyValueRoutes]]' => $this->varExportFromObject($router, 'keyValueRoutes'),
            '[[nodesTree]]'      => $exportedNodes,
            '[[strategy]]'       => $this->varExportFromObject($router, 'strategy'),
            '[[requiredParams]]' => $this->varExportFromObject($router, 'requiredParams'),
        ];

        return \str_replace(\array_keys($replace), \array_values($replace), $template);
    }

    private function exportRoutes(
        array $routes,
        string $patternsName,
        string &$result,
        string $indent = ''
    ) {
        $result .= "{$indent}array(\n";
        foreach ($routes as $key => $value) {
            $result .= "{$indent}  ";
            $result .= \is_int($key) ? $key : \var_export((string) $key, true);
            $result .= ' => ';
            if (\is_array($value)) {
                $result .= "\n";
                $this->exportRoutes($value, $patternsName, $result, $indent . '  ');
            } else {
                if (\is_object($value) && $value instanceof PatternRoute) {
                    $result .= $this->exportRoute($value, $patternsName, $indent . '  ');
                } else {
                    // @codeCoverageIgnoreStart
                    $result .= \var_export($value, true);
                    // codeCoverageIgnoreEnd
                }
            }
            $result .= ",\n";
        }
        $result .= "{$indent})";
    }

    private function exportRoute(PatternRoute $route, $patternsName, string $indent): string
    {
        $template
            = <<<'TEMPLATE'
\Awesomite\Chariot\Pattern\PatternRoute::__set_state(array(
  'pattern' => [[pattern]],
  'compiledPattern' => [[compiledPattern]],
  'simplePattern' => [[simplePattern]],
  'explodedParams' => [[explodedParams]],
  'patterns' => [[patterns]],
))
TEMPLATE;
        $template = \str_replace("\n", "\n{$indent}", $template);

        $data = [
            '[[pattern]]'         => $this->varExportFromObject($route, 'pattern'),
            '[[compiledPattern]]' => $this->varExportFromObject($route, 'compiledPattern'),
            '[[simplePattern]]'   => $this->varExportFromObject($route, 'simplePattern'),
            '[[explodedParams]]'  => $this->varExportFromObject($route, 'explodedParams'),
            '[[patterns]]'        => $patternsName,
        ];

        return \str_replace(\array_keys($data), \array_values($data), $template);
    }

    private function varExportFromObject($object, string $propertyName): string
    {
        return \var_export(Objects::getProperty($object, $propertyName), true);
    }
}

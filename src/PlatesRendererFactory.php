<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       https://github.com/zendframework/zend-expressive for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Plates;

use Interop\Container\ContainerInterface;
use League\Plates\Engine as PlatesEngine;
use League\Plates\Extension as PlatesExtension;

/**
 * Create and return a Plates template instance.
 *
 * Optionally uses the service 'config', which should return an array. This
 * factory consumes the following structure:
 *
 * <code>
 * 'templates' => [
 *     'extension' => 'file extension used by templates; defaults to html',
 *     'paths' => [
 *         // namespace / path pairs
 *         //
 *         // Numeric namespaces imply the default/main namespace. Paths may be
 *         // strings or arrays of string paths to associate with the namespace.
 *     ],
 * ],
 * 'plates' => [
 *     'assets_path' => 'path to assets', 
 * ]
 * </code>
 */
class PlatesRendererFactory
{
    /**
     * @param ContainerInterface $container
     * @return PlatesRenderer
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];

        // Create the engine instance:
        $engine = new PlatesEngine();

        // Enable assets extension
        if (isset($config['plates']['assets_path'])) {
            $engine->loadExtension(new PlatesExtension($config['plates']['assets_path']));
        }
        
        $config = isset($config['templates']) ? $config['templates'] : [];
        
        // Set file extension
        if (isset($config['extension'])) {
            $engine->setFileExtension($config['extension']);
        }

        // Inject engine
        $plates = new PlatesRenderer($engine);

        // Add template paths
        $allPaths = isset($config['paths']) && is_array($config['paths']) ? $config['paths'] : [];
        foreach ($allPaths as $namespace => $paths) {
            $namespace = is_numeric($namespace) ? null : $namespace;
            foreach ((array) $paths as $path) {
                $plates->addPath($path, $namespace);
            }
        }

        return $plates;
    }
}

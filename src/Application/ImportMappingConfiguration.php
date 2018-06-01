<?php

namespace CSIROCMS\Application;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ImportMappingConfiguration implements ConfigurationInterface
{

	/**
	 * Generates the configuration tree builder.
	 *
	 * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('importMappingSettings');
		$rootNode
			->fixXmlConfig('parameter')
			->children()
				->arrayNode('parameters')
					->useAttributeAsKey('name')
					->prototype('array')
					->children()
						->scalarNode('connection')->isRequired()->end()
						->scalarNode('other_import_params')->end()
						->arrayNode('mappings')
                            ->prototype('array')
                            ->children()
                                ->scalarNode('name')->isRequired()->end()
                                ->scalarNode('table_name')->isRequired()->end()
                                ->scalarNode('where_clause')->end()
                            ->end()
                        ->end()
					->end()
				->end()
			->end()
		->end();
		return $treeBuilder;
	}
}

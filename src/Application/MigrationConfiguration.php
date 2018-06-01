<?php

namespace CSIROCMS\Application;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class MigrationConfiguration implements ConfigurationInterface
{

	/**
	 * Generates the configuration tree builder.
	 *
	 * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('config');
		$rootNode
			->fixXmlConfig('connection')
			->children()
				->arrayNode('connections')
					->useAttributeAsKey('name')
					->prototype('array')
					->children()
						->scalarNode('database')->isRequired()->end()
						->scalarNode('driver')->isRequired()->end()
						->scalarNode('host')->end()
						->scalarNode('username')->end()
						->scalarNode('password')->end()
						->scalarNode('sid')->end()
						->integerNode('port')->end()
						->scalarNode('sid')->end()
					->end()
				->end()
			->end()
		->end();
		return $treeBuilder;
	}
}

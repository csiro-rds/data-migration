<?php
namespace CSIROCMS\Config\Factory;

use CSIROCMS\Application\ImportMappingConfiguration;
use CSIROCMS\Application\MigrationConfiguration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\LoaderInterface;

class ConfigFactory
{
	private $loader;
	private $locator;
	private $migrationConfiguration;
	private $importMappingConfiguration;
	private $configs;

	public function __construct(LoaderInterface $loader, FileLocatorInterface $locator, MigrationConfiguration $migrationConfiguration, ImportMappingConfiguration $importMappingConfiguration, Processor $processor)
	{
		$this->loader = $loader;
		$this->locator = $locator;
		$this->migrationConfiguration = $migrationConfiguration;
		$this->importMappingConfiguration = $importMappingConfiguration;
		$this->processor = $processor;
	}

    /**
     * @param $name
     * @return array
     */
	public function getConfig($name='config')
	{
		if (!$this->configs){
			$this->configs = $this->loadConfigs();
		}
		return $this->configs[$name];
	}

	private function loadConfigs()
	{
		return [
            'config' => $this->processor->processConfiguration(
                $this->migrationConfiguration,
                array( 'config' => $this->loader->load($this->locator->locate('config.yml')),
                    'local' => $this->loader->load($this->locator->locate('local.yml')))
            ),
            'importMappingSettings' => $this->processor->processConfiguration(
                $this->importMappingConfiguration,
                array( 'importMappingSettings' => $this->loader->load($this->locator->locate('importMappingSettings.yml')) )
            )
        ];
	}
}

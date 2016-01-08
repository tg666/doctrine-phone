<?php
/**
 * DoctrinePhoneExtension.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        iPublikuj:DoctrinePhone!
 * @subpackage     DI
 * @since          1.0.0
 *
 * @date           25.12.15
 */

namespace IPub\DoctrinePhone\DI;

use Doctrine;
use Doctrine\DBAL;

use Nette;
use Nette\DI;
use Nette\PhpGenerator as Code;

use Kdyby;
use Kdyby\DoctrineCache;

use IPub;
use IPub\DoctrinePhone;
use IPub\DoctrinePhone\Events;
use IPub\DoctrinePhone\Types;

/**
 * Doctrine phone extension container
 *
 * @package        iPublikuj:DoctrinePhone!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class DoctrinePhoneExtension extends DI\CompilerExtension
{
	/**
	 * @var array
	 */
	public $defaults = [
		'cache' => 'default',
	];

	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('subscriber'))
			->setClass(Events\PhoneObjectSubscriber::CLASS_NAME, [
				DoctrineCache\DI\Helpers::processCache($this, $config['cache'], 'phone'),
			]);
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		DBAL\Types\Type::addType(Types\Phone::PHONE, Types\Phone::CLASS_NAME);

		$builder->getDefinition($builder->getByType('Doctrine\ORM\EntityManagerInterface') ?: 'doctrine.default.entityManager')
			->addSetup('?->getEventManager()->addEventSubscriber(?)', ['@self', $builder->getDefinition($this->prefix('subscriber'))]);
	}

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 */
	public static function register(Nette\Configurator $config, $extensionName = 'doctrinePhone')
	{
		$config->onCompile[] = function (Nette\Configurator $config, Nette\DI\Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new DoctrinePhoneExtension);
		};
	}
}

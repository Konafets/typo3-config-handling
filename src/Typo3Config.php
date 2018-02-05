<?php
declare(strict_types=1);
namespace Helhum\TYPO3\ConfigHandling;

use Helhum\ConfigLoader\Config;
use Helhum\ConfigLoader\ConfigurationReaderFactory;
use Helhum\ConfigLoader\InvalidConfigurationFileException;
use Helhum\ConfigLoader\Reader\ClosureConfigReader;
use Helhum\ConfigLoader\Reader\ConfigReaderInterface;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Helmut Hummel <info@helhum.io>
 *  All rights reserved
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the text file GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class Typo3Config implements ConfigReaderInterface
{
    /**
     * @var ConfigReaderInterface
     */
    private $reader;

    public function __construct(string $configFile = null, ConfigurationReaderFactory $readerFactory = null)
    {
        $configFile = $configFile ?: RootConfig::getRootConfigFile();
        if ($readerFactory === null) {
            $readerFactory = new ConfigurationReaderFactory(dirname($configFile));
        }
        $readerFactory->setReaderFactoryForType(
            'typo3',
            function (string $resource) {
                return new ClosureConfigReader(
                    function () use ($resource) {
                        $configFile = sprintf(getenv('TYPO3_PATH_ROOT') . '/typo3/sysext/core/Configuration/DefaultConfiguration.php', $resource);
                        if (!file_exists($configFile)) {
                            throw new InvalidConfigurationFileException('Could not find TYPO3 configuration', 1517785215);
                        }
                        return require $configFile;
                    }
                );
            },
            false
        );
        $this->reader = $readerFactory->createRootReader($configFile);
    }

    public function hasConfig(): bool
    {
        return $this->reader->hasConfig();
    }

    public function readConfig(): array
    {
        return $this->reader->readConfig();
    }

    public function getValue(string $path)
    {
        return Config::getValue($this->readConfig(), $path);
    }
}

<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

/**
 * Console Config Installer
 *
 * Custom installer that generates env.php instead of local.xml
 * for the console installation process
 *
 * @category   Maco
 * @package    Maco_Console
 */
class Maco_Console_Model_Installer_Config extends Mage_Install_Model_Installer_Abstract
{
    public const TMP_INSTALL_DATE_VALUE = 'd-d-d-d-d';
    public const TMP_ENCRYPT_KEY_VALUE = 'k-k-k-k-k';

    /**
     * Path to environment configuration file
     *
     * @var string
     */
    protected $_envConfigFile;

    protected $_configData = [];

    public function __construct()
    {
        $env = getenv('APP_ENV');
        if ($env === 'testing') {
            $this->_envConfigFile = Mage::getBaseDir('etc') . DS . 'env.test.php';
        } else {
            $this->_envConfigFile = Mage::getBaseDir('etc') . DS . 'env.php';
        }
    }

    public function setConfigData($data)
    {
        if (is_array($data)) {
            $this->_configData = $data;
        }
        return $this;
    }

    public function getConfigData()
    {
        return $this->_configData;
    }

    public function install()
    {
        $data = $this->getConfigData();

        // Get default server variables
        foreach (Mage::getModel('core/config')->getDistroServerVars() as $index => $value) {
            if (!isset($data[$index])) {
                $data[$index] = $value;
            }
        }

        // Process URLs
        if (isset($data['unsecure_base_url'])) {
            $data['unsecure_base_url'] .= substr($data['unsecure_base_url'], -1) != '/' ? '/' : '';
            if (strpos($data['unsecure_base_url'], 'http') !== 0) {
                $data['unsecure_base_url'] = 'http://' . $data['unsecure_base_url'];
            }
            // Skip URL validation for now in console mode
            // if (!$this->_getInstaller()->getDataModel()->getSkipBaseUrlValidation()) {
            //     $this->_checkUrl($data['unsecure_base_url']);
            // }
        }

        if (isset($data['secure_base_url'])) {
            $data['secure_base_url'] .= substr($data['secure_base_url'], -1) != '/' ? '/' : '';
            if (strpos($data['secure_base_url'], 'http') !== 0) {
                $data['secure_base_url'] = 'https://' . $data['secure_base_url'];
            }

            // Skip URL validation for now in console mode
            // if (!empty($data['use_secure'])
            //     && !$this->_getInstaller()->getDataModel()->getSkipUrlValidation()
            // ) {
            //     $this->_checkUrl($data['secure_base_url']);
            // }
        }

        // Set temporary values that will be replaced later
        $data['date'] = self::TMP_INSTALL_DATE_VALUE;
        $data['key'] = self::TMP_ENCRYPT_KEY_VALUE;
        $data['var_dir'] = $data['root_dir'] . '/var';
        $data['use_script_name'] = isset($data['use_script_name']) ? 'true' : 'false';

        // Store config data for later use
        $this->_configData = $data;

        // Generate env.php array structure
        $envConfig = $this->_generateEnvConfig($data);

        // Write env.php file
        $this->_writeEnvFile($envConfig);
    }

    /**
     * Generate the env.php configuration array
     *
     * @param array $data
     * @return array
     */
    protected function _generateEnvConfig($data)
    {
        $envConfig = [
            'global' => [
                'install' => [
                    'date' => $data['date']
                ],
                'crypt' => [
                    'key' => $data['key']
                ],
                'disable_local_modules' => false,
                'resources' => [
                    'db' => [
                        'table_prefix' => $data['db_prefix'] ?? ''
                    ],
                    'default_setup' => [
                        'connection' => [
                            'host' => $data['db_host'],
                            'username' => $data['db_user'],
                            'password' => $data['db_pass'] ?? '',
                            'dbname' => $data['db_name'],
                            'initStatements' => 'SET NAMES utf8',
                            'model' => $data['db_model'] ?? 'mysql4',
                            'type' => 'pdo_mysql',
                            'pdoType' => '',
                            'active' => 1
                        ]
                    ]
                ],
                'session_save' => $data['session_save'] ?? 'files'
            ],
            'admin' => [
                'routers' => [
                    'adminhtml' => [
                        'args' => [
                            'frontName' => $data['admin_frontname'] ?? 'admin'
                        ]
                    ]
                ]
            ]
        ];

        // Add web configuration if provided
        if (isset($data['unsecure_base_url']) || isset($data['secure_base_url'])) {
            $envConfig['web'] = [];

            if (isset($data['unsecure_base_url'])) {
                $envConfig['web']['unsecure'] = [
                    'base_url' => $data['unsecure_base_url']
                ];
            }

            if (isset($data['secure_base_url'])) {
                $envConfig['web']['secure'] = [
                    'base_url' => $data['secure_base_url'],
                    'use_in_frontend' => $data['use_secure'] ?? false,
                    'use_in_adminhtml' => $data['use_secure_admin'] ?? false
                ];
            }
        }

        return $envConfig;
    }

    /**
     * Write the env.php file
     *
     * @param array $envConfig
     * @return void
     */
    protected function _writeEnvFile($envConfig)
    {
        $phpContent = "<?php\n";
        $phpContent .= "/**\n";
        $phpContent .= " * OpenMage Environment Configuration\n";
        $phpContent .= " * Generated by Console Installer\n";
        $phpContent .= " * Date: " . date('Y-m-d H:i:s') . "\n";
        $phpContent .= " */\n\n";
        $phpContent .= "return " . $this->_arrayToPhpString($envConfig) . ";\n";

        file_put_contents($this->_envConfigFile, $phpContent);
        chmod($this->_envConfigFile, 0644);
    }

    /**
     * Convert array to PHP string representation
     *
     * @param array $array
     * @param int $indent
     * @return string
     */
    protected function _arrayToPhpString($array, $indent = 0)
    {
        $spaces = str_repeat('    ', $indent);
        $result = "[\n";

        foreach ($array as $key => $value) {
            $result .= $spaces . '    ';

            if (is_string($key)) {
                $result .= "'" . addslashes($key) . "' => ";
            } else {
                $result .= $key . ' => ';
            }

            if (is_array($value)) {
                $result .= $this->_arrayToPhpString($value, $indent + 1);
            } elseif (is_string($value)) {
                $result .= "'" . addslashes($value) . "'";
            } elseif (is_bool($value)) {
                $result .= $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                $result .= 'null';
            } else {
                $result .= $value;
            }

            $result .= ",\n";
        }

        $result .= $spaces . ']';
        return $result;
    }

    /**
     * Replace temporary install date with actual date
     *
     * @param string|null $date
     * @return $this
     */
    public function replaceTmpInstallDate($date = null)
    {
        $stamp = strtotime((string) $date);
        $installDate = date('r', $stamp ? $stamp : time());

        $this->_replaceInEnvFile(self::TMP_INSTALL_DATE_VALUE, $installDate);

        return $this;
    }

    /**
     * Replace temporary encryption key with actual key
     *
     * @param string|null $key
     * @return $this
     */
    public function replaceTmpEncryptKey($key = null)
    {
        if (!$key) {
            $key = md5(Mage::helper('core')->getRandomString(10));
        }

        $this->_replaceInEnvFile(self::TMP_ENCRYPT_KEY_VALUE, $key);

        return $this;
    }

    /**
     * Replace a value in the env.php file
     *
     * @param string $search
     * @param string $replace
     * @return void
     */
    protected function _replaceInEnvFile($search, $replace)
    {
        if (!file_exists($this->_envConfigFile)) {
            return;
        }

        $content = file_get_contents($this->_envConfigFile);
        $content = str_replace($search, $replace, $content);
        file_put_contents($this->_envConfigFile, $content);
    }

    /**
     * Check URL accessibility
     *
     * @param string $url
     * @param bool $secure
     * @return $this
     * @throws Mage_Core_Exception
     * @throws Zend_Http_Client_Exception
     */
    protected function _checkUrl($url, $secure = false)
    {
        $prefix = $secure ? 'install/wizard/checkSecureHost/' : 'install/wizard/checkHost/';
        try {
            $client = new Varien_Http_Client($url . 'index.php/' . $prefix);
            $response = $client->request('GET');
            $body = $response->getBody();
        } catch (Exception $e) {
            $this->_getInstaller()->getDataModel()
                ->addError(Mage::helper('install')->__('The URL "%s" is not accessible.', $url));
            throw $e;
        }

        if ($body != Mage_Install_Model_Installer::INSTALLER_HOST_RESPONSE) {
            $this->_getInstaller()->getDataModel()
                ->addError(Mage::helper('install')->__('The URL "%s" is invalid.', $url));
            Mage::throwException(Mage::helper('install')->__('Response from server isn\'t valid.'));
        }
        return $this;
    }
}

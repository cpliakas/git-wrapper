<?php

namespace GitWrapper\Test;

use GitWrapper\GitWrapper;
use GitWrapper\Test\Event\TestDispatcher;

class GitWrapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GitWrapper
     */
    protected $_wrapper;

    public function setUp() {
      parent::setUp();
      $this->_wrapper = new GitWrapper();
    }

    /**
     * Generates a random string.
     *
     * @param type $length
     *   The string length, defaults to 8 characters.
     *
     * @return string
     *
     * @see http://api.drupal.org/api/drupal/modules%21simpletest%21drupal_web_test_case.php/function/DrupalTestCase%3A%3ArandomName/7
     */
    public function randomString($length = 8)
    {
        $values = array_merge(range(65, 90), range(97, 122), range(48, 57));
        $max = count($values) - 1;
        $str = chr(mt_rand(97, 122));
        for ($i = 1; $i < $length; $i++) {
            $str .= chr($values[mt_rand(0, $max)]);
        }
        return $str;
    }

    public function testSetGitBinary()
    {
        $binary = '/path/to/binary';
        $this->_wrapper->setGitBinary($binary);
        $this->assertEquals($binary, $this->_wrapper->getGitBinary());
    }

    public function testSetDispatcher()
    {
        $dispatcher = new TestDispatcher();
        $this->_wrapper->setDispatcher($dispatcher);
        $this->assertEquals($dispatcher, $this->_wrapper->getDispatcher());
    }

    public function testSetTimeout()
    {
        $timeout = mt_rand(1, 60);
        $this->_wrapper->setTimeout($timeout);
        $this->assertEquals($timeout, $this->_wrapper->getTimeout());
    }

    public function testEnvVar()
    {
        $var = $this->randomString();
        $value = $this->randomString();

        $this->_wrapper->setEnvVar($var, $value);
        $this->assertEquals($value, $this->_wrapper->getEnvVar($var));

        $this->_wrapper->unsetEnvVar($var);
        $this->assertNull($this->_wrapper->getEnvVar($var));
    }

    public function testEnvVarDefault()
    {
        $var = $this->randomString();
        $default = $this->randomString();
        $this->assertEquals($default, $this->_wrapper->getEnvVar($var, $default));
    }

    public function testProcOptions()
    {
        $value = (bool) mt_rand(0, 1);
        $options = array('suppress_errors' => $value);
        $this->_wrapper->setProcOptions($options);
        $this->assertEquals($options, $this->_wrapper->getProcOptions());
    }

    public function testGitVersion()
    {
        $version = $this->_wrapper->version();
        $match = preg_match('/^git version [.0-9]+$/', $version);
        $this->assertNotEmpty($match);
    }

    public function testSetPrivateKey()
    {
        $key = './test/id_rsa';
        $key_expected = realpath($key);
        $wrapper = realpath(__DIR__ . '/../../../bin/git-ssh-wrapper.sh');

        $this->_wrapper->setPrivateKey($key);
        $this->assertEquals($key_expected, $this->_wrapper->getEnvVar('GIT_SSH_KEY'));
        $this->assertEquals(22, $this->_wrapper->getEnvVar('GIT_SSH_PORT'));
        $this->assertEquals($wrapper, $this->_wrapper->getEnvVar('GIT_SSH'));
    }

    public function testSetPrivateKeyPort()
    {
        $key = './test/id_rsa';
        $port = mt_rand(1024, 10000);

        $this->_wrapper->setPrivateKey($key, $port);
        $this->assertEquals($port, $this->_wrapper->getEnvVar('GIT_SSH_PORT'));
    }

    public function testSetPrivateKeyWrapper()
    {
        $key = './test/id_rsa';
        $wrapper = './test/dummy-wrapper.sh';
        $wrapper_expected = realpath($wrapper);

        $this->_wrapper->setPrivateKey($key, 22, $wrapper_expected);
        $this->assertEquals($wrapper_expected, $this->_wrapper->getEnvVar('GIT_SSH'));
    }
}
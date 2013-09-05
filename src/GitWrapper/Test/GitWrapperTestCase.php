<?php

namespace GitWrapper\Test;

use GitWrapper\Event\GitEvents;
use GitWrapper\GitException;
use GitWrapper\GitWrapper;
use GitWrapper\Test\Event\TestBypassListener;
use GitWrapper\Test\Event\TestListener;

class GitWrapperTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GitWrapper
     */
    protected $_wrapper;

    /**
     * Overrides PHPUnit_Framework_TestCase::setUp().
     */
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

    /**
     * Adds the test listener for all events, returns the listener.
     *
     * @return TestListener
     */
    public function addListener()
    {
        $dispatcher = $this->_wrapper->getDispatcher();
        $listener = new TestListener();

        $dispatcher->addListener(GitEvents::GIT_PREPARE, array($listener, 'onPrepare'));
        $dispatcher->addListener(GitEvents::GIT_SUCCESS, array($listener, 'onSuccess'));
        $dispatcher->addListener(GitEvents::GIT_ERROR, array($listener, 'onError'));
        $dispatcher->addListener(GitEvents::GIT_BYPASS, array($listener, 'onBypass'));

        return $listener;
    }

    /**
     * Adds the bypass listener so that Git commands are not run.
     *
     * @return TestBypassListener
     */
    public function addBypassListener()
    {
        $listener = new TestBypassListener();
        $dispatcher = $this->_wrapper->getDispatcher();
        $dispatcher->addListener(GitEvents::GIT_PREPARE, array($listener, 'onPrepare'), -5);
        return $listener;
    }

    /**
     * Asserts a correct Git version string was returned.
     *
     * @param type $version
     *   The version returned by the `git --version` command.
     */
    public function assertGitVersion($version)
    {
        $match = preg_match('/^git version [.0-9]+/', $version);
        $this->assertNotEmpty($match);
    }

    /**
     * Executes a bad command.
     *
     * @param bool $catch_exception
     *   Whether to catch the exception to continue script execution, defaults
     *   to false.
     */
    public function runBadCommand($catch_exception = false)
    {
        try {
            $this->_wrapper->git('a-bad-command');
        } catch (GitException $e) {
            if (!$catch_exception) {
                throw $e;
            }
        }
    }
}
<?php

class Jirafe_Api_Collection_ResourcesTest extends PHPUnit_Framework_TestCase
{
    private $clientMock;
    private $applications;
    private $application;
    private $resources;

    protected function setUp()
    {
        $this->clientMock = $this->getMockBuilder('Jirafe_Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->applications = new Jirafe_Api_Collection_Applications($this->clientMock);
        $this->application  = new Jirafe_Api_Resource_Application(41, $this->applications, $this->clientMock);
        $this->resources    = new Jirafe_Api_Collection_Resources($this->application, $this->clientMock);
    }

    /**
     * @test
     */
    public function shouldProvideCorrectPath()
    {
        $this->assertEquals('applications/41/resources', $this->resources->getPath());
    }

    /**
     * @test
     */
    public function shouldBeAbleToSyncResourcesIncludingApplication()
    {
        $sitesToSync = array(
            array('description' => 'site1', 'url' => 'http://site1'),
            array('description' => 'site1', 'url' => 'http://site2')
        );
        $usersToSync = array(
            array('email' => 'everzet@knplabs.com', 'username' => 'everzet'),
            array('email' => 'vjousse@knplabs.com', 'username' => 'vjousse')
        );

        $params = array(
            'opt_in' => true,
            'platform_type' => 'magento',
            'platform_version' => '1.0.0',
            'plugin_version' => '0.1.0',
        );

        $this->clientMock
            ->expects($this->once())
            ->method('post')
            ->with('applications/41/resources', array(), array(
                'sites' => $sitesToSync,
                'users' => $usersToSync,
                'opt_in' => true,
                'platform_type' => 'magento',
                'platform_version' => '1.0.0',
                'plugin_version' => '0.1.0',
            ))
            ->will($this->returnValue(new Jirafe_HttpConnection_Response('"hash"', array(), 0, '')));

        $this->assertEquals('hash', $this->resources->sync($sitesToSync, $usersToSync, $params));
    }

    /**
     * @test
     */
    public function shouldBeAbleToSyncResources()
    {
        $sitesToSync = array(
            array('description' => 'site1', 'url' => 'http://site1'),
            array('description' => 'site1', 'url' => 'http://site2')
        );
        $usersToSync = array(
            array('email' => 'everzet@knplabs.com', 'username' => 'everzet'),
            array('email' => 'vjousse@knplabs.com', 'username' => 'vjousse')
        );

        $this->clientMock
            ->expects($this->once())
            ->method('post')
            ->with('applications/41/resources', array(), array(
                'sites' => $sitesToSync,
                'users' => $usersToSync
            ))
            ->will($this->returnValue(new Jirafe_HttpConnection_Response('"hash"', array(), 0, '')));

        $this->assertEquals('hash', $this->resources->sync($sitesToSync, $usersToSync));
    }
}

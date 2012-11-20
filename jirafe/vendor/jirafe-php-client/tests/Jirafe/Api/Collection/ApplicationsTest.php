<?php

class Jirafe_Api_Collection_ApplicationsTest extends PHPUnit_Framework_TestCase
{
    private $clientMock;
    private $applications;

    protected function setUp()
    {
        $this->clientMock = $this->getMockBuilder('Jirafe_Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->applications = new Jirafe_Api_Collection_Applications($this->clientMock);
    }

    /**
     * @test
     */
    public function shouldProvideApplicationResource()
    {
        $application = $this->applications->get(12);

        $this->assertInstanceOf('Jirafe_Api_Resource_Application', $application);
        $this->assertEquals(12, $application->getId());
    }

    /**
     * @test
     */
    public function shouldProvideCorrectPath()
    {
        $this->assertEquals('applications', $this->applications->getPath());
    }

    /**
     * @test
     */
    public function shouldBeAbleToCreateNewApplication()
    {
        $this->clientMock
            ->expects($this->once())
            ->method('post')
            ->with('applications', array(), array(
                'name'              => 'everzet',
                'url'               => 'http://everzet.com',
                'platform_type'     => 'generic',
                'platform_version'  => '1.0.0', 
                'plugin_version'    => '0.1.0'
            ))
            ->will($this->returnValue(new Jirafe_HttpConnection_Response('"val"', array(), 0, '')));

        $this->assertEquals('val', $this->applications->create('everzet', 'http://everzet.com'));
    }

    /**
     * @test
     */
    public function shouldBeAbleToCreateNewApplicationFromPlugin()
    {
        $this->clientMock
            ->expects($this->once())
            ->method('post')
            ->with('applications', array(), array(
                'name'              => 'everzet',
                'url'               => 'http://everzet.com',
                'platform_type'     => 'magento',
                'platform_version'  => '1.5.0', 
                'plugin_version'    => '0.5.0'
            ))
            ->will($this->returnValue(new Jirafe_HttpConnection_Response('"val"', array(), 0, '')));

        $this->assertEquals('val', $this->applications->create('everzet', 'http://everzet.com', 'magento', '1.5.0', '0.5.0'));
    }

    /**
     * @test
     * @expectedException Jirafe_Exception
     */
    public function shouldThrowExceptionOnCreateError()
    {
        $this->clientMock
            ->expects($this->once())
            ->method('post')
            ->with('applications', array(), array(
                'name'              => 'everzet',
                'url'               => 'http://everzet.com',
                'platform_type'     => 'generic',
                'platform_version'  => '1.0.0', 
                'plugin_version'    => '0.1.0'
            ))
            ->will($this->returnValue(new Jirafe_HttpConnection_Response('"val"', array(), 2, '')));

        $this->assertEquals('val', $this->applications->create('everzet', 'http://everzet.com'));
    }
}

<?php

class Jirafe_Api_Collection_SitesTest extends PHPUnit_Framework_TestCase
{
    private $clientMock;
    private $applications;
    private $application;
    private $sites;

    protected function setUp()
    {
        $this->clientMock = $this->getMockBuilder('Jirafe_Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->applications = new Jirafe_Api_Collection_Applications($this->clientMock);
        $this->application  = new Jirafe_Api_Resource_Application(41, $this->applications, $this->clientMock);
        $this->sites        = new Jirafe_Api_Collection_Sites($this->application, $this->clientMock);
    }

    /**
     * @test
     */
    public function shouldProvideSiteResource()
    {
        $site = $this->sites->get(123);

        $this->assertInstanceOf('Jirafe_Api_Resource_Site', $site);
        $this->assertEquals(123, $site->getId());
    }

    /**
     * @test
     */
    public function shouldProvideCorrectPath()
    {
        $this->assertEquals('applications/41/sites', $this->sites->getPath());
    }

    /**
     * @test
     */
    public function shouldBeAbleToFetchAllSites()
    {
        $this->clientMock
            ->expects($this->once())
            ->method('get')
            ->with('applications/41/sites', array())
            ->will($this->returnValue(new Jirafe_HttpConnection_Response('"list"', array(), 0, '')));

        $this->assertEquals('list', $this->sites->fetchAll());
    }

    /**
     * @test
     * @expectedException Jirafe_Exception
     */
    public function shouldThrowExceptionOnFetchAllError()
    {
        $this->clientMock
            ->expects($this->once())
            ->method('get')
            ->with('applications/41/sites', array())
            ->will($this->returnValue(new Jirafe_HttpConnection_Response('', array(), 1, 'error')));

        $this->assertEquals('list', $this->sites->fetchAll());
    }
}

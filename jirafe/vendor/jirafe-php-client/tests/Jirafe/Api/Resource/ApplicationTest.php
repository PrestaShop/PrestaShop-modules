<?php

class Jirafe_Api_Resource_ApplicationTest extends PHPUnit_Framework_TestCase
{
    private $clientMock;
    private $applications;
    private $application;

    protected function setUp()
    {
        $this->clientMock = $this->getMockBuilder('Jirafe_Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->applications = new Jirafe_Api_Collection_Applications($this->clientMock);
        $this->application  = new Jirafe_Api_Resource_Application(23, $this->applications, $this->clientMock);
    }

    /**
     * @test
     */
    public function shouldProvideSitesCollection()
    {
        $sites = $this->application->sites();

        $this->assertInstanceOf('Jirafe_Api_Collection_Sites', $sites);
    }

    /**
     * @test
     */
    public function shouldProvideResourcesCollection()
    {
        $resources = $this->application->resources();

        $this->assertInstanceOf('Jirafe_Api_Collection_Resources', $resources);
    }

    /**
     * @test
     */
    public function shouldProvideSiteResource()
    {
        $site = $this->application->sites(2);

        $this->assertInstanceOf('Jirafe_Api_Resource_Site', $site);
        $this->assertEquals(2, $site->getId());
    }

    /**
     * @test
     */
    public function shouldProvideCorrectPath()
    {
        $this->assertEquals('applications/23', $this->application->getPath());
    }
}

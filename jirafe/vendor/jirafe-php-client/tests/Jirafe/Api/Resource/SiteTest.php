<?php

class Jirafe_Api_Resource_SiteTest extends PHPUnit_Framework_TestCase
{
    private $clientMock;
    private $applications;
    private $application;
    private $sites;
    private $site;

    protected function setUp()
    {
        $this->clientMock = $this->getMockBuilder('Jirafe_Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->applications = new Jirafe_Api_Collection_Applications($this->clientMock);
        $this->application  = new Jirafe_Api_Resource_Application(23, $this->applications, $this->clientMock);
        $this->sites        = new Jirafe_Api_Collection_Sites($this->application, $this->clientMock);
        $this->site         = new Jirafe_Api_Resource_Site(104, $this->sites, $this->clientMock);
    }

    /**
     * @test
     */
    public function shouldProvideCorrectPath()
    {
        $this->assertEquals('applications/23/sites/104', $this->site->getPath());
    }

    /**
     * @test
     */
    public function shouldProvideVisitsReport()
    {
        $visits = $this->site->visits();

        $this->assertInstanceOf('Jirafe_Api_Report_Visits', $visits);
        $this->assertSame($this->site, $visits->getParent());
    }

    /**
     * @test
     */
    public function shouldProvideVisitorsReport()
    {
        $visitors = $this->site->visitors();

        $this->assertInstanceOf('Jirafe_Api_Report_Visitors', $visitors);
        $this->assertSame($this->site, $visitors->getParent());
    }

    /**
     * @test
     */
    public function shouldProvideBouncesReport()
    {
        $bounces = $this->site->bounces();

        $this->assertInstanceOf('Jirafe_Api_Report_Bounces', $bounces);
        $this->assertSame($this->site, $bounces->getParent());
    }

    /**
     * @test
     */
    public function shouldProvideAverageReport()
    {
        $average = $this->site->average();

        $this->assertInstanceOf('Jirafe_Api_Report_Average', $average);
        $this->assertSame($this->site, $average->getParent());
    }

    /**
     * @test
     */
    public function shouldProvideRevenuesReport()
    {
        $revenues = $this->site->revenues();

        $this->assertInstanceOf('Jirafe_Api_Report_Revenues', $revenues);
        $this->assertSame($this->site, $revenues->getParent());
    }

    /**
     * @test
     */
    public function shouldProvideKeywordsReport()
    {
        $keywords = $this->site->keywords();

        $this->assertInstanceOf('Jirafe_Api_Report_Keywords', $keywords);
        $this->assertSame($this->site, $keywords->getParent());
    }

    /**
     * @test
     */
    public function shouldProvideReferersReport()
    {
        $referers = $this->site->referers();

        $this->assertInstanceOf('Jirafe_Api_Report_Referers', $referers);
        $this->assertSame($this->site, $referers->getParent());
    }

    /**
     * @test
     */
    public function shouldProvideExitsReport()
    {
        $exits = $this->site->exits();

        $this->assertInstanceOf('Jirafe_Api_Report_Exits', $exits);
        $this->assertSame($this->site, $exits->getParent());
    }
}

<?php

class Jirafe_Api_ReportTest extends PHPUnit_Framework_TestCase
{
    private $clientMock;
    private $applications;
    private $application;
    private $sites;
    private $site;
    private $reportMock;

    protected function setUp()
    {
        $this->clientMock = $this->getMockBuilder('Jirafe_Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->applications = new Jirafe_Api_Collection_Applications($this->clientMock);
        $this->application  = new Jirafe_Api_Resource_Application(23, $this->applications, $this->clientMock);
        $this->sites        = new Jirafe_Api_Collection_Sites($this->application, $this->clientMock);
        $this->site         = new Jirafe_Api_Resource_Site(104, $this->sites, $this->clientMock);

        $this->reportMock = $this->getMockBuilder('Jirafe_Api_Report')
            ->setMethods(array('no_stubs'))
            ->setConstructorArgs(array($this->site, $this->clientMock))
            ->getMock();
    }

    /**
     * @test
     */
    public function shouldUseProperPathInGetReportCalls()
    {
        $class = $this->getCollectionName($this->reportMock);

        $this->clientMock
            ->expects($this->once())
            ->method('get')
            ->with('applications/23/sites/104/'.$class, array());

        $this->reportMock->doReportGet();
    }

    /**
     * @test
     */
    public function shouldUseProperNestedPathInGetReportCalls()
    {
        $class = $this->getCollectionName($this->reportMock);

        $this->clientMock
            ->expects($this->once())
            ->method('get')
            ->with('applications/23/sites/104/'.$class.'/total/vals', array());

        $this->reportMock->doReportGet('total/vals');
    }

    private function getCollectionName($class)
    {
        return strtolower(preg_replace('/[^_]+_/', '', get_class($class)));
    }
}

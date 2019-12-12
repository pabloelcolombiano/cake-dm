<?php


namespace CakeDomainManager\Test;

use Cake\Error\FatalErrorException;
use Cake\Http\BaseApplication;
use CakeDomainManager\DomainApplication;
use PHPUnit\Framework\TestCase as BaseTestCase;

class DomainApplicationTest extends BaseTestCase
{
    /**
     * @var DomainApplication
     */
    public $domainApplication;

    /**
     * @var string
     */
    public $testDirectory;

    /**
     * @var string
     */
    public $testNamespace;

    public function setUp()
    {
        $application = $this->createMock(BaseApplication::class);
        $this->domainApplication = DomainApplication::init($application);
        $this->testDirectory = ROOT . DS . 'tests' . DS . 'TestDirectory';
        $this->testNamespace = 'App\Test\TestDirectory';
    }

    public function testTrackDuplicatedClassesInNamespace()
    {
        $this->expectException(FatalErrorException::class);
        $this->domainApplication->trackDuplicatedClassesInNamespace($this->testNamespace, $this->testDirectory);
    }

    public function testTrackDuplicatedPHPFilesInFolder()
    {
        $this->expectException(FatalErrorException::class);
        $this->domainApplication->trackDuplicatedPHPFilesInDirectory($this->testDirectory);
    }

    public function testThrowFatalErrorException()
    {
        $this->expectException(FatalErrorException::class);
        $this->domainApplication->throwFatalErrorException('');
    }

}
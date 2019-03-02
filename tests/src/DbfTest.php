<?php

use PHPUnit\Framework\TestCase;
use Cbrf\Dbase\Update;
use Cbrf\Dbf;

/**
 * Тесты для класса Cbrf\Dbase\Dbase
 */
class DbfTest extends TestCase
{

  /** @var Dbf Ресурс объекта Dbf */
  private $dbf = null;
  
  /** @var string Имя директории для DBF файлов */
  private static $dirname = 'data';

  protected function setUp()
  { 
    $this->dbf = (new Dbf(self::$dirname));
  }

  protected function tearDown()
  {
    $this->dbf = null;
  }

  public static function setUpBeforeClass()
  {
    (new Update(self::$dirname))->files();
  }

  public static function tearDownAfterClass()
  {
    if(is_file(self::$dirname))
    {
      unlink(self::$dirname);
      rmdir(preg_replace('|[/\\\]([^\/\\\]*)$|i', '', self::$dirname));
    }
    else
    {
      $objects = scandir(self::$dirname);
      foreach ($objects as $object)
      {
        if ($object != "." && $object != "..")
        {
          if (filetype(self::$dirname."/".$object) == "dir") rrmdir(self::$dirname."/".$object); else unlink(self::$dirname."/".$object);
        }
      }
      reset($objects);
      rmdir(self::$dirname);
    }
  }

  public function testGetDirName()
  {
    $result = $this->dbf->getDirName();

    $this->assertIsString($result);
    $this->assertNotEquals('', $result);
    $this->assertDirectoryExists($result);
  }

  public function testSelect()
  {
    $result = $this->dbf->
      select('NNP, NAMEP')->
      from('BNKSEEK.DBF')->
      exect()->
      fetchAll();

    $this->assertIsArray($result);
    $this->assertNotEmpty($result);

    return $result;
  }

  public function testGetFields()
  {
    $result = $this->dbf->
      select('NNP, NAMEP')->
      select('NNP')->
      from('BNKSEEK.DBF')->
      exect()->
      getFields();

    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
    $this->assertCount(3, $result);
    $this->assertTrue($result[1] === 'NAMEP');
  }

  public function testGetTable()
  {
    $result = $this->dbf->
      select('KOR')->
      from('KORREK.DBF')->
      exect()->
      getTable();

    $this->assertIsString($result);
    $this->assertNotEquals('', $result);
  }

  public function testEqual()
  {
    $result = $this->dbf->
      select()->
      from('KORREK.DBF')->
      equal(
        'DT_IZM = 19970617,CHS = 1447936521'
      )->
      exect()->
      fetch();

    $this->assertIsArray($result);
    $this->assertCount(2, $result);
  }
  
  /**
   * @depends testSelect
   */
  public function testExclude(array $resultAll)
  {
    $result = $this->dbf->
      select()->
      from('BNKSEEK.DBF')->
      exclude(
        'NNP = КРАСНОДАР'
      )->
      exect()->
      fetch();

    $this->assertIsArray($result);
    $this->assertLessThan(count($resultAll), count($result[0]));
  }

  public function testInclude()
  {
    $result = $this->dbf->
      select()->
      from('BNKSEEK.DBF')->
      include(
        'KSNP = 3010181094525000'
      )->
      exect()->
      fetch();

    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
  }

  /**
   * @covers \Cbrf\Dbf::fetch
   * @covers \Cbrf\Dbf::rowCount
   */
  public function testFetchEqual()
  {
    $result = $this->dbf->
      select()->
      from('KORREK.DBF')->
      equal(
        'DT_IZM = 19970617,CHS = 1447936521'
      )->
      include(
        'KOR=0125'
      )->
      exect();

    $count = $result->rowCount();

    $result = $result->fetch(Dbf::FETCH_EQUAL);

    $this->assertIsInt($count);
    $this->assertEquals(3, $count);

    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
    $this->assertCount(2, $result);
  }
  
  /**
   * @covers \Cbrf\Dbf::fetch
   * @covers \Cbrf\Dbf::rowCount
   */
  public function testFetchInclude()
  {
    $result = $this->dbf->
      select()->
      from('KORREK.DBF')->
      equal(
        'DT_IZM = 19970617, CHS = 1447936521'
      )->
      include(
        'KOR=0125'
      )->
      exect();

    $count = $result->rowCount();

    $result = $result->fetch(Dbf::FETCH_INCLUDE);

    $this->assertIsInt($count);
    $this->assertEquals(3, $count);

    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
    $this->assertCount(1, $result);
  }
  
  /**
   * @covers \Cbrf\Dbf::fetch
   * @covers \Cbrf\Dbf::rowCount
   */
  public function testFetchExclude()
  {
    $resultAll = $this->dbf->
      select()->
      from('KORREK.DBF')->
      exect();

    $countAll = $resultAll->rowCount();

    $result = $this->dbf->
      select()->
      from('KORREK.DBF')->
      exclude(
       'CHS = 1447936521'
      )->
      exect();

    $count = $result->rowCount();

    $result = $result->fetch(Dbf::FETCH_EXCLUDE);

    $this->assertIsInt($count);
    $this->assertLessThan($countAll, $count);

    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
    $this->assertCount(1, $result);
  }

  public function testNumRows()
  {
    $result = $this->dbf->
      select()->
      from('UERKO.DBF')->
      exect()->numRows();

    $this->assertIsInt($result);
    $this->assertGreaterThan(0, $result);
  }

  public function testNumFields()
  {
    $result = $this->dbf->
      select()->
      from('UERKO.DBF')->
      exect()->numFields();

    $this->assertIsInt($result);
    $this->assertGreaterThan(0, $result);
  }

  public function testGetFieldsInfo()
  {
    $result = $this->dbf->
      select()->
      from('UERKO.DBF')->
      exect()->getFieldsInfo();

    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
  }

  public function testDownload()
  {
    $result = $this->dbf->download();

    $this->assertIsBool($result);
    $this->assertTrue($result);
  }

  public function testUpdate()
  {
    $result = $this->dbf->update();

    $this->assertIsBool($result);
    $this->assertTrue($result);
  }

  public function testClose()
  {
    $result = $this->dbf->close();

    $this->assertTrue($result);
  }

}

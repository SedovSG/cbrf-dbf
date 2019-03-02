<?php

use PHPUnit\Framework\TestCase;
use Cbrf\Dbase\Update;

/**
 * Тесты для класса Cbrf\Dbase\Update
 */
class UpdateTest extends TestCase
{

  /** @var Update Ресурс объекта Update */
  private $update = null;

  /** @var string Имя директории для DBF файлов */
  private static $dirname = __DIR__ . '/../../data';

  protected function setUp()
  { 
    $this->update = new Update(self::$dirname);
  }

  protected function tearDown()
  {
    $this->update = null;
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

  public function testGetPathToDbf()
  {
    $result = $this->update->getPathToDbf();

    $this->assertIsString($result);
    $this->assertNotEquals('', $result);
    $this->assertDirectoryExists($result);
  }

  public function testGetXml()
  {
    $result = $this->update->getXml();
    
    $this->assertIsString($result);
    $this->assertNotEquals('', $result);
    $this->assertStringStartsNotWith('<BicDBList', $result);
  }

  public function testReadXml()
  {
    $result = $this->update->readXml();

    $this->assertIsObject($result);
    $this->assertInstanceOf(Update::class, $result);
  }

  public function testGetDateCreatedXml()
  {
    $result = $this->update->readXml()->getDateCreatedXml();

    $this->assertIsString($result);
    $this->assertNotEquals('', $result);
  }

  public function testGetPathToZip()
  {
    $result = $this->update->readXml()->getPathToZip();

    $this->assertIsString($result);
    $this->assertNotEquals('', $result);
  }

  public function testGetDateCreatedZip()
  {
    $result = $this->update->readXml()->getDateCreatedZip();

    $this->assertIsString($result);
    $this->assertNotEquals('', $result);
  }

  public function testGetZipName()
  {
    $result = $this->update->readXml()->getZipName();

    $this->assertIsString($result);
    $this->assertNotEquals('', $result);
  }

  public function testGetUrlForZip()
  {
    $result = $this->update->readXml()->getUrlForZip();

    $this->assertIsString($result);
    $this->assertNotEquals('', $result);
    $this->assertRegExp('|(^http://www.cbr.ru/vfs/mcirabis/BIK/bik_db_.+)|', $result);
  }

  public function testDownloadZip()
  {
    $result = $this->update->downloadZip();
    $file = $this->update->getPathToDbf() . DIRECTORY_SEPARATOR . 'bik.zip';
    
    $this->assertTrue($result);
    $this->assertFileExists($file);
  }

  public function testUnpackZip()
  {
    $result = $this->update->unpackZip();
    $file = $this->update->getPathToDbf() . DIRECTORY_SEPARATOR . 'BNKSEEK.DBF';
    
    $this->assertFileExists($file);
    $this->assertFileIsReadable($file);
  }

  public function testFiles()
  {
    $result = $this->update->files();
    $file = $this->update->getPathToDbf() . DIRECTORY_SEPARATOR . 'BNKSEEK.DBF';

    $this->assertTrue($result);
    $this->assertFileExists($file);
    $this->assertFileIsReadable($file);
  }

}

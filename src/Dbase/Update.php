<?php
/**
 * Класс для обновления данных ЦБ РФ
 *
 * @link       http://www.sedovsg.me
 * @author     Седов Станислав, <SedovSG@yandex.ru>
 * @copyright  Copyright (c) 2019 Седов Станислав. (http://www.sedovsg.me) 
 * @license    https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 */

declare(strict_types = 1);

namespace Cbrf\Dbase;

/**
 * Класс обновляет данные с сайта ЦБ РФ
 *
 * @category   Library
 * @package    SedovSG/Cbrf
 * @author     Седов Станислав, <SedovSG@yandex.ru>
 * @copyright  Copyright (c) 2019 Седов Станислав. (http://www.sedovsg.me)
 * @license    https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 * @version    1.0.0
 * @since      1.0.0
 */
class Update
{
  /** @var string Веб-адрес ЦБ РФ */
	const CBR_URL = 'http://www.cbr.ru';
  
	/**
   * Создаёт экземпляр объекта загрузки и обновления DBF
   * 
   * @return void
   */
	public function __construct(string $dbfPath = '')
	{
    $this->setPathToDbf($dbfPath);
	}

  /**
   * Метод устанавливает путь до файлов DBF
   * 
   * @param string $value Путь
   */
  public function setPathToDbf(string $value = '')
  {
    $this->dbfPath = $value;

    if(!file_exists($this->dbfPath))
    {
      mkdir($this->dbfPath, 0775, true);
    }
  }
  
  /**
   * Метод получает путь до файлов DBF
   * 
   * @return string
   */
  public function getPathToDbf(): string
  {
    return $this->dbfPath;
  }
  
  /**
   * Метод получает XML
   *
   * @throws \LogicException Если библиотека cURL отсутствует
   * 
   * @return string
   */
	public function getXml(): string
	{
    $result = '';

		if(extension_loaded('curl') == false)
		{
		  throw new \LogicException('Библиотека cURL не подключена');
		}

    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $this->xmlPath);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl_handle, CURLOPT_USERAGENT,
    	'Mozilla/5.0 (Windows NT 5.2; rv:6.0.2) Gecko/20170101 Firefox/6.0.2');
    
    $result = curl_exec($curl_handle);

    curl_close($curl_handle);

    return $result;
	}

  /**
   * Метод получает информацию об актуальном архиве.
   *
   * @throws \LogicException    Если библиотека SimpleXML отсутствует
   * @throws \RuntimeException  Если возникла ошибка файла XML
   * 
   * @return Update()
   */
	public function readXml(): Update
	{
	  $xml = null;
	  $bic = null;

    $xml = $this->getXml();
	  
	  if(extension_loaded('simplexml') == false)
	  {
	  	throw new \LogicException('Библиотека SimpleXML не подключена');
	  }
    
	  $bic = simplexml_load_string($xml);

	  if($bic == null)
    {
      throw new \RuntimeException('Ошибка разбора файла XML');
    }

    $this->zipPath        = (string) $bic->attributes()->Base;
	  $this->dateCreatedXml = (string) $bic->attributes()->DataGeneration;

	  $this->zipName        = (string) $bic->item[0]->attributes()->file;
	  $this->dateCreatedZip = (string) $bic->item[0]->attributes()->date;

	  return $this;
	}

  /**
   * Метод получает дату создания XML файла.
   *
   * @return string
   */
  public function getDateCreatedXml(): string
  {
  	return $this->dateCreatedXml;
  }
  
  /**
   * Метод получает базовый путь до архива
   *
   * @return string
   */
	public function getPathToZip(): string
	{
		return $this->zipPath;
	}
  
  /**
   * Метод получает дату создания Zip архива.
   *
   * @return string
   */
	public function getDateCreatedZip(): string
	{
		return $this->dateCreatedZip;
	}
  
  /**
   * Метод получает имя Zip архива.
   *
   * @return string
   */
	public function getZipName(): string
	{
		return $this->zipName;
	}

  /**
   * Метод получает URL до ZIP архива.
   *
   * @return string
   */
	public function getUrlForZip(): string
	{
		return self::CBR_URL . $this->zipPath . $this->zipName;
	}
  
  /**
   * Метод выполняет обновление данных ЦБ РФ
   * 
   * @return bool
   */
	public function files(): bool
	{
    if($this->downloadZip() === true)
    {
      $this->unpackZip();
    }

    return true;
	}
  
  /**
   * Метод скачивает архив.
   *
   * @throws \ErrorException Если не удалось скопировать архив
   * 
   * @return bool
   */
  public function downloadZip(): bool
  {
    $this->readXml();

    if(!copy($this->getUrlForZip(), $this->dbfPath . '/bik.zip'))
    {
      throw new \ErrorException('Не удалось скопировать архив');  
    }

    return true;
  }
   
  /**
   * Метод распаковывает архив.
   *
   * @throws \LogicException   Если библиотека ZipArchive отсутствует
   * @throws \RuntimeException Если не удалось открыть архив
   * 
   * @return void
   */
  public function unpackZip(): void
  {
    $zip = null;

    if(extension_loaded('zip') == false)
    {
      throw new \LogicException('Библиотека ZipArchive не подключена');
    }

    $zip = new \ZipArchive();

    if($zip->open($this->dbfPath . '/bik.zip') === true)
    {
      $zip->extractTo($this->dbfPath);
      $zip->close();

      unlink($this->dbfPath . '/bik.zip');
    }
    else
    {
      throw new \RuntimeException('Не удалось открыть архив');
    }
  }
  
  /** @var string Путь до XML документа */
	private $xmlPath = self::CBR_URL . '/Queries/FileSource/33411/GetBicCatalog.xml';

	/** @var string Дата создания XML документа */
	private $dateCreatedXml = '';
  
  /** @var string Базовый путь до ZIP архива */
	private $zipPath = '';
  
  /** @var string Дата создания ZIP архива */
	private $dateCreatedZip = '';
  
  /** @var string Имя файла ZIP архива */
	private $zipName = '';

  /** @var string Путь до каталога DBF */
  private $dbfPath = '';

}

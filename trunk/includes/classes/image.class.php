<?php
/**
 * Image
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Core
 */

if (defined('IN_ACP3') === false)
	exit;

/**
 * Klasse zum beliebigen Skalieren und Ausgeben von Bildern
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Core
 */
class image
{
	/**
	 *
	 * @var boolean 
	 */
	private $cache_picture = false;
	/**
	 *
	 * @var string 
	 */
	private $cache_dir = '';
	/**
	 *
	 * @var string 
	 */
	private $cache_prefix = '';
	/**
	 *
	 * @var integer 
	 */
	private $max_width = 0;
	/**
	 *
	 * @var integer 
	 */
	private $max_height = 0;
	/**
	 *
	 * @var integer 
	 */
	private $jpg_quality = 85;
	/**
	 *
	 * @var string 
	 */
	private $file = '';
	/**
	 *
	 * @var boolean 
	 */
	private $force_resample = false;
	/**
	 *
	 * @var resource 
	 */
	private $image = null;

	/**
	 * Konstruktor der Klasse.
	 * Überschreibt die Defaultwerte mit denen im $options-array enthaltenen Werten
	 *
	 * @param array $options
	 */
	public function __construct(array $options)
	{
		if (isset($options['cache_picture']) && is_bool($options['cache_picture']))
			$this->cache_picture = $options['cache_picture'];
		if (isset($options['cache_dir']) && is_dir($options['cache_dir']))
			$this->cache_dir = $options['cache_dir'] . (!preg_match('/\/$/', $options['cache_dir']) ? '/' : '');
		if (isset($options['cache_prefix']))
			$this->cache_prefix = $options['cache_prefix'];
		if (isset($options['max_width']) && validate::isNumber($options['max_width']))
			$this->max_width = $options['max_width'];
		if (isset($options['max_height']) && validate::isNumber($options['max_height']))
			$this->max_height = $options['max_height'];
		if (isset($options['jpg_quality']) && validate::isNumber($options['jpg_quality']))
			$this->jpg_quality = $options['jpg_quality'];
		if (isset($options['force_resample']) && is_bool($options['force_resample']))
			$this->force_resample = $options['force_resample'];
		$this->file = $options['file'];
	}
	/**
	 * Gibt den während der Bearbeitung belegten Speicher wieder frei 
	 */
	public function __destruct()
	{
		if (is_resource($this->image))
			imagedestroy ($this->image);
	}
	/**
	 * Berechnet die neue Breite/Höhe eines Bildes
	 *
	 * @param integer $width
	 *  Ausgangsbreite des Bildes
	 * @param integer $height
	 *  Ausgangshöhe des Bildes
	 * @return array
	 */
	private function calcNewDimensions($width, $height)
	{
		if ($width > $height) {
			$newWidth = $this->max_width;
			$newHeight = intval($height * $newWidth / $width);
		} else {
			$newHeight = $this->max_height;
			$newWidth = intval($width * $newHeight / $height);
		}

		return array('width' => $newWidth, 'height' => $newHeight);
	}
	/**
	 *
	 * @param type $width
	 * @param type $height
	 * @param type $type
	 * @return type 
	 */
	private function setCacheName()
	{
		return $this->cache_prefix . '_' . substr($this->file, strrpos($this->file, '/') + 1);
	}
	/**
	 * Führt die Größenanpassung des Bildes durch
	 *
	 * @param integer $newWidth
	 * @param integer $newHeight
	 * @param integer $width
	 * @param integer $height
	 * @param integer $type 
	 */
	private function resample($newWidth, $newHeight, $width, $height, $type, $cache_file = null)
	{
		$this->image = imagecreatetruecolor($newWidth, $newHeight);
		switch ($type) {
			case 1:
				$oldPic = imagecreatefromgif($this->file);
				imagecopyresampled($this->image, $oldPic, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
				imagegif($this->image, $cache_file);
				break;
			case 2:
				$oldPic = imagecreatefromjpeg($this->file);
				imagecopyresampled($this->image, $oldPic, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
				imagejpeg($this->image, $cache_file, $this->jpg_quality);
				break;
			case 3:
				imagealphablending($this->image, false);
				$oldPic = imagecreatefrompng($this->file);
				imagecopyresampled($this->image, $oldPic, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
				imagesavealpha($this->image, true);
				imagepng($this->image, $cache_file, 9);
				break;
		}
	}
	/**
	 * Gibt ein Bild direkt aus, ohne dieses in der Größe zu bearbeiten
	 *
	 * @return string 
	 */
	private function readFromFile()
	{
		return readfile($this->file);
	}
	/**
	 * Gibt das Bild aus
	 */
	public function output()
	{
		if (is_file($this->file)) {
			$picInfo = getimagesize($this->file);
			$width = $picInfo[0];
			$height = $picInfo[1];
			$type = $picInfo[2];

			header('Cache-Control: public');
			header('Pragma: public');
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($this->file)) . ' GMT');
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
			header('Content-type: ' . $picInfo['mime']);

			// Falls Cache aktiviert ist und das Bild bereits gecachet wurde, dieses direkt ausgeben
			if ($this->cache_picture === true && is_file($this->cache_dir . $this->setCacheName())) {
				$this->file = $this->cache_dir . $this->setCacheName();
				$this->readFromFile();
			// Bild resampeln
			} elseif (($width > $this->max_width && $height > $this->max_height || $this->force_resample === true) && ($type === 1 || $type === 2 || $type === 3)) {
				$dimensions = $this->calcNewDimensions($width, $height);
				$cache_file = null;
				if ($this->cache_picture === true && is_dir($this->cache_dir) && !is_file($this->cache_dir . $this->setCacheName()))
					$cache_file = $this->cache_dir . $this->setCacheName();

				$this->resample($dimensions['width'], $dimensions['height'], $width, $height, $type, $cache_file);

				if (is_null($cache_file) === false) {
					$this->file = $cache_file;
					$this->readFromFile();
				}
			// Bild direkt ausgeben
			} else {
				$this->readFromFile();
			}
		}
	}
}
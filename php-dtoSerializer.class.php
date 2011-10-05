<?php
/**
 * DTO serializing and deserializing class
 *
 * PHP Version 5
 *
 * LICENSE: You may use this source file under the terms of the MIT license.
 * The full terms of the license is available through the world-wide-web at 
 * the following URI: http://www.opensource.org/licenses/MIT
 * 
 * @author		Johan Guttormsson <johan.guttormsson@cubicalstudios.se>
 * @license		http://www.opensource.org/licenses/MIT The MIT License
 * @link		https://www.github.com/johangu/php-dtoSerializer
 */

class DTOSerializer {
	
	/**
	 * Serializes a DTO and returns a DOMDocument object
	 * @arguments	$DTO - The object to serialize
	 *
	 * @return		DTO serialized as DOMDocument
	 */

	public static function serializeXML($DTO) {
		$dom = new DOMDocument('1.0', 'utf-8');
		$rootElement = $dom->createElement(get_class($DTO));
		$dom->appendChild($rootElement);

		foreach($DTO as $key => $value) {
			if(is_array($value)) {
				$element = $dom->createElement($key);
				foreach($value as $k => $childDoc) {
					$childDoc = self::serializeXML($childDoc[0]);
					$childElement = $dom->importNode($childDoc->documentElement, true);
					$element->appendChild($childElement);
				}
			}
			else if(is_object($value)) {
				$childDoc = self::serializeXML($value);
				$element = $dom->importNode($childDoc->documentElement, true);
			}
			else {
				$element = $dom->createElement($key, $value);
			}
			$rootElement->appendChild($element);
		}
		
		return $dom;
	}

	/**
	 * De-serializes an DOMDocument and returns a DTO
	 * @arguments	$XML - The DOMDocument to de-serialize
	 *
	 * @return		XML de-serialized as DTO
	 */

	public static function deserializeXML($XML) {
		$XML = ($XML->documentElement) ? $XML->documentElement : $XML;
		$DTO = new $XML->nodeName;

		foreach($XML->childNodes as $xmlChild) {
			$prop = $xmlChild->tagName;
			if (is_array($DTO->$prop)) {
				foreach($xmlChild->childNodes as $childNode) {
					array_push($DTO->$prop, self::deserializeXML($childNode));
				}
			}
			else if($xmlChild->childNodes->length > 1) {
				$DTO->$prop = self::deserializeXML($xmlChild);
			}
			else {
				 $DTO->$prop = $xmlChild->textContent;
			}
		}
		
		return $DTO;
	}
}
?>
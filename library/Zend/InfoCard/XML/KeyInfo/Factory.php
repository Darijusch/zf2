<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_InfoCard
 */

namespace Zend\InfoCard\XML\KeyInfo;
use Zend\InfoCard\XML;

/**
 * Factory class to return a XML KeyInfo block based on input XML
 *
 * @category   Zend
 * @package    Zend_InfoCard
 * @subpackage Zend_InfoCard_Xml
 */
class Factory
{
    /**
     * Constructor (disabled)
     *
     */
    private function __construct()
    {
    }

    /**
     * Returns an instance of KeyInfo object based on the input KeyInfo XML block
     *
     * @param string $xmlData The KeyInfo XML Block
     * @return \Zend\InfoCard\XML\KeyInfo\AbstractKeyInfo
     * @throws XML\Exception\InvalidArgumentException
     */
    static public function getInstance($xmlData)
    {

        if($xmlData instanceof XML\AbstractElement) {
            $strXmlData = $xmlData->asXML();
        } else if (is_string($xmlData)) {
            $strXmlData = $xmlData;
        } else {
            throw new XML\Exception\InvalidArgumentException("Invalid Data provided to create instance");
        }

        $sxe = simplexml_load_string($strXmlData);

        $namespaces = $sxe->getDocNameSpaces();

        if(!empty($namespaces)) {
            foreach($sxe->getDocNameSpaces() as $namespace) {
                switch($namespace) {
                    case 'http://www.w3.org/2000/09/xmldsig#':
                        return simplexml_load_string($strXmlData, 'Zend\InfoCard\XML\KeyInfo\XMLDSig');
                    default:
                        throw new XML\Exception\RuntimeException("Unknown KeyInfo Namespace provided");
                    // We are ignoring these lines, as XDebug reports each as a "non executed" line
                    // which breaks my coverage %
                    // @codeCoverageIgnoreStart
                }
            }
        }
        // @codeCoverageIgnoreEnd

        return simplexml_load_string($strXmlData, 'Zend\InfoCard\XML\KeyInfo\DefaultKeyInfo');
    }
}

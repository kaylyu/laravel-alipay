<?php
/**
 * Created by PhpStorm.
 * User: kaylv <kaylv@dayuw.com>
 * Date: 2019/9/4
 * Time: 13:22
 */

namespace Kaylyu\Alipay\Kernel\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class XML
{
    const NAME_ATTRIBUTES = '@attributes';

    const NAME_CONTENT = '@content';

    const NAME_ROOT = '@root';

    /**
     * XML to array.
     *
     * @param string $xml XML string
     * @param array $forceArrayKeys
     *
     * @return array|false
     */
    public static function parse($xml, $forceArrayKeys = [])
    {
        $doc = new DOMDocument();

        $load = @$doc->loadXML($xml);

        if ($load === false) {
            return false;
        }

        $root = $doc->documentElement;

        $output = self::DOMDocumentToArray($root, $forceArrayKeys);

        $output[self::NAME_ROOT] = $root->tagName;

        return $output;
    }

    /**
     * 请求
     * @param array $data
     *
     * @return string
     */
    public static function buildRequest(array $data = [])
    {
        return self::build($data, 'request');
    }

    /**
     * 响应
     * @param array $data
     *
     * @return string
     */
    public static function buildResponse(array $data = [])
    {
        return self::build($data, 'response');
    }

    /**
     * XML encode.
     *
     * @param array $data
     * @param string $method
     *
     * @return string
     */
    public static function build(array $data = [], string $method)
    {
        // create a new XML document
        $document = new DomDocument('1.0', 'UTF-8');

        // create body node
        $body = $document->createElement($method);
        $body = $document->appendChild($body);
        self::buildNode($data, $body, $document);

        return $document->saveXML($document, LIBXML_NOEMPTYTAG);
    }

    /**
     * @param DOMDocument $document
     * @param array $rootOptions
     *
     * @return DOMNode
     */
    protected static function buildRoot(DomDocument $document, array $rootOptions = [])
    {
        $root = $document->createElementNS($rootOptions['namespace'], $rootOptions['name']);

        return $document->appendChild($root);
    }

    /**
     * @param array $data
     * @param DOMNode $node
     * @param DOMDocument $DOMDocument
     */
    protected static function buildNode(array $data, DOMNode &$node, DOMDocument $DOMDocument)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    if ($key === 0 && count($data) > 1) {
                        $node->parentNode->appendChild($child = $DOMDocument->createElement($node->nodeName));
                    } else {
                        $child = $node;
                    }
                } else {
                    $node->appendChild($child = $DOMDocument->createElement($key));
                }
                self::buildNode($value, $child, $DOMDocument);
            } else {
                $node->appendChild($DOMDocument->createElement($key, $value));
            }
        }
    }

    /**
     * Convert DOMDocument->documentElement to array
     *
     * @param DOMElement $documentElement
     * @param array $forceArrayKeys
     *
     * @return array
     */
    protected static function DOMDocumentToArray($documentElement, $forceArrayKeys = [])
    {
        $return = [];

        switch ($documentElement->nodeType) {

            case XML_CDATA_SECTION_NODE:
                $return = trim($documentElement->textContent);
                break;
            case XML_TEXT_NODE:
                $return = trim($documentElement->textContent);
                break;
            case XML_ELEMENT_NODE:

                for ($count = 0, $childNodeLength = $documentElement->childNodes->length; $count < $childNodeLength; $count++) {
                    $child = $documentElement->childNodes->item($count);
                    $childValue = self::DOMDocumentToArray($child, $forceArrayKeys);
                    if (isset($child->tagName)) {
                        $tagName = $child->tagName;
                        if (!isset($return[$tagName])) {
                            $return[$tagName] = [];
                        }
                        $return[$tagName][] = $childValue;
                    } elseif ($childValue || $childValue === '0' || $child->nodeName === '#cdata-section') {
                        $return = (string)$childValue;
                    }
                }

                if ($documentElement->attributes->length && !is_array($return)) {
                    $return = [self::NAME_CONTENT => $return];
                }

                if (is_array($return)) {
                    if ($documentElement->attributes->length) {
                        $attributes = [];
                        foreach ($documentElement->attributes as $attrName => $attrNode) {
                            $attributes[$attrName] = (string)$attrNode->value;
                        }
                        $return[self::NAME_ATTRIBUTES] = $attributes;
                    }
                    foreach ($return as $key => $value) {
                        if (is_array($value) && count($value) == 1 && $key != self::NAME_ATTRIBUTES && !in_array($key,
                                $forceArrayKeys)) {
                            $return[$key] = $value[0];
                        }
                    }
                }
                break;
        }

        return $return;
    }
}
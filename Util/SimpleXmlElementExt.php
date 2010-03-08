<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2010, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         SVN: $Id$
 */


namespace OpenAvanti\Util; 

/**
 * This class extends the SimpleXMLElement class in PHP and adds a few extra methods to aid
 * in the XML DOM manipulation.
 *
 * @category    XML
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/simplexmlelementext
 */
class SimpleXmlElementExt extends SimpleXMLElement
{

    /**
     * Adds a child SimpleXMLElement node as a child of this node. This differs from the native
     * addChild() method in that it allows adding an XML node, not creating a tag.
     *
     * @argument SimpleXMLElement The node to add as a child of this node
     * @returns void
     */
    public function addChildNode(SimpleXMLElement $child) 
    {
        $parentDom = dom_import_simplexml($this);
        $childDom = dom_import_simplexml($child);
        $newParentDom = $parentDom->ownerDocument->importNode($childDom, true);
        $parentDom->appendChild($newParentDom);
    
    } // addChildNode()


    /**
     * Clones this node recursively and returns the cloned node.
     *
     * @returns SimpleXMLElementExt A copy of the current node
     */
    public function cloneNode()
    {
        $domNode = dom_import_simplexml($this);
        $newNode = $domNode->cloneNode(true);
        
        return simplexml_import_dom($newNode, "SimpleXMLElementExt"));         
        
    } // cloneNode()
    
    
    /**
     * Removes a child from the DOM specified by $child
     *
     * @argument SimpleXMLElement
     */
    public function removeChild($child)
    {
        $parentDom = dom_import_simplexml($this);
        $childDom = dom_import_simplexml($child);
        
        $parentDom->removeChild($childDom);
        
    } // removeChild()
    
    
    /**
     *
     *
     */
    public function removeAttributeNS($ns, $attribute)
    {
        $dom = dom_import_simplexml($this);
        
        $dom->removeAttributeNS($ns, $attribute);
        
    } // removeAttributeNS()
    
    
    /**
     * Removes an attribute from the node
     *
     * @argument string The attribute to remove from the node
     * @returns void
     */
    public function removeAttribute($attribute)
    {
        $dom = dom_import_simplexml($this);
        
        $dom->removeAttribute($attribute);
        
    } // removeAttribute()
    
    
    /**
     * Adds a namespaced attribute to an XML node
     * 
     * @argument string The name of the namespace to add the attribute to
     * @argument string The name of the attribute to add to the node
     * @argument string The value of the attribute to add to the node
     * @returns void
     */
    public function addAttributeNS($ns, $attribute, $value)
    {
        $dom = dom_import_simplexml($this);
        
        $dom->setAttributeNS($ns, $attribute, $value);
        
    } // removeAttributeNS()
    
    
    /**
     * Inserts a node before a specified node, making them siblings.
     *
     * @argument SimpleXMLElement The new node to insert
     * @argument SimpleXMLElement The sibling node to insert before
     * @returns void
     */
    public function insertBefore(SimpleXmlElement $newNode, SimpleXMLElement $refNode)
    {
        $dom = dom_import_simplexml($this);
        
        $newNodeDom = dom_import_simplexml($newNode);
        $refNodeDom = dom_import_simplexml($refNode);            
        
        $newNodeDom = $dom->ownerDocument->importNode($newNodeDom, true);
        
        $dom->insertBefore($newNodeDom, $refNodeDom);
        
    } // insertBefore()
    
    
    /**
     * Inserts a node after a specified node, making them siblings.
     *
     * @argument SimpleXMLElement The new node to insert
     * @argument SimpleXMLElement The sibling node to insert after
     * @returns void
     */
    public function insertAfter(SimpleXMLElement $newNode, SimpleXMLElement $refNode)
    {
        $dom = dom_import_simplexml($this);
        
        $newNodeDom = dom_import_simplexml($newNode);
        $refNodeDom = dom_import_simplexml($refNode);
        
        $newNodeDom = $dom->ownerDocument->importNode($newNodeDom, true);
        
        $dom->insertBefore($newNodeDOM, $refNodeDOM->nextSibling);
        
    } // insertAfter()
    
    
    /**
     * Returns whether this dom node has children.
     *
     * @returns bool True if this node has children, false otherwise
     */
    public function hasChildNodes()
    {
        $dom = dom_import_simplexml($this);
        
        return $dom->hasChildNodes();
    
    } // hasChildNodes()
    
    
    /**
     * Returns the parent of this SimpleXMLElement
     *
     * @returns SimpleXMLElement
     */
    public function getParent()
    {
        $parent = current($this->xpath(".."));
        
        return $parent;
    
    } // getParent()

} // SimpleXMLElementExt()

?>

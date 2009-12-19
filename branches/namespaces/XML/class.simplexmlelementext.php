<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    SimpleXML
 * @copyright       Copyright (c) 2008, Kristopher Wilson
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         1.3.0-beta
 *
 */
 
    namespace OpenAvanti\XML;
 
    /**
     * This class extends the SimpleXMLElement class in PHP and adds a few extra methods to aid
     * in the XML DOM manipulation.
     *
     * @category    XML
     * @author      Kristopher Wilson
     * @link            http://www.openavanti.com/docs/simplexmlelementext
     */
    class SimpleXMLElementExt extends SimpleXMLElement
    {
    
        /**
         * Adds a child SimpleXMLElement node as a child of this node. This differs from the native
         * addChild() method in that it allows adding an XML node, not creating a tag.
         *
         * @param SimpleXMLElement The node to add as a child of this node
         * @return void
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
         * @return SimpleXMLElementExt A copy of the current node
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
         * @param SimpleXMLElement
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
         * @param string The attribute to remove from the node
         * @return void
         */
        public function removeAttribute($attribute)
        {
            $dom = dom_import_simplexml($this);
            
            $dom->removeAttribute($attribute);
            
        } // removeAttribute()
        
        
        /**
         * Adds a namespaced attribute to an XML node
         * 
         * @param string The name of the namespace to add the attribute to
         * @param string The name of the attribute to add to the node
         * @param string The value of the attribute to add to the node
         * @return void
         */
        public function addAttributeNS($ns, $attribute, $value)
        {
            $dom = dom_import_simplexml($this);
            
            $dom->setAttributeNS($ns, $attribute, $value);
            
        } // removeAttributeNS()
        
        
        /**
         * Inserts a node before a specified node, making them siblings.
         *
         * @param SimpleXMLElement The new node to insert
         * @param SimpleXMLElement The sibling node to insert before
         * @return void
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
         * @param SimpleXMLElement The new node to insert
         * @param SimpleXMLElement The sibling node to insert after
         * @return void
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
         * @return bool True if this node has children, false otherwise
         */
        public function hasChildNodes()
        {
            $dom = dom_import_simplexml($this);
            
            return $dom->hasChildNodes();
        
        } // hasChildNodes()
        
        
        /**
         * Returns the parent of this SimpleXMLElement
         *
         * @return SimpleXMLElement
         */
        public function getParent()
        {
            $parent = current($this->xpath(".."));
            
            return $parent;
        
        } // getParent()

    } // SimpleXMLElementExt()

?>
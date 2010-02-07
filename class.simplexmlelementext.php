<?php
/***************************************************************************************************
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @dependencies    SimpleXML
 * @copyright       Copyright (c) 2007-2009, Kristopher Wilson
 * @package         openavanti
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 */
 
 
    /**
     * This class extends the SimpleXMLElement class in PHP and adds a few extra methods to aid
     * in the XML DOM manipulation.
     *
     * @category    XML
     * @author      Kristopher Wilson
     * @package     openavanti
     * @link        http://www.openavanti.com/documentation/docs/1.0.3/SimpleXMLElementExt
     */
    class SimpleXMLElementExt extends SimpleXMLElement
    {
    
        /**
         * Adds a child SimpleXMLElement node as a child of this node. This differs from the native
         * addChild() method in that it allows adding an XML node, not creating a tag.
         *
         * @param SimpleXMLElement $oChild The node to add as a child of this node
         */
        public function addChildNode( SimpleXMLElement $oChild ) 
        {
            $oParentDOM = dom_import_simplexml( $this );
            $oChildDOM = dom_import_simplexml( $oChild );
            $oNewParentDOM = $oParentDOM->ownerDocument->importNode( $oChildDOM, true );
            $oParentDOM->appendChild( $oNewParentDOM );
        
        } // addChildNode()
    
    
        /**
         * Clones this node recursively and returns the cloned node.
         *
         * @return SimpleXMLElementExt A copy of the current node
         */
        public function cloneNode()
        {
            $oDomNode = dom_import_simplexml( $this );
            $oNewNode = $oDomNode->cloneNode( true );
            
            return( simplexml_import_dom( $oNewNode, "SimpleXMLElementExt" ) );         
            
        } // cloneNode()
        
        
        /**
         * Removes the specified child from the XML DOM
         *
         * @param SimpleXMLElement $oChild The child node to remove from the parent DOM
         */
        public function removeChild( $oChild )
        {
            $oParentDOM = dom_import_simplexml( $this );
            $oChildDOM = dom_import_simplexml( $oChild );
            
            $oParentDOM->removeChild( $oChildDOM );
            
        } // removeChild()
        
        
        /**
         * Remove an attribute in a particular namespace
         *
         * @param string $sNS The namespace the attribute exists in
         * @param string $sAttribute The name of the attribute to remove
         */
        public function removeAttributeNS( $sNS, $sAttribute )
        {
            $oDOM = dom_import_simplexml( $this );
            
            $oDOM->removeAttributeNS( $sNS, $sAttribute );
            
        } // removeAttributeNS()
        
        
        /**
         * Remove an attribute from the current node. Note that if the attribute exists in a non-
         * default namespace, removeAttributeNS() should be used.
         *
         * @param string $sAttribute The attribute to remove
         */
        public function removeAttribute( $sAttribute )
        {
            $oDOM = dom_import_simplexml( $this );
            
            $oDOM->removeAttribute( $sAttribute );
            
        } // removeAttribute()
        
        
        /**
         * Adds an attribute to the current node in the specified namespace. 
         *
         * @param string $sNS The namespace that the new attribute belongs in
         * @param string $sAttribute The attribute to add to the node
         * @param string $sValue The value of the attribute to add to the node
         */
        public function addAttributeNS( $sNS, $sAttribute, $sValue )
        {
            $oDOM = dom_import_simplexml( $this );
            
            $oDOM->setAttributeNS( $sNS, $sAttribute, $sValue );
            
        } // removeAttributeNS()
        
        
        /**
         * Inserts the specified new node before the specified existing node. These nodes
         * must be children of the current SimpleXML element.
         *
         * @param SimpleXMLElement $oNewNode The new node to add to this nodes DOM
         * @param SimpleXMLElement $oRefNode An existing child node in this nodes DOM that becomes
         *      the next sibling of the new node.
         */
        public function insertBefore( $oNewNode, $oRefNode )
        {
            $oDOM = dom_import_simplexml( $this );
            
            $oNewNodeDOM = dom_import_simplexml( $oNewNode );
            $oRefNodeDOM = dom_import_simplexml( $oRefNode );            
            
            $oNewNodeDOM = $oDOM->ownerDocument->importNode( $oNewNodeDOM, true );
            
            $oDOM->insertBefore( $oNewNodeDOM, $oRefNodeDOM );
            
        } // insertBefore()
        
        
        /**
         * Inserts the specified new node after the specified existing node. These nodes
         * must be children of the current SimpleXML element.
         *
         * @param SimpleXMLElement $oNewNode The new node to add to this nodes DOM
         * @param SimpleXMLElement $oRefNode An existing child node in this nodes DOM that becomes
         *      the previous sibling of the new node.
         */
        public function insertAfter( $oNewNode, $oRefNode )
        {
            $oDOM = dom_import_simplexml( $this );
            
            $oNewNodeDOM = dom_import_simplexml( $oNewNode );
            $oRefNodeDOM = dom_import_simplexml( $oRefNode );
            
            $oNewNodeDOM = $oDOM->ownerDocument->importNode( $oNewNodeDOM, true );
            
            $oDOM->insertBefore( $oNewNodeDOM, $oRefNodeDOM->nextSibling );
            
        } // insertAfter()
        
        
        /**
         * Determines if this SimpleXML node has children nodes
         *
         * @return bool True if this node has children, false if it does not
         */
        public function hasChildNodes()
        {
            $oDOM = dom_import_simplexml( $this );
            
            return( $oDOM->hasChildNodes() );
        }
        
        
        /**
         * Returns the parent node of the current SimpleXML node
         *
         * @return SimpleXMLElement The parent of this node
         */
        public function getParent()
        {
            $oParent = current( $this->xpath( ".." ) );
            
            return( $oParent );
            
        }
        
    } // SimpleXMLElementExt()

?>

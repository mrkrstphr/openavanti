<?php
/**
 * OpenAvanti
 *
 * OpenAvanti is an open source, object oriented framework for PHP 5+
 *
 * @author          Kristopher Wilson
 * @copyright       Copyright (c) 2007-2010, Kristopher Wilson
 * @package         openavanti
 * @license         http://www.openavanti.com/license
 * @link            http://www.openavanti.com
 * @version         SVN: $Id$
 */

namespace OpenAvanti\Cvs\Subversion;

use \SimpleXMLElement;
use \stdClass;


/**
 * A class to aid in getting information about Subversion repositories
 *
 * @category    CVS
 * @author      Kristopher Wilson
 * @package     openavanti
 * @link        http://www.openavanti.com/documentation/1.4.0/SvnLook
 */

class SvnLook extends Svn
{
  
    /**
     *
     *
     */
    public function changed($revision)
    {
        $cmd = "svnlook changed -r {$revision} {$this->_repositoryUri}";
        
        return $this->processCommand($cmd);
    
    } // changed()


    /**
     *
     *
     */
    public function getChangedFiles($revision)
    {
        $output = explode("\n", $this->changed($revision));

        $changedFiles = array();

        foreach($output as $index => $file)
        {
            $operation = substr($file, 0, 1);
            $file = trim(substr($file, 1));

            $changedFiles[$index] = new \Stdclass();
            $changedFiles[$index]->file = $file;
            $changedFiles[$index]->operation = $operation;
        }

        return $changedFiles;
    
    } // getChangedFiles()

} // SvnLook()

?>

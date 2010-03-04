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

namespace OpenAvanti\CVS\Subversion;

use \SimpleXMLElement;
use \stdClass;


/**
 * A class to aid in getting information about Subversion repositories
 *
 * @category    CVS
 * @author      Kristopher Wilson
 * @package     openavanti
 * @link        http://www.openavanti.com/documentation/1.4.0/Subversion
 */

class Subversion
{
    /**
     * Stores the location of the repository
     */
    protected $_repositoryUri = null;
    
    
    /**
     * Accepts the Uri to the repository
     *
     * @param string $repository The Uri to the repository
     */
    public function __construct($repository)
    {
        $this->_repositoryUri = $repository;
    
    } // __construct()
    
    
    /**
     * Lists all files in the supplied path, or in the root of the repository if no path is
     * supplied. Accepts additional arguments to control the output of the command.
     *
     * @param string $path Optional; The path to list files for. Default: null
     * @param string $revision Optional; The revision to list files for. Default: null
     * @param string $options Optional; Additional options to pass to the ls command. Default: null
     * @return string The output of the ls command in XML format
     */
    public function ls($path = null, $revision = null, $options = null)
    {
        $cmd = "svn ls --xml {$this->_repositoryUri}/{$path} {$options}";
        
        if(!empty($revision))
            $cmd .= " -r {$revision}";
        
        return $this->processCommand($cmd, $options);
    
    } // ls()
    
    
    /**
     * Returns info about the supplied path, or info about the root of the repository if no path
     * is supplied. Accepts additional arguments to control the output of the command.
     *
     * @param string $path Optional; The path to get info for. Default: null
     * @param string $revision Optional; The revision to get info for. Default: null
     * @param string $options Optional; Additional options to pass to the info command.
     *      Default: null
     * @return string The output of the info command in XML format
     */
    public function info($path, $revision = null, $options = null)
    {
        $cmd = "svn info --xml {$this->_repositoryUri}/{$path}";
        
        if(!empty($revision))
            $cmd .= " -r {$revision}";
        
        return $this->processCommand($cmd);
        
    } // info()
    
    
    /**
     * Retrieves the supplied file for the specified revision, or the HEAD revision if no revision
     * is supplied.
     *
     * @param string $path The path to the file to retrieve, relative to the repository Uri
     * @param string $revision Optional; The revision number of the file to retrieve. Default: null
     * @return string The file contents
     */
    public function cat($path, $revision = null)
    {
        $cmd = "svn cat {$this->_repositoryUri}/{$path}";
        
        if(!empty($revision))
            $cmd .= " -r {$revision}";
        
        return $this->processCommand($cmd);
        
    } // cat()
    
    
    /**
     * Returns a list of logs for the specified path, or for the repository root if no path is
     * supplied. Accepts additional arguments to control the output of the command.
     *
     * @param string $path Optional; The path to get logs for. Default: null
     * @param string $revision Optional; The revision to get the log for. Default: null
     * @param string $options Optional; Additional options to pass to the log command.
     *      Default: null
     * @return string The output of the log command in XML format
     */
    public function log($path = null, $revision = null, $options = null)
    {
        $cmd = "svn log --xml {$this->_repositoryUri}/{$path} {$options}";
        
        if(!empty($revision))
            $cmd .= " -r {$revision}";
        
        return $this->processCommand($cmd);
        
    } // log()
    
    
    /**
     * Returns the output of an svn diff command on a supplied path for specific revisions. Accepts
     * additional arguments to control the output of the command.
     *
     * @param string $path The path to diff.
     * @param string $revision Optional; The revision to get the log for. Default: null
     * @param string $options Optional; Additional options to pass to the log command.
     *      Default: null
     * @return string The output of the log command in XML format
     */
    public function diff($path, $revisions, $options = null)
    {
        $cmd = "svn diff -r {$revisions} {$options} {$this->_repositoryUri}/{$path}";
        
        return $this->processCommand($cmd);
        
    } // diff()
    
    
    /**
     * Returns info about the given path, or the repository root if no path is supplied, for
     * the current revision, or the HEAD revision if no revision is supplied. Returns the output
     * as an easy to use object. 
     *
     * Object Properties:
     *
     *     kind = dir|file
     *     path 
     *     revision 
     *     url 
     *     root 
     *     last_commit 
     *     last_commit_rev 
     *     last_commit_by 
     *
     * @param string $path Optional; The path to return info for Default: null
     * @param string $revision Optional; The revision to return info for Default: null
     * @return StdClass The info data
     */
    public function getInfo($path = null, $revision = null)
    {
        $xml = $this->info($path, $revision);
        
        $xml = new SimpleXMLElement($xml);
        
        $ret = new stdClass();
        
        $ret->kind = (string)$xml->entry["kind"];
        $ret->path = (string)$xml->entry["path"];
        $ret->revision = (string)$xml->entry["revision"];
        $ret->url = (string)$xml->entry->url;
        $ret->root = (string)$xml->entry->repository->root;
        
        $ret->last_commit = (string)$xml->entry->commit->date;
        $ret->last_commit_rev = (string)$xml->entry->commit["revision"];
        $ret->last_commit_by = (string)$xml->entry->commit->author;
        
        return $ret;
        
    } // getInfo()
    
    
    /**
     * Returns the files in a given path, or the repository root if no path is supplied, for
     * the current revision, or the HEAD revision if no revision is supplied. Returns the output
     * as an easy to use object. 
     *
     * Object Properties:
     *
     *      directories = array
     *          Same output as files below
     *      files = array
     *          kind
     *          name 
     *          size 
     *          last_commit 
     *          last_commit_rev 
     *          last_commit_by 
     *
     * @param string $path Optional; The path to return files for Default: null
     * @param string $revision Optional; The revision to return files for Default: null
     * @return StdClass The info data
     */
    public function getListing($path = null, $revision = null)
    {
        $xml = $this->ls($path, $revision);
        
        $xml = new SimpleXMLElement($xml);
        
        $ret = new stdClass();
        
        $ret->directories = array();
        $ret->files = array();
        
        foreach($xml->list->entry as $entry)
        {
            $ret->files[(string)$entry->name] = new stdClass();
            
            $file = &$ret->files[(string)$entry->name];
            
            $file->kind = (string)$entry["kind"];
            $file->name = (string)$entry->name;
            $file->size = (string)$entry->size;
            //$file->human_size = TODO;
            
            $file->last_commit = (string)$entry->commit->date;
            $file->last_commit_rev = (string)$entry->commit["revision"];
            $file->last_commit_by = (string)$entry->commit->author;
            
            if($file->kind == "dir")
                $ret->directories[$file->name] = &$file;
        }
        
        return $ret;
    
    } // getListing()
    
    
    /**
     * Returns logs for the given path, or the repository root if no path is supplied, for
     * the current revision, or the HEAD revision if no revision is supplied. Returns the output
     * as an easy to use array of objects. 
     *
     * Object Properties:
     *
     *     revision 
     *     author 
     *     date 
     *     message
     *
     * @param string $path Optional; The path to return logs for Default: null
     * @param string $revision Optional; The revision to return logs for Default: null
     * @return array An array of logs
     */
    public function getLogs($path, $revision = null, $options = null)
    {
        $xml = $this->log($path, $revision, $options);
        
        $xml = new SimpleXMLElement($xml);
        
        $ret = array();
        
        foreach($xml->logentry as $logEntry)
        {
            $log = new stdClass();
            $log->revision = (string)$logEntry["revision"];
            $log->author = (string)$logEntry->author;
            $log->date = (string)$logEntry->date;
            $log->message = (string)$logEntry->msg;
            
            $ret[$log->revision] = $log;
        }
        
        return $ret;
        
    } // getLogs()
    
    
    /**
     * Responsible for running a command, preparing and returning the results
     *
     * @param string $command The command to run
     * @return string The output returned by the command
     */
    protected function processCommand($command)
    {
        $output = null;
        $retVal = null;
        
        exec($command, $output, $retVal);
        
        if($retVal !== 0)
            throw new \Exception("Command failed: {$command}");
        
        if(is_array($output))
            $output = implode("\n", $output);
        
        return $output;
        
    } // processCommand()
    
    
} // Subversion()

?>
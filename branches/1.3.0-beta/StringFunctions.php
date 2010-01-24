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
 
 
/**
 * A library for manipulating strings
 *
 * @category    String
 * @author      Kristopher Wilson
 * @link        http://www.openavanti.com/docs/stringfunctions
 */
class StringFunctions
{
    protected static $_consonants = "bcdfghjklmnpqrstvwxyz";
    protected static $_vowels = "aeiou";

    protected static $_dictionary = array(
        "hero" => "heroes", "datum" => "data", "bacterium" => "bacteria", 
        "criterion" => "criteria", "alumnus" => "alumni", "alumna" => "alumnae", 
        "person" => "people", "deer" => "deer", "beer" => "beer", "goose" => "geese", 
        "mouse" => "mice", "content" => "content", "template" => "templates", 
        "man" => "men", "woman" => "women", "child" => "children", "calf" => "calves", 
        "elf" => "elves", "half" => "halves", "hoof" => "hooves", "knife" => "knives", 
        "leaf" => "leaves", "life" => "lives", "loaf" => "loaves",
        "scarf" => "scarves", "self" => "selves", "sheaf" => "sheaves",
        "shelf" => "shelves", "thief" => "thieves", "wife" => "wives", "wolf" => "wolves",
        "fireman" => "firemen","foot" => "feet", "louse" => "lice", "man" => "men",
        "tooth" => "teeth", "woman" => "women", "ox" => "oxen", "echo" => "echoes",
        "embargo" => "embargoes", "potato" => "potatoes", "tomato" => "tomatoes",
        "torpedo" => "torpedoes", "veto" => "vetoes", "cod" => "cod", "deer" => "deer",
        "fish" => "fish", "offspring" => "offspring", "perch" => "perch",
        "sheep" => "sheep", "trout" => "trout", "barracks" => "barracks", 
        "crossroads" => "crossroads", "die" => "dice", "gallows" => "gallows",
        "headquarters" => "headquarters", "means" => "means", "series" => "series",
        "species" => "species", "alga" => "algae", "amoeba" => "amoebae",
        "antenna" => "antennae", "formula" => "formulae", "larva" => "larvae",
        "nebula" => "nebulae", "vertebra" => "vertebrae", "ialumnus" => "alumni",
        "bacillus" => "bacilli", "cactus" => "cacti", "focus" => "foci",
        "fungus" => "fungi", "nucleus" => "nuclei", "octopus" => "octopi",
        "radius" => "radii", "stimulus" => "stimuli", "syllabus" => "syllabi",
        "terminus" => "termini", "addendum" => "addenda", "biacterium" => "bacteria",
        "curriculum" => "curricula", "datum" => "data", "erratum" => "errata",
        "medium" => "media", "memorandum" => "memoranda", "ovum" => "ova",
        "stratum" => "strata", "symposium" => "symposia", "apex" => "apices",
        "appendix" => "appendices", "cervix" => "cervices", "index" => "indices",
        "matrix" => "matrices", "vortex" => "vortices", "analysis" => "analyses",
        "axis" => "axes", "basis" => "bases", "crisis" => "crises", "diagnosis" => "diagnoses",
        "emphasis" => "emphases", "hypothesis" => "hypotheses", "neurosis" => "neuroses",
        "oasis" => "oases", "parenthesis" => "parentheses", "synopsis" => "synopses",
        "thesis" => "theses", "criterion" => "criteria", "phenomenon" => "phenomena",
        "automaton" => "automata", "piano" => "pianos", "homo" => "homos",
        "pro" => "pros", "calf" => "calves", "knife" => "knives",
        "swine" => "swine", "bison" => "bison", "aircraft" => "aircraft",
    );


    /**
     *
     *
     */                     
    private function __construct()
    {
        // this class cannot be instantiated
        
    } // __construct()


    /**
     * Attempts to turn a supplied string, preferably an English, singular word, into the
     * plural version of the string.
     *
     * @argument string the singular word to attempt to make plural
     * @returns string the result of attempting to make the word plural
     */
    public static function toSingular($inputString)
    {
        $dictionary = array_flip(self::$_dictionary);
        
        if(isset($dictionary[strtolower($inputString)]))
        {
            return $dictionary[strtolower($inputString)];
        }
    
        if(substr($inputString, strlen($inputString) - 3, 3) == "ies")
        {
            $inputString = substr($inputString, 0, strlen($inputString) - 3) . "y";
        }
        else if(substr($inputString, strlen($inputString) - 2, 2) == "es")
        {
            $inputString = substr($inputString, 0, strlen($inputString) - 2);
        }
        else if(substr($inputString, strlen($inputString) - 1, 1) == "s")
        {
            $inputString = substr($inputString, 0, strlen($inputString) - 1);
        }
        
        return $inputString;
    
    } // toSingular()
    
    
    /**
     * Attempts to turn a supplied string, preferably an English, plural word, into the
     * singular version of the string.
     *
     * @argument string the plural word to attempt to make singular
     * @returns string the result of attempting to make the word singular
     */
    public static function toPlural($inputString)
    {       
        $dictionary = self::$_dictionary;
        
        if(isset($dictionary[strtolower($inputString)]))
        {
            return $dictionary[strtolower($inputString)];
        }
    
        if(substr($inputString, strlen($inputString) - 1, 1) == "y")
        {
            $beforeTheY = substr($inputString, 0, strlen($inputString) - 1);

            if(strpos(self::$_vowels, $beforeTheY) !== false)
            {
                $inputString .= "s";
            }
            else
            {
                $inputString = substr($inputString, 0, strlen($inputString) - 1 ) . "ies";
            }
        }
        elseif(substr($inputString, strlen($inputString) - 1, 1) == "x")
        {
            $inputString .= "es";
        }
        elseif(substr($inputString, strlen($inputString) - 1, 1) == "s")
        {
            $inputString .= "es";
        }
        elseif(substr($inputString, strlen($inputString) - 2, 2) == "ch")
        {
            $inputString .= "es";
        }
        else
        {
            $inputString .= "s";
        }
        
        return $inputString;
    
    } // toPlural()
    
    
    /**
     * Allows for adding custom words to the single/plural dictionary used by the toSingular()
     * and toPlural() methods. Either two strings can be supplied as arguments, the singular
     * and plural forms of the words, respectively, or one array can be given that contains
     * an associative array of rules in the form of singular => plural.
     *
     * @argument string|array Either the singular form of the word, or an array of 
     *      single => plural
     * @argument string Optional; Either the plural form of the word or null if an array was
     *      passed for the first argument
     * @returns array A copy of the dictionary
     */
    public static function addToDictionary($single, $plural = null)
    {
        if(is_array($single))
        {
            // We could simply add or merge this array with the dictionary, but let's loop
            // each element and make sure both the key and value are strings to try to prevent
            // bad data:
            
            foreach($single as $key => $value)
            {
                if(is_string($key) && is_string($value))
                {
                    self::$_dictionary[strtolower(strval($key))] = strtolower(strval($value));  
                }
            }
        }
        else if(!empty($plural) && is_string($plural))
        {
            self::$_dictionary[strtolower(strval($single))] = strtolower(strval($plural));
        }
        
        return self::$_dictionary;
        
    } // addToDictionary()
    
    
    /**
     * Allows the retrieval of the full dictionary used by toSingular() and toPlural().
     *
     * @returns array A copy of the dictionary
     */
    public function getDictionary()
    {
        return self::$_dictionary;
        
    } // getDictionary()
    
    
    /**
     * Returns a substring of the supplied string, starting after the last occurrence   of the
     * supplied delimiter.        
     *
     * @argument string The string we're generating a substring from
     * @argument string The delimiter that we're searching for
     * @returns string The generated substring
     */
    public static function afterLastOccurrenceOf($inputString, $deliminator)
    {
        return(substr($inputString, strrpos($inputString, $deliminator) + 1));
    
    } // afterLastOccurrenceOf()

} // StringFunctions()

?>

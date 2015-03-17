# Developer Documentation #

The purpose of this page is to outline the development standards for the OpenAvanti project. These standards should be followed to provide a cohesive development environment and set of source files.

## phpdoc ##

[phpdoc](http://en.wikipedia.org/wiki/PHPDoc) style comment blogs should be added to the beginning of each file, as well as the beginning of each class and class member, including constants, variables and methods. These code comments are used to generate the functional documentation for each version of the library.

## Coding Standards ##

Rather than outline how many spaces should go after a comma, it is recommended that developers review existing code to understand the standards that they should follow in formatting their code.

All newly written code should follow the formatting of all previously written code to provide a concise feel to the source.

## Subversion ##

The following standards should be followed when committing source to Subversion:
  * Broken code should not be committed to the trunk or branches where multiple developers are working on the source.
  * All new files should have the following snippet added to the opening comment block:
> `@version         SVN: $Id$`
  * Along with the `$Id$` tag, each new file should have `Id` keyword added to it:
> `svn propset svn:keywords "Id" path/to/file.php`
  * A policy of frequent commits should be adopted. Once a functional portion of code is completed, it should be committed before the next piece is developed. The purpose of this is to prevent commits with large amounts of files and massive changes that make it hard to follow what was changed in the revision.
  * A summary commit message explaining the change should be provided for each commit
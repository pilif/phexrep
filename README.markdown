phexrep exception reporting
===========================

Introduction
------------
phexrep is an exception logging and viewing utility for PHP applications. It's heavily inspired (in fact, even the layout is stolen) from the exception_logger plugin which was made for Ruby on Rails. The idea is that you add some exception logging facility to your application (not currently part of this release), so that the exception information is logged into the database.

Then you create a configuration file and call scripts/build_package.php.

This will create a .phar-File that you can (if needed) rename to .php and copy it to whatever place you want to have it on the server you want to access the exception log information.

You will get a view of the 20 latest exceptions (more view options are planned), including a full backtrace with previews of the files and a link to a webbased repository - if you configure phexrep to do so.

Requirements
------------
 * PHP 5.3 - phexrep makes use of the Phar facility (though that on is optional) and of anonymous functions. Thus, PHP 5.3 is required.
 * PostgreSQL - for now, phexrep's backend is bound to PostgreSQL as this is what I initially needed. Later versions will fix this.
 * Some error logging facility that logs to said PostgreSQL database. The schema is included as part of phexrep.
 

Database
--------
Before you can put phexrep to use, you need to log exceptions. For this, you will have to create a table in a PostgreSQL database (other DBs will follow in the future) using the schema that you'll find in the util subdirectory of this package.

The columns provided in the schema file are mandatory, but you can add whatever other additional column you would want. All of them will be blindly shown in the exception report without any additional formatting.

One column is special. It's the error_info column which contains additional unstructured information about the error which is stored by serialize()ing an array with the information. 

Here's how my internal exception handler looks to give you an idea:

    private static function collectErrorInfo(Exception $ex){
        if (is_object($_SESSION['user'])){
            $error['cid']  = $_SESSION['user']->getID();
        }
        $error['no']   = $ex->getCode();
        $error['text'] = get_class($ex).': '.$ex->getMessage();
        $error['webserver'] = $_SERVER['HTTP_HOST'];
        $error['file'] = $ex->getFile();
        $error['line'] = $ex->getLine();
        $error['context'] = '';
        $error['uri'] = $_SERVER['REQUEST_URI'];
        $error['callstack_string'] = $ex->getTraceAsString();
        $error['callstack'] = $ex->getTrace();
        $error['type'] = get_class($ex);
        $error['message'] = $ex->getMessage();
        $error['remote_addr'] = $_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $error['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        try{
            $error['release'] = self::readVersion();
        }catch(Exception $e){
            $error['release'] = '<error>';
        }
        try{
            $error['gitinfo'] = self::getGitInfo();
        }catch(Exception $e){
            $error['gitinfo'] = '<error>';
        }
        return $error;
    }
    
what this function returns is then serialize()d and stored in that error_info column. All fields are optional besides callstack, line and file. Whatever comes out of unserialize()ing this array is then merged with the other fields of the table overriding already existing fields.

Crude. I know, but it does its job for a 0.0.1 release :-)

Configuration
-------------
phexrep needs to be configured by providing a config.ini either in the parent directory or when building the phar archive. A sample config.ini is provided as `config.ini.template`, though you might want to change some of its contents.

Here's a description of the various settings.

### Section: database
*user*: The username you use to connect to your PostgreSQL database
*dbname*: The database that contains the exception_logging table
*password*: The password needed to conntect to postgres (can be missing from the INI file if you don't use a password)

### Section access_control
When you are using the phar-facility and you specify an access_control section in your ini file, then phexrep will require authentication to be used. In this case, configure `username` to be the username you want to use in HTTP authentication and `password` to the output of PHP's crypt() with any salt you want. 

The password in the template file is "password" encrypted using crypt() and as salt of $2a$07$/eI4PkBQVhwcigIpSXci$, so using blowfish encryption and a complexity of 7.

### Section report_format
This secion allows you to configure the format of the reports, enabling additional features in the process.

*app_root* is the root of your application on the server. When app_root is specified, shortened paths will be shown in the backtrace and the previewing of files will be made possible. phexrep refuses to allow you to preview files if `app_root` is not configured. You can set this to % which will be replaced by whatever $_SERVER['DOCUMENT_ROOT'] is set to.

*web_repo* is the URL of some kind of web based repository like [Trac](http://trac.edgewall.org/) or [Redmine](http://www.redmine.org/). There are some placeholders that you should put into the URL so that phexrep can link to the correct file at the correct revision and line, but before I'm going into those, there are two more fields I need to explain:

*revision_field* is the name of a field in the exception_logging table (or in the serialized array inside `exception_logging`) that contains the specific revision of the repository that was current when the exception occurred. Usually you can get at that using some $Revision$ logic in SVN or just a little bit of filesystem magic in case of git (cat `cut -d ' ' -f 2 HEAD` in the .git directory).

*default_revision* will be used when building the web_repo link if no revision could be determined.

But now back to *web_repo* and the placeholders. phexrep supports %r which will be the revision as determined by looking at the error data and the revision_field configuration setting. %pi is the path to the file (minus `app_root`) with slashes left alone and individual components URLencoded. %pu is the same thing, but slashes also encoded. You'd use that in case your tool requires the file to link to in form of a GET parameter.

Finally, there's %l which is the line number of the callstack.

The template file contains a possible configuration for redmine assuming that there's a "revision" column in the exception logging database

Building
--------
While you can run phexrep stand-alone, I would recommend you to build one of these cool phar-archives and upload that directly to any web application you want to monitor. No multiple files to handle. Just one neatly bundled containing all that's needed.

A build script that does the right thing is included with the distribution:

just call 

    % php build_package.php <name_of_file>.phar <name of config file>
    
like so:

    % php build_package.php exrep.phar config.ini
    
While .phar is required at call time, you may rename it to .php, upload that file to any PHP 5.3 enabled host and call it directly from the web browser. Isn't this wonderfully easy? I certainly think so.

API
---
phexrep's UI is done completely in JavaScript. The PHP part of the application just exports JSON which is then consumed by the JavaScript UI. This of course means that you can access that data, replacing my crappy frontend by something else.

The API endpoint is in public/api.php though from the phar-file, that's just api.php. It's more or less self-discoverable and restful, so you should be able to quickly grasp it yourself. (hint: GET /api.php/exceptions and GET /api.php/exceptions/{id} is probably all you need).

TODO
----
This early release, while useful in theory (and about to being used in production), of course is lacking features. Here's the list of stuff I will probably fix over time (or incorporate patches if you feel inclined to send any):

* All the view specific UI elements do nothing. You get a list of the 20 last exceptions and that's it. No searching, no filtering, no nothing.
* The preview, while being cool, should probably display a few lines before and after the exception, not 10 lines beginning with the line the fault happened on.
* While PostgreSQL is an awesome database, a bit more variety in support wouldn't probably hurt - especially as the schema is so dead simple.
* Maybe allow support for deleting exceptions?

License
-------
Released under the MIT license and copyright 2010 by Philip Hofstetter. See LICENSE.
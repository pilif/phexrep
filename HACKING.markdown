Hacking phexrep
===============

In case you intend to contribute to phexrep, there are a couple of notes to keep in mind which I'm presenting here.

Autoloading
-----------
First, there's the concept of the autoload compilation. Instead of forcing you to have one class per file or a fixed directory structure represening a class name, phexrep, while still doing autoloading requires none of that.

On the other hand, doing autoloading still means that some component needs to know in which file a class is declared.

In comes scripts/autoload_compile.php

When you run that file, the script will go through all the PHP files in lib/ and, using PHP's tokenizer will extract all class and interface names it finds. Then it writes the findings in form of an associative array to lib/_autoload.php which is then included and used in init.php (which otherwise sets up paths and defines a couple of constants for convenience)

This means that whenever you add a new class to either an existing or a new file, you have to run autoload_compile.php if you intend that class to be usable.

Personally, I prefer the freedom of having any class in any file anywhere to a strictly enforced path structure like Java enforces it.

Adding URL Endpoints
--------------------
All requests should go through api.php.

api.php manually includes all php files in lib/controllers which, on inclusion register the classes declared therein (plus the endpoint name and an URL regex) with RequestMapper (mapper.php).

api.php then uses RequestMapper to dispatch any call and route it to the respective controller.

This means, that if you want to add another API endpoint besides /exceptions and /preview, that you just create another controller and register it.

Controllers inherit from BaseController overriding the abstract method handle() which should return something that can be processed by json_encode.

Your controllers (and for sure not the models they use) should produce no output.
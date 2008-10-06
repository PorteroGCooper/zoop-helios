 oooooooooooo                                     .o        oooooooo 
d'""""""d888'                                   o888       dP""""""" 
      .888P    .ooooo.   .ooooo.  oo.ooooo.      888      d88888b.   
     d888'    d88' `88b d88' `88b  888' `88b     888          `Y88b  
   .888P      888   888 888   888  888   888     888            ]88  
  d888'    .P 888   888 888   888  888   888     888  .o. o.   .88P  
.8888888888P  `Y8bod8P' `Y8bod8P'  888bod8P'    o888o Y8P `8bd88P'   
                                   888                               
                                  o888o                              

Zoop is a recursive acronym which stands for Zoop Object Oriented PHP Framework.

Far from being Yet Another PHP Framework or Rails clone, Zoop has been in development since 2001 and in use for the last 6 years in a number of different production environments. While it predates the recent proliferation of PHP frameworks, it's based on solid MVC principles, including separation of display, logic, and data layers. It's designed to be efficient, modular, and extensible, striking a balance between lightweight and fully-featured.

With Zoop an inexperienced coder can make secure web applications quickly. More experienced coders will appreciate the design and flexibility. Both will benefit from the shortcuts it provides to handle common and mundane tasks.

Zoop's integrated error handling can be configured to log errors for production environments, and is highly informative and readable which makes bugs easy to find and squash.

The Zoop Framework is inclusive, cooperating with and containing components integrated from some existing projects including Smarty, the Prototype JS Framework, and a number of Pear Modules. We're not content just to cobble pieces together, however -- for example, we've combined the above into an implementation that brings GuiControls to PHP, providing developers with easy access to rich form widgets with client-side validation completely integrated. We're also working to include support for pieces from some of the latest and greatest PHP and Javascript projects, including the Zend Framework, PHP Doctrine, and jQuery, among others. 

The Zoop Framework can be found at http://zoopframework.com

					 ----------
					| FEATURES |
					 ----------
Zoop has the following enhanced features:

For Programmers:

* Extensible component architecture
* Supports popular database servers provided by PDO or PEAR-DB including:
	mysql
	mysqli
	pgsql
	sqlite
* GuiControls (nicest thing to happen to web development)
* Forms (simplified form / db integration)
* Full AJAX Support
* Automatic Validation of forms
* Built in Security Features
* Independence of data, logic & presentation layers
* Comprehensive error handling (Integrated Backtrace)
* Authentication management
* Integrated File Uploading / Management
* Integration with PEAR libraries
* PHP coding standards
* Platform/PHP version/browser independence
* Built-in support for XML-RPC including an XML-RPC server
* PDF creation
* SMTP Email Messaging
* Template based Email Messaging
* Fast, flexible templating powered by smarty
* Compatibile with PHP4 and PHP5
* Sequences (Navigation Management / Action Request Workflow)
* A Nice Skeleton program to get you started on any project
* Compatible with mod_php as well as php-cli and php-fcgi (fastCGI)




					 -----------
					| CHANGELOG |
					 -----------

This version of The Zoop Framework brings a number of major enhancements, new components as well as the usual bug fixes and performance optimizations.

Improvements
* Zcache now has modular back end, supporting file-based or MemCached caching.
* View and Controller Zcache integration.
* Inline template caching with {zcache id=$X group=$Y ttl=$Z} Cached Content {/zcache}
* Improved user input filtering.
* guiControl API redesigned and standardized. 
* JS Validation provided by Prototype, integrated with back end validation through guiControls.
* FastCGI support. 
* PDO now default back end for Zoop DB.
* Full configurable support for mod_rewrite.
* Zoop Create script to generate projects, zones and config from the CLI.
* Better error handling.
* Better security.
* Controller Zones further relationships supporting full ancestry. 
* Further integration of View and Controller with new $zone->guiDisplay.
* Introduction of new canonicalizeTemplate function to standardize templating.
* GUI now supports inline template caching. 
* Forms2 refined to automate CRUD operations. 
* Full soap support through integration of Nusoap component.
* Rewrite of Mail component for better support including sending personalized emails to multiple recipients.
* Application structure cleanup, controller zones can be placed in a zones/ subdirectory.
* Example apps.
* Updated and revised Skeleton.

New Components
* Auth. Providing Authentication and group, role and users based permissions. (beta).
* Simpletest. Provides a standard unittesting framework for Zoop and Zoop based applications. 
* Nusoap. Provides full soap server and client.
* Graphic. Outputs PDFs and images from Smarty templates.
* Chart. Extends Graphic to support bar, line and pie charts. 
* guiWidgets. Introduced as a simplified version of a guiControl. 
* guiWidgets are not tied into a form, nor do they validate, they simply render data into html. An OO approach to designing HTML. 
* Full Zcache Rewrite
* Full DB Rewrite

				  --------------
				 | REQUIREMENTS |
				  --------------

* A working php version 4.3.10 and up
* Optionally The following PEAR packages:
	Starting with ZooP 1.2 the necessary pear packages can be found from at http://zoopframework.com
	ZooP 1.2 and later can read from ZOOP_DIR/libs as well as the system wide pear repository.

	DATE
	XML_TREE
	CACHE_LITE		(only if using forms and / or cache)
	PEAR-DB 		(only if using a database)
	Mail 			(only if using mail framework)
	Mail_Mime 		(only if using mail framework)
	VFS_SQL 		(only if using storage framework)
	VFS 			(only if using userfiles framework)
	XML_Serializer 		(only if using forms)

				 -----------------
				| HOW TO USE ZOOP |
				 -----------------

1. Download both Zoop and the skeleton from http://zoopframework.com.
2. Place zoop in a location accessable to the webserver.
	Zoop is now installed!

3. Place the skeleton in a public directory.
4. Edit the config files config.php and in the config/ directory.
	Make sure to define where Zoop is located in config.php.
5. Edit includes.php to include the zones & frameworks you are using.
6. Point a browser at the public directory where you placed the skeleton.
7. You should see a login screen.
8. Edit zone_default.php to fit your liking.

Since Zoop resides seperately from your Zoop based applications.
One instance of Zoop can be used by many different applications.

				   -----------
				  | COPYRIGHT |
				   -----------

Copyright (c) 2008 Supernerd LLC and Contributors.
All Rights Reserved.

This software is subject to the provisions of the Zope Public License,
Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
FOR A PARTICULAR PURPOSE.

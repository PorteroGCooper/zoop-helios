     oooooooooooo                     ooooooooo.        .o        .oooo.   
     '""""""d888'                     `888   `Y88.    o888      .dP""Y88b  
          .888P    .ooooo.   .ooooo.   888   .d88'     888            ]8P' 
         d888'    d88' `88b d88' `88b  888ooo88P'      888          .d8P'  
       .888P      888   888 888   888  888             888        .dP'     
      d888'    .  888   888 888   888  888             888  .o. .oP     .o 
    .8888888888P  `Y8bod8P' `Y8bod8P' o888o           o888o Y8P 8888888888 
                                                                           
The Zoop Object Oriented Php Framework, The Zoop Framework for short, A framework 
written in and for php.

Zoop is an object oriented framework for PHP based on a front controller. It is 
designed to be very fast and efficient and very nice for the programmer to work 
with. It is easily extensible, and you need only include the functionality you use.


Zoop is based on the pehppy framework and is developed by the same group. 
In essence Zoop 1.x is the next major version of pehppy. 
We decided to rename it to zoop because:

	1. This version broke compatibility with pehppy without reconfiguring and
	   some rewriting of code.
	2. This version more than doubles the features had in pehppy.
	3. We didn't really like the name pehppy all that much.

Zoop integrates many different projects including smarty
(http://smarty.php.net) and the prototype AJAX framework. It also makes use of
many PEAR functions (http://pear.php.net)

With Zoop an inexperienced coder can make secure web applications
quickly. It will even look fairly attractive. A more experienced coder
will really appreciate how flexible Zoop is. The experienced coder will
appreciate the automations that are at his/her disposal to handle mundane tasks.

The Zoop Framework can be found at http://zoopframework.com

					 ------------
					| Zoop Group |
					 ------------
Zoop has been developed over the past 5 years by a core group of coders mostly
affiliated with Supernerd LLC (http://supernerd.com).

Project Manager:
		Steve Francia : sfrancia@supernerd.com
Lead Developers:
		John LeSueur : john@supernerd.com
		Steve Francia : sfrancia@supernerd.com
Contributors:
		Rick Gigger : rgigger@supernerd.com
		Richard Bateman : rbateman@supernerd.com



					 ----------
					| FEATURES |
					 ----------
Zoop has the following enhanced features:

For Programmers:

* Extensible component architecture
* Supports popular database servers provided by PEAR-DB including:
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

				  --------------
				 | REQUIREMENTS |
				  --------------

* A working php above version 4.3.0
* The following PEAR packages:
	Starting with ZooP 1.2 the necessary pear packages can be found from at http://zoopframework.com
	ZooP 1.2 and later can read from zoop_dir/libs as well as the system wide pear repository.

	DATE
	XML_TREE
	CACHE_LITE		(only if using forms and / or cache)
	PEAR-DB 		(only if using a database)
	Mail 			(only if using mail framework)
	Mail_Mime 		(only if using mail framework)
	VFS_SQL 		(only if using storage framework)
	VFS 			(only if using userfiles framework)
	XML_Serializer 	(only if using forms)

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

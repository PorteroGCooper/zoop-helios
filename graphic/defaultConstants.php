<?php

if(PHP_OS == 'Windows' || PHP_OS == 'WINNT')
	define_once('graphic_image_fontfile', "C:\Windows\fonts\verdana.ttf");
//On other systems, there is no easy standard location for fonts. Even on windows, the above may be a mistake.

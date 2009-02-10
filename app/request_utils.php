<?php

/**
 * HTTP request utilities collection.
 */

function getPost($var_name = false) {
	deprecated('Use POST::get() instead of getPost()');
	return POST::get($var_name);
}

function getGet($var_name = false) {
	deprecated('Use GET::get() instead of getGet()');
	return GET::get($var_name);
}

function findPostItem($var_name = false) {
	deprecated('This function has been deprecated. Please access POST variables through POST::get(), POST::getText(), POST::getInt(), POST::getBool() or--if you absolutely need it--through POST::getRaw().');
	return;
}

function getGetIsset($var_name = false) {
	deprecated('Use GET::varIsset() instead of getGetIsset()');
	return GET::varIsset($var_name);
}

function getPostIsset($var_name = false) {
	deprecated('Use POST::varIsset() instead of getPostIsset()');
	return POST::varIsset($var_name);
}

function getGetBool($var_name = false) {
	deprecated('Use GET::getBool() instead of getGetBool()');
	return GET::getBool($var_name);
}

function getPostBool($var_name = false) {
	deprecated('Use POST::getBool() instead of getPostBool()');
	return POST::getBool($var_name);
}

function getPostCheckbox($var_name = false) {
	deprecated('Use POST::getBool instead of getPostCheckbox()');
	return POST::getBool($var_name);
}

function unsetPost($var_name) {
	deprecated('Use POST::unsetVar() instead of unsetPost()');
	return POST::unsetVar($var_name);
}

function unsetGet($var_name) {
	deprecated('Use GET::unsetVar() instead of unsetGet()');
	return GET::unsetVar($var_name);
}

function getGetKeys($var_name = false) {
	deprecated('Use GET::getKeys() instead of getGetKeys()');
	return GET::getKeys($var_name);
}

function getPostKeys($var_name = false) {
	deprecated('Use POST::getKeys() instead of getPostKeys()');
	return POST::getKeys($var_name);
}

function getRawPost($var_name = false) {
	deprecated('Use POST::getRaw() instead of getRawPost()');
	return POST::getRaw($var_name);
}

function getRawGet($var_name = false) {
	deprecated('Use GET::getRaw() instead of getRawGet()');
	return GET::getRaw($var_name);
}

function getPostHTML($var_name = false) {
	deprecated('Use POST::getHTML() instead of getPostHTML()');
	return POST::getHTML($var_name);
}

function getGetHTML($var_name = false) {
	deprecated('Use GET::getHTML() instead of getGetHTML()');
	return GET::getHTML($var_name);
}

function getGetText($var_name = false) {
	deprecated('Use GET::getText() instead of getGetText()');
	return GET::getText($var_name);
}

function getPostText($var_name = false) {
	deprecated('Use POST::getText() instead of getPostText()');
	return POST::getText($var_name);
}

function getGetInt($var_name) {
	deprecated('Use GET::getInt() instead of getGetInt()');
	return GET::getInt($var_name);
}

function getPostInt($var_name) {
	deprecated('Use POST::getInt() instead of getPostInt()');
	return POST::getInt($var_name);
}
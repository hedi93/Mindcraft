<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * File upload
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_mindcraft
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

header('content-type: application/json');
$headers = getallheaders();
$option = new stdClass();
$source = file_get_contents('php://input');

if($headers['x-upload-type'] == 'img'){
	$type = array('image/png', 'image/gif', 'image/jpeg', 'image/jpg');
}
elseif($headers['x-upload-type'] == 'file'){
	$type = array('application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip', 'application/pdf');
}

if($headers['x-file-size'] > 5242880){
	$option->error = get_string('maxsize', 'mindcraft');
}
elseif(!in_array($headers['x-file-type'], $type)){
	$option->error = get_string('unsupportedformat', 'mindcraft');
}
else{
	if(isset($headers['x-param-value'])){
		if($headers['x-upload-type'] == 'img'){
			$path = 'ressources/';
		}
		elseif($headers['x-upload-type'] == 'file'){
			$path = 'ressources/files/';
		}
		if(!strstr($headers['x-param-value'], 'images/default/')){
			unlink($path.$headers['x-param-value']);
		}
	}
	// create a name for the uploaded file
	$nodeid = $headers['x-node-id'];
	$mindcraftid = $headers['x-mindcraft-id'];
	$extension = strtolower(strrchr($headers['x-file-name'], '.'));
	$name = sha1($nodeid.$mindcraftid);
	$name .= $extension;
	if($headers['x-upload-type'] == 'img'){
		file_put_contents('ressources/images/'.$name, $source);
		$option->name = $name;
		$option->content = '<img src="ressources/images/'.$name.'" alt="" />';
	}
	elseif($headers['x-upload-type'] == 'file'){
		file_put_contents('ressources/files/'.$name, $source);
		$option->name = $name;
		$option->nameforusers = $headers['x-file-name'];
		$doc = array('application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
		$xls = array('application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		$ppt = array('application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
		if(in_array($headers['x-file-type'], $doc)){
			$img = 'doc-icon.png';
		}
		elseif(in_array($headers['x-file-type'], $ppt)){
			$img = 'ppt-icon.png';
		}
		elseif(in_array($headers['x-file-type'], $xls)){
			$img = 'xls-icon.png';
		}
		elseif($headers['x-file-type'] == 'application/pdf'){
			$img = 'pdf-icon.png';
		}
		elseif($headers['x-file-type'] == 'application/zip'){
			$img = 'zip-icon.png';
		}
		else{
			$img = 'file-icon.png';
		}
		$option->fileIcon = 'images/' . $img;
		$option->content = '<img src="ressources/images/' . $img . '" alt="' . $headers['x-file-name'] . '" />';
	}
}

echo json_encode($option);
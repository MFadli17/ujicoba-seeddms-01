<?php
//    MyDMS. Document Management System
//    Copyright (C) 2002-2005  Markus Westphal
//    Copyright (C) 2006-2008 Malcolm Cowe
//    Copyright (C) 2010-2016 Uwe Steinmann
//
//    This program is free software; you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation; either version 2 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program; if not, write to the Free Software
//    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

include("../inc/inc.Settings.php");
include("../inc/inc.Utils.php");
include("../inc/inc.LogInit.php");
include("../inc/inc.Language.php");
include("../inc/inc.Init.php");
include("../inc/inc.Extension.php");
include("../inc/inc.DBInit.php");
include("../inc/inc.ClassUI.php");
include("../inc/inc.Authentication.php");

if (!$user->isAdmin()) {
	UI::exitError(getMLText("admin_tools"),getMLText("access_denied"));
}

if (isset($_POST["action"])) $action=$_POST["action"];
else $action=NULL;

// Add new category ---------------------------------------------------------
if ($action == "addcategory") {
	
	/* Check if the form data comes from a trusted request */
	if(!checkFormKey('addcategory')) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_request_token"));
	}

	$name = trim($_POST["name"]);
	if($name == '') {
		UI::exitError(getMLText("admin_tools"),getMLText("category_noname"));
	}
	if (is_object($dms->getDocumentCategoryByName($name))) {
		UI::exitError(getMLText("admin_tools"),getMLText("category_exists"));
	}
	$newCategory = $dms->addDocumentCategory($name);
	if (!$newCategory) {
		UI::exitError(getMLText("admin_tools"),getMLText("error_occured"));
	}
	$categoryid=$newCategory->getID();

	$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_add_category')));
	add_log_line(".php&action=addcategory&categoryid=".$categoryid);
}

// Delete category ---------------------------------------------------------
else if ($action == "removecategory") {

	/* Check if the form data comes from a trusted request */
	if(!checkFormKey('removecategory')) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_request_token"));
	}

	if (!isset($_POST["categoryid"]) || !is_numeric($_POST["categoryid"]) || intval($_POST["categoryid"])<1) {
		UI::exitError(getMLText("admin_tools"),getMLText("unknown_document_category"));
	}
	$categoryid = $_POST["categoryid"];
	$category = $dms->getDocumentCategory($categoryid);
	if (!is_object($category)) {
		UI::exitError(getMLText("admin_tools"),getMLText("unknown_document_category"));
	}

	if (!$category->remove()) {
		UI::exitError(getMLText("admin_tools"),getMLText("error_occured"));
	}

	$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_rm_category')));
	add_log_line(".php&action=removecategory&categoryid=".$categoryid);
	$categoryid=-1;
}

// Edit category -----------------------------------------------------------
else if ($action == "editcategory") {

	/* Check if the form data comes from a trusted request */
	if(!checkFormKey('editcategory')) {
		UI::exitError(getMLText("admin_tools"),getMLText("invalid_request_token"));
	}

	if (!isset($_POST["categoryid"]) || !is_numeric($_POST["categoryid"]) || intval($_POST["categoryid"])<1) {
		UI::exitError(getMLText("admin_tools"),getMLText("unknown_document_category"));
	}
	$categoryid = $_POST["categoryid"];
	$category = $dms->getDocumentCategory($categoryid);
	if (!is_object($category)) {
		UI::exitError(getMLText("admin_tools"),getMLText("unknown_document_category"));
	}

	$name = $_POST["name"];
	if (!$category->setName($name)) {
		UI::exitError(getMLText("admin_tools"),getMLText("error_occured"));
	}

	$session->setSplashMsg(array('type'=>'success', 'msg'=>getMLText('splash_edit_category')));
	add_log_line(".php&action=editcategory&categoryid=".$categoryid);
}

else {
	UI::exitError(getMLText("admin_tools"),getMLText("unknown_command"));
}

header("Location:../out/out.Categories.php?categoryid=".$categoryid);


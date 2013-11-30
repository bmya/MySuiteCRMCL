<?php
//Included to provide a little security to the system
require_once('config.php');
//**********************************************************************************
//   Home Page Manager Admin Menu File
//**********************************************************************************
$admin_option_defs=array();
$admin_option_defs['hpm']= array('Users','LBL_DEFAULT_HOMEPAGE','LBL_DEFAULT_HOMEPAGE','index.php?module=Users&action=EditHomepageSettings');
$admin_group_header[0][3]['Users']=array_merge($admin_group_header[0][3]['Users'],$admin_option_defs);
//**********************************************************************************
//   END Home Page Manager Admin Menu File
//**********************************************************************************
?>
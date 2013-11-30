<?php
	global $current_user;
	require_once('modules/Users/User.php');
	require_once('custom/modules/Users/homepage_manager.php');
	$dd=new defaultHomepage();
	$focus=new User();
	if($_REQUEST['stage']=='1') {
		if($_REQUEST['submit']=="Delete") {
			$id=$_REQUEST['dashboard_name'];
			$sql="UPDATE user_preferences SET deleted=1 WHERE assigned_user_id='$id' AND category='Home';";
			$result=$focus->db->query($sql,true);
		} elseif ($_REQUEST['submit']=="Save") {
			$user_id=$current_user->id;
			$sql="SELECT * FROM user_preferences WHERE assigned_user_id='$user_id' AND category='Home';";
			$result=$focus->db->query($sql,true);
			$hash=$focus->db->fetchByAssoc($result);
			$name='DD_'.$_REQUEST['new_dashboard_name'];
			$new_id=create_guid();
			$todays_date=date('Y-m-d h:i:s');
			$sql="INSERT INTO user_preferences (id,category,deleted,date_entered,date_modified,assigned_user_id,contents)
					VALUES('{$new_id}',
						   'Home',
						   0,
						   '{$todays_date}',
						   '{$todays_date}',
						   '{$name}',
						   '{$hash['contents']}');";
			$result=$focus->db->query($sql,true);
		}
	}
    echo "\n<p>\n";
    echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_MODULE_NAME'].": ".$mod_strings['LBL_HOMEPAGE_MANAGER_TITLE'], true);
    echo "\n</p>\n";
?>
<form name="default-dashboard" action="index.php" method="GET" enctype="multipart/form-data">
<INPUT type="hidden" name="module" value="Users">
<INPUT type="hidden" name="action" value="EditHomepageSettings">
<INPUT type="hidden" name="stage" value="1">
<table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view">
<tr>
    <th align="left" scope="row" colspan="4"><h4><slot><?php echo $mod_strings['LBL_SAVE_CONFIGRUATION'];?></slot></h4></th>
</tr>
<tr><td scope="row">
<i><?php echo $mod_strings['LBL_SAVE_TEXT']; ?></i>
</td>
</tr>
<tr>
<td>Name: <INPUT type="text" name="new_dashboard_name">&nbsp;<INPUT type="submit" name="submit" value="Save"></td>
</tr>
</table>
<br>
<table width="100%" border="0" cellpadding="5">
<table width="100%" border="0" cellspacing="1" cellpadding="0" class="edit view">
<tr>
    <th align="left" scope="row" colspan="4"><h4><slot>Delete a Dashboard Configuration</slot></h4></th>
</tr>
\<tr><td>
<i>This will delete the selected configuration and any user that is using it will be released from it.  Their current dashboard
will not change however.</i>
</td>
<tr>
<td>Name:
<select name="dashboard_name">
<?php
echo $dd->getCustomDashboardOptions("",true);
?>
</select>&nbsp;<INPUT type="submit" name="submit" value="Delete"></td>
</tr>
</table>
</form>
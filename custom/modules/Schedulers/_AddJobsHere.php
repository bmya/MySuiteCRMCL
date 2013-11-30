<?php
/*********************************************************************************
 * Email Archiver module scans IMAP email boxes and imports emails into SugarCRM developed by
 * Letrium Ltd.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY LETRIUM LTD., LETRIUM LTD. DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact Letrium Ltd. phone number +380 664 59 3633,
 * or at email address crm@letrium.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 ********************************************************************************/

$job_strings[60] = 'EmailArchiver';

function EmailArchiver() {
	global $db, $timedate;
	require_once('custom/modules/InboundEmail/ImportInboundEmail.php');
	$query = 'SELECT id, group_id
				FROM inbound_email 
				WHERE deleted=0 AND status = \'Active\' AND mailbox_type != \'bounce\' AND is_personal=1';
	$res = $db->query($query);
	while ($row = $db->fetchByAssoc($res))
	{
		$_REQUEST['user_id'] = (!empty($row['group_id'])) ? $row['group_id'] : NULL;
		$ie = new ImportInboundEmail();
		$ie->retrieve($row['id']);
		$newMsgs = array();
		$mailboxes = $ie->getMailboxes(true);
		foreach($mailboxes as $mailbox) {
			$ie->mailbox = $mailbox;
			$ie->connectMailserver();
			$since_date = '';
			$id = create_guid();
			$res_date = $db->query("SELECT last_email_date, id FROM import_emails WHERE inbound_email_id='{$ie->id}' AND folder='{$ie->mailbox}'");
			if ($row = $db->fetchByAssoc($res_date)) 
			{
				$since_date = $row['last_email_date'];
				$id = $row['id'];
			}
			
			else $db->query("INSERT INTO import_emails VALUES ('{$id}','{$ie->id}','{$ie->mailbox}','')");
			$newMsgs = $ie->getAllNewMessageIds($since_date);
			if(is_array($newMsgs)) {
				foreach($newMsgs as $k => $uid) {
					$cnt = 0;
					$cnt_res = $db->query("SELECT COUNT(*) cnt FROM import_emails_in_process WHERE import_email_id='{$id}' AND uid='{$uid}'");
					if ($cnt_row = $db->fetchByAssoc($cnt_res)) 
					{
						$cnt = $cnt_row['cnt'];
					}
					if ($cnt < 2)
					{
						$db->query("INSERT INTO import_emails_in_process VALUES (UUID(),'{$id}','{$uid}')");
						$msgNo = imap_msgNo($ie->conn, $uid);
						$ie->importOneEmail($msgNo, $uid);
						$ie->handleLinking($ie->email);
						$header = imap_headerinfo($ie->conn, $msgNo);
						if(!empty($header->date)) {
							$date = $timedate->asDbDate($timedate->fromString($header->date));
			   				$upd = "UPDATE import_emails SET last_email_date='".$date."' WHERE inbound_email_id='{$ie->id}' AND folder='{$ie->mailbox}'";
							$db->query($upd);
			   			 }
			   			 $db->query("DELETE FROM import_emails_in_process WHERE import_email_id='{$id}' AND uid='{$uid}'");
		      		}
				}
			}
			$ie->disconnectMailserver();
		}
		unset($ie);
	}
	return true;
}


?>

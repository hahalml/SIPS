<?php require("../../ini/dbconnect.php");

foreach ($_POST as $key => $value) { 
    ${$key} = $value;
}
foreach ($_GET as $key => $value) {
    ${$key} = $value;
}

function FieldsListBuilder($CampaignID, $AllFields, $FieldID, $FieldDisplayName, $FieldReadOnly, $Flag, $link)
{
	
	if($Flag == "ALL" || $Flag == "DIALOG")
	{
		$query = mysql_query("SELECT Name, Display_name, readonly, active, field_order FROM vicidial_list_ref WHERE campaign_id='$CampaignID' ORDER BY field_order DESC") or die(mysql_error());
		if(mysql_num_rows($query))
		{
			
			
			while($row = mysql_fetch_assoc($query))
			{
				if($row['active'])
				{
					$js['name'][] =  $row['Name'];
					$js['displayname'][] = $row['Display_name'];
					$js['readonly'][] = $row['readonly'];
					$js['order'][] = $row['field_order'];
				
				}
			}
		}
		else
		{
			foreach($AllFields as $index => $value)
			{
				switch($value)
				{
					case "FIRST_NAME": $active = 1; $displayname = "Nome"; $order = 1; break;
					case "PHONE_NUMBER": $active = 1; $displayname = "Telefone"; $order = 2;  break;
					case "ALT_PHONE": $active = 1; $displayname = "Telefone Alternativo"; $order = 3; break;
					case "ADDRESS3": $active = 1; $displayname = "Telemóvel"; $order = 4; break;
					case "ADDRESS1": $active = 1; $displayname = "Morada"; $order = 5; break;
					case "POSTAL_CODE": $active = 1; $displayname = "Código Postal"; $order = 6; break;
					case "EMAIL": $active = 1; $displayname = "E-mail"; $order = 7; break;
					case "COMMENTS": $active = 1; $displayname = "Comentários"; $order = 8; break;
					default : $active = 0; $displayname = $value; $order = 0;
				}
				
				if($active)
				{
					$js['name'][] = $value;
					$js['displayname'][] = $displayname;
					$js['readonly'][] = 0;
					$js['order'][] = $order;
				}
				
				mysql_query("INSERT INTO vicidial_list_ref (Name, Display_name, readonly, active, campaign_id, field_order) VALUES ('$value', '$displayname', '0', '$active', '$CampaignID', '$order')");
				mysql_query("UPDATE sips_campaign_stats SET fields = 8 WHERE campaign_id='$CampaignID'") or die(mysql_error());
			}	
		}

	}
	else
	{
		//$js["debug"] = "SELECT Display_name FROM vicidial_list_ref WHERE Display_name COLLATE utf8_bin = '$FieldDisplayName' AND campaign_id = '$CampaignID'";
		
		$query = mysql_query("SELECT Display_name FROM vicidial_list_ref WHERE Display_name COLLATE utf8_bin = '$FieldDisplayName' AND campaign_id = '$CampaignID'", $link) or die(mysql_error());
		if(mysql_num_rows($query) == 0)
		{
			mysql_query("UPDATE vicidial_list_ref SET field_order = field_order+1 WHERE campaign_id='$CampaignID' AND field_order > 0") or die(mysql_error());
			mysql_query("UPDATE vicidial_list_ref SET Display_name='$FieldDisplayName', readonly='$FieldReadOnly', active=1, campaign_id='$CampaignID', field_order=1 WHERE campaign_ID='$CampaignID' AND Name='$FieldID'") or die(mysql_error());
			mysql_query("UPDATE sips_campaign_stats SET fields = fields + 1 WHERE campaign_id='$CampaignID'") or die(mysql_query());
			$js['name'][] = $FieldID;
			$js['displayname'][] = $FieldDisplayName;
			$js['readonly'][] = $FieldReadOnly;
			$js['order'][] = 1;
				
		}
		else 
		{
			$js['duplicate'] = "error";
		}
		

	
	
	}
	
	
	
	
	

echo json_encode($js);  


}

function FieldsReadOnlySwitch($CampaignID, $FieldID, $ReadOnly, $link)
{
	mysql_query("UPDATE vicidial_list_ref SET readonly='$ReadOnly' WHERE campaign_id='$CampaignID' AND Name='$FieldID'") or die(mysql_error());
}

function ReOrderFields($CampaignID, $SortedFields, $link)
{
	foreach($SortedFields as $index => $value)
	{
		mysql_query("UPDATE vicidial_list_ref SET field_order='".($index+1)."' WHERE Name='$value' AND campaign_id='$CampaignID'") or die(mysql_error());
	}
}

function RemoveField($CampaignID, $FieldID, $link)
{
	$result = mysql_fetch_row(mysql_query("SELECT field_order FROM vicidial_list_ref WHERE campaign_id='$CampaignID' AND Name ='$FieldID'")) or die(mysql_error());
	mysql_query("UPDATE vicidial_list_ref SET field_order = field_order - 1 WHERE field_order > $result[0] AND campaign_id='$CampaignID'") or die(mysql_error());
	mysql_query("UPDATE vicidial_list_ref SET Display_name = Name, readonly=0, active=0, field_order=0 WHERE campaign_id='$CampaignID' AND Name='$FieldID'") or die(mysql_error());
	mysql_query("UPDATE sips_campaign_stats SET fields = fields - 1 WHERE campaign_id='$CampaignID'") or die(mysql_query());
}

function DialogFieldsEditOnSave($CampaignID, $FieldID, $FieldName, $link)
{
	$js["debug"] = "SELECT Display_name FROM vicidial_list_ref WHERE Display_name COLLATE utf8_bin = '$FieldName' AND campaign_id = '$CampaignID'";
	
	$query = mysql_query("SELECT Display_name FROM vicidial_list_ref WHERE Display_name COLLATE utf8_bin = '$FieldName' AND campaign_id = '$CampaignID'", $link) or die(mysql_error());
	if(mysql_num_rows($query)==0)
	{
		mysql_query("UPDATE vicidial_list_ref SET Display_name = '$FieldName' WHERE campaign_id='$CampaignID' AND Name = '$FieldID'") or die(mysql_error());
		$js['flag'] = true;
	}
	else
	{
		$js['flag'] = false;
	}
echo json_encode($js);
}

function DialogFieldsApplyToAllCampaignsOnSave($CampaignID, $AllowedCampaigns, $link)
{
	$result = mysql_query("SELECT Name, Display_name, readonly, active, field_order FROM vicidial_list_ref WHERE campaign_id='$CampaignID'") or die(mysql_error());
	
	$count = 0;
	while($row = mysql_fetch_assoc($result))
	{
		if($row['active'] == 1){$count++;}
		mysql_query("UPDATE vicidial_list_ref SET Display_name = '$row[Display_name]', readonly = '$row[readonly]', active = '$row[active]', field_order = '$row[field_order]' WHERE campaign_id IN ('".implode("','", $AllowedCampaigns)."') AND Name = '$row[Name]'") or die(mysql_error());
	}
	mysql_query("UPDATE sips_campaign_stats SET fields = $count") or die(mysql_query());
}

function DialogFieldsCopyOnOpen($CampaignID, $ModAllowedCampaigns, $link)
{
	$result = mysql_query("SELECT campaign_id, campaign_name FROM vicidial_campaigns WHERE campaign_id IN ('".implode("','", $ModAllowedCampaigns)."') ORDER BY campaign_name") or die(mysql_error());
	
	while($row = mysql_fetch_assoc($result))
	{
		$js['c_id'][] = $row['campaign_id'];
		$js['c_name'][] = $row['campaign_name'];
	}
	echo json_encode($js);
}

function BtnCopyFields($CampaignID, $CopyCampaignID, $link)
{
	$result = mysql_query("SELECT Name, Display_name, readonly, active, field_order FROM vicidial_list_ref WHERE campaign_id='$CopyCampaignID'") or die(mysql_error());
	$count = 0;
	while($row = mysql_fetch_assoc($result))
	{
		if($row['active'] == 1) $count++;
		mysql_query("UPDATE vicidial_list_ref SET Display_name = '$row[Display_name]', readonly = '$row[readonly]', active = '$row[active]', field_order = '$row[field_order]' WHERE campaign_id = '$CampaignID' AND Name = '$row[Name]'") or die(mysql_error());
	}
	mysql_query("UPDATE sips_campaign_stats SET fields = $count WHERE campaign_id='$CampaignID'") or die(mysql_query());
}


switch($action)
{
	case "FieldsListBuilder": FieldsListBuilder($CampaignID, $AllFields, $FieldID, $FieldDisplayName, $FieldReadOnly, $Flag, $link); break;
	case "FieldsReadOnlySwitch": FieldsReadOnlySwitch($CampaignID, $FieldID, $ReadOnly, $link); break;
	case "ReOrderFields": ReOrderFields($CampaignID, $SortedFields, $link); break;
	case "RemoveField": RemoveField($CampaignID, $FieldID, $link); break;
	case "DialogFieldsEditOnSave": DialogFieldsEditOnSave($CampaignID, $FieldID, $FieldName, $link); break;
	case "DialogFieldsApplyToAllCampaignsOnSave": DialogFieldsApplyToAllCampaignsOnSave($CampaignID, $AllowedCampaigns, $link); break;
	case "DialogFieldsCopyOnOpen": DialogFieldsCopyOnOpen($CampaignID, $ModAllowedCampaigns, $link); break;
	case "BtnCopyFields": BtnCopyFields($CampaignID, $CopyCampaignID, $link); break;	
}





































/* CAMPOS DINAMICOS */
if($action== "submit_dfields")
{
    mysql_query("DELETE FROM vicidial_list_ref WHERE campaign_id='$sent_campaign_id'") or die(mysql_error());
    
    for($i=0; $i<count($sent_sortedIDs); $i++)
    {
    	mysql_query("INSERT INTO vicidial_list_ref (Name, Display_name, readonly, active, campaign_id, field_order) VALUES ('$sent_sortedIDs[$i]', '$sent_sortedLabels[$i]', '$sent_sortedReadOnly[$i]', '1', '$sent_campaign_id', '$sent_sortedOrder[$i]')") or die(mysql_error());    
    }
    for($i=0; $i<count($sent_fillers); $i++)
    {
        $sent_fillers[$i] = strtoupper($sent_fillers[$i]);
        mysql_query("INSERT INTO vicidial_list_ref (Name, active, campaign_id) VALUES ('$sent_fillers[$i]', '0', '$sent_campaign_id')") or die(mysql_error());  

    }
    
}

if($action == "copy_dfields")
{
	$query = "SELECT Name, Display_name, readonly, active FROM vicidial_list_ref WHERE campaign_id='$sent_campaign_id_copy' and active=1 ORDER by field_order";
	$query = mysql_query($query, $link);
	while($row = mysql_fetch_assoc($query))
	{
		$js['name'][] = $row['Name'];
		$js['display_name'][] = $row['Display_name'];
		$js['readonly'][] = $row['readonly'];
		$js['active'][] = $row['active'];
	}
	
	echo json_encode($js);
}
/* BASES DE DADOS */
?>
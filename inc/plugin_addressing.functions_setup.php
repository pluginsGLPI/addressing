<?php
/*
  ----------------------------------------------------------------------
  GLPI - Gestionnaire Libre de Parc Informatique
  Copyright (C) 2003-2008 by the INDEPNET Development Team.
  
  http://indepnet.net/   http://glpi-project.org/
  ----------------------------------------------------------------------
  
  LICENSE
  
  This file is part of GLPI.
  
  GLPI is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.
  
  GLPI is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
  
  You should have received a copy of the GNU General Public License
  along with GLPI; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
  ------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: GRISARD Jean Marc & CAILLAUD Xavier
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
	}
	
function plugin_addressing_execute($DB_file){
	global $DB;
	
	$DBf_handle = fopen($DB_file, "rt");
	$sql_query = fread($DBf_handle, filesize($DB_file));
	fclose($DBf_handle);
	foreach ( explode(";\n", "$sql_query") as $sql_line) {
		if (get_magic_quotes_runtime()) $sql_line=stripslashes_deep($sql_line);
		$DB->query($sql_line);
	}
}

function plugin_addressing_Install() {

	global $DB;
	
	plugin_addressing_execute(GLPI_ROOT ."/plugins/addressing/inc/plugin_addressing-1.6-empty.sql");
}

function plugin_addressing_updatev14(){

	plugin_addressing_execute(GLPI_ROOT ."/plugins/addressing/inc/plugin_addressing-1.4-update.sql");
}

function plugin_addressing_updatev15(){

	plugin_addressing_execute(GLPI_ROOT ."/plugins/addressing/inc/plugin_addressing-1.5-update.sql");
}

function plugin_addressing_updatev16(){

	plugin_addressing_execute(GLPI_ROOT ."/plugins/addressing/inc/plugin_addressing-1.6-update.sql");
}

function plugin_addressing_uninstall() {
	
	global $DB;
	
	$query = "DROP TABLE `glpi_plugin_addressing`;";	
	$DB->query($query) or die($DB->error());
			
	$query = "DROP TABLE `glpi_plugin_addressing_display`;";	
	$DB->query($query) or die($DB->error());
		
	$query = "DROP TABLE `glpi_plugin_addressing_profiles`;";	
	$DB->query($query) or die($DB->error());
	
	$query="DELETE FROM glpi_display WHERE type='".PLUGIN_ADDRESSING_TYPE."';";
	$DB->query($query) or die($DB->error());
	
	$query="DELETE FROM glpi_bookmark WHERE device_type='".PLUGIN_ADDRESSING_TYPE."';";
	$DB->query($query) or die($DB->error());	
}

function plugin_addressing_ping($system,$ip)
{
	$list ='';
    switch ($system){
    
    case 0:
    // linux ping
        exec("ping -c 1 -w 1 ".$ip, $list);    
        $nb=count($list);
        if (isset($nb)){
            for($i=0;$i<$nb;$i++)
            {
                if(strpos($list[$i],"ttl=")>0) return true;
            }
        }
    break;
    
    case 1:
    //windows
        exec("ping.exe -n 1 -w 1 -i 1 ".$ip, $list);
        $nb=count($list); 
        if (isset($nb)){
            for($i=0;$i<$nb;$i++)
            {
                if(strpos($list[$i],"TTL")>0) return true;
            }
        }
    break;
    
	case 2:
    //linux fping
    exec("fping -r1 -c1 -t100 ".$ip, $list);    
        $nb=count($list);
        if (isset($nb)){
            for($i=0;$i<$nb;$i++)
            {
                if(strpos($list[$i],"bytes")>0) return true;
            }
        }
    break;    
    }
}

?>
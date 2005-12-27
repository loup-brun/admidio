<?php
/******************************************************************************
 * Neue User auflisten
 *
 * Copyright    : (c) 2004 - 2005 The Admidio Team
 * Homepage     : http://www.admidio.org
 * Module-Owner : Markus Fassbender
 *
 ******************************************************************************
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *****************************************************************************/
 
require("../../system/common.php");
require("../../system/session_check_login.php");

// nur Webmaster d�rfen User best�tigen, ansonsten Seite verlassen
if(!hasRole("Webmaster"))
{
   $location = "location: $g_root_path/adm_program/system/err_msg.php?err_code=norights";
   header($location);
   exit();
}

// Neue Mitglieder der Gruppierung selektieren
$sql    = "SELECT * FROM ". TBL_NEW_USER. " ".
          " WHERE anu_org_shortname = '$g_organization' ".
          " ORDER BY anu_name, anu_vorname ";
$result = mysql_query($sql, $g_adm_con);
db_error($result, 1);
$member_found = mysql_num_rows($result);

if ($member_found == 0)
{
   $location = "location: $g_root_path/adm_program/system/err_msg.php?err_code=nomembers&err_head=Anmeldungen&url=home";
   header($location);
   exit();
}

echo "
<!-- (c) 2004 - 2005 The Admidio Team - http://www.admidio.org - Version: ". getVersion(). " -->\n
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html>
<head>
   <title>$g_current_organization->longname - Neue Anmeldungen</title>
   <link rel=\"stylesheet\" type=\"text/css\" href=\"$g_root_path/adm_config/main.css\">

   <!--[if gte IE 5.5000]>
   <script language=\"JavaScript\" src=\"$g_root_path/adm_program/system/correct_png.js\"></script>
   <![endif]-->";

   require("../../../adm_config/header.php");
echo "</head>";

require("../../../adm_config/body_top.php");
   echo "<div align=\"center\">
   <h1>Neue Anmeldungen</h1>

   <table class=\"tableList\" cellpadding=\"2\" cellspacing=\"0\">
      <tr>
         <th class=\"tableHeader\" style=\"text-align: left;\">&nbsp;Nachname</th>
         <th class=\"tableHeader\" style=\"text-align: left;\">&nbsp;Vorname</th>
         <th class=\"tableHeader\" style=\"text-align: left;\">&nbsp;Benutzername</th>
         <th class=\"tableHeader\" style=\"text-align: left;\">&nbsp;E-Mail</th>
         <th class=\"tableHeader\" style=\"text-align: center;\">&nbsp;Funktionen</th>
      </tr>";

      while($row = mysql_fetch_object($result))
      {
         echo "<tr class=\"listMouseOut\" onmouseover=\"this.className='listMouseOver'\" onmouseout=\"this.className='listMouseOut'\">
                  <td style=\"text-align: left;\">&nbsp;$row->anu_name</td>
                  <td style=\"text-align: left;\">&nbsp;$row->anu_vorname</td>
                  <td style=\"text-align: left;\">&nbsp;$row->anu_login</td>
                  <td style=\"text-align: left;\">&nbsp;<a href=\"mailto:$row->anu_mail\">$row->anu_mail</a></td>
                  <td style=\"text-align: center;\">
                     <a href=\"new_user_function.php?mode=3&amp;anu_id=$row->anu_id\">
                        <img src=\"$g_root_path/adm_program/images/properties.png\" border=\"0\" alt=\"Anmeldung zuordnen\" title=\"Anmeldung zuordnen\"></a>&nbsp;&nbsp;
                     <a href=\"$g_root_path/adm_program/system/err_msg.php?err_code=delete_new_user&amp;err_text=$row->anu_vorname $row->anu_name&amp;err_head=L&ouml;schen&amp;button=2&amp;url=". urlencode("$g_root_path/adm_program/administration/new_user/new_user_function.php?anu_id=$row->anu_id&amp;mode=4"). "\">
                        <img src=\"$g_root_path/adm_program/images/delete.png\" border=\"0\" alt=\"Anmeldung l&ouml;schen\" title=\"Anmeldung l&ouml;schen\"></a>
                  </td>
               </tr>";
      }

   echo "</table>
   </div>";

   require("../../../adm_config/body_bottom.php");
echo "</body>
</html>";
?>
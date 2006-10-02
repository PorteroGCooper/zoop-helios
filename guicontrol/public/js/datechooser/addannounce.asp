<%@LANGUAGE="VBSCRIPT"%>
<!--#include file="scripts/logout_script.vbs" -->
<!--#include file="../Connections/BAPDatabase.asp" -->
<%
' *** Edit Operations: declare variables

MM_editAction = CStr(Request("URL"))
If (Request.QueryString <> "") Then
  MM_editAction = MM_editAction & "?" & Request.QueryString
End If

' boolean to abort record edit
MM_abortEdit = false

' query string to execute
MM_editQuery = ""
%>
<%
' *** Insert Record: set variables

If (CStr(Request("MM_insert")) <> "") Then

  MM_editConnection = MM_BAPDatabase_STRING
  MM_editTable = "Announcements"
  MM_editRedirectUrl = "announcethanks.asp"
  MM_fieldsStr  = "StartDate|value|EndDate|value|AnnouncementType|value|Announcement|value|WebPage|value|hiddenField|value"
  MM_columnsStr = "StartDate|',none,NULL|EndDate|',none,NULL|AnnouncementType|',none,''|Announcement|',none,''|WebPage|',none,''|UserID|',none,''"

  ' create the MM_fields and MM_columns arrays
  MM_fields = Split(MM_fieldsStr, "|")
  MM_columns = Split(MM_columnsStr, "|")
  
  ' set the form values
  For i = LBound(MM_fields) To UBound(MM_fields) Step 2
    MM_fields(i+1) = CStr(Request.Form(MM_fields(i)))
  Next

  ' append the query string to the redirect URL
  If (MM_editRedirectUrl <> "" And Request.QueryString <> "") Then
    If (InStr(1, MM_editRedirectUrl, "?", vbTextCompare) = 0 And Request.QueryString <> "") Then
      MM_editRedirectUrl = MM_editRedirectUrl & "?" & Request.QueryString
    Else
      MM_editRedirectUrl = MM_editRedirectUrl & "&" & Request.QueryString
    End If
  End If

End If
%>
<%
' *** Insert Record: construct a sql insert statement and execute it

If (CStr(Request("MM_insert")) <> "") Then

  ' create the sql insert statement
  MM_tableValues = ""
  MM_dbValues = ""
  For i = LBound(MM_fields) To UBound(MM_fields) Step 2
    FormVal = MM_fields(i+1)
    MM_typeArray = Split(MM_columns(i+1),",")
    Delim = MM_typeArray(0)
    If (Delim = "none") Then Delim = ""
    AltVal = MM_typeArray(1)
    If (AltVal = "none") Then AltVal = ""
    EmptyVal = MM_typeArray(2)
    If (EmptyVal = "none") Then EmptyVal = ""
    If (FormVal = "") Then
      FormVal = EmptyVal
    Else
      If (AltVal <> "") Then
        FormVal = AltVal
      ElseIf (Delim = "'") Then  ' escape quotes
        FormVal = "'" & Replace(FormVal,"'","''") & "'"
      Else
        FormVal = Delim + FormVal + Delim
      End If
    End If
    If (i <> LBound(MM_fields)) Then
      MM_tableValues = MM_tableValues & ","
      MM_dbValues = MM_dbValues & ","
    End if
    MM_tableValues = MM_tableValues & MM_columns(i)
    MM_dbValues = MM_dbValues & FormVal
  Next
  MM_editQuery = "insert into " & MM_editTable & " (" & MM_tableValues & ") values (" & MM_dbValues & ")"

  If (Not MM_abortEdit) Then
    ' execute the insert
    Set MM_editCmd = Server.CreateObject("ADODB.Command")
    MM_editCmd.ActiveConnection = MM_editConnection
    MM_editCmd.CommandText = MM_editQuery
    MM_editCmd.Execute
    MM_editCmd.ActiveConnection.Close

    If (MM_editRedirectUrl <> "") Then
      Response.Redirect(MM_editRedirectUrl)
    End If
  End If

End If
%>
<html>
<TITLE>BAP - Gamma Alpha Home Page</TITLE>
<link rel="stylesheet" href="../css/datechooser.css" type="text/css">
<link rel="stylesheet" href="css/bap_css.css" type="text/css">
<script language="Javascript" type="text/javascript"
    src="../scripts/datechooser.js">;
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_validateForm() { //v4.0
  var i,p,q,nm,test,num,min,max,errors='',args=MM_validateForm.arguments;
  for (i=0; i<(args.length-2); i+=3) { test=args[i+2]; val=MM_findObj(args[i]);
    if (val) { nm=val.name; if ((val=val.value)!="") {
      if (test.indexOf('isEmail')!=-1) { p=val.indexOf('@');
        if (p<1 || p==(val.length-1)) errors+='- '+nm+' must contain an e-mail address.\n';
      } else if (test!='R') { num = parseFloat(val);
        if (isNaN(val)) errors+='- '+nm+' must contain a number.\n';
        if (test.indexOf('inRange') != -1) { p=test.indexOf(':');
          min=test.substring(8,p); max=test.substring(p+1);
          if (num<min || max<num) errors+='- '+nm+' must contain a number between '+min+' and '+max+'.\n';
    } } } else if (test.charAt(0) == 'R') errors += '- '+nm+' is required.\n'; }
  } if (errors) alert('The following error(s) occurred:\n'+errors);
  document.MM_returnValue = (errors == '');
}
//-->
</script>
</head>
<body onLoad="hide_Calendar();MM_preloadImages('../images/arrowRpress.jpg')">
<table width="400" align="center">
  <tr> 
    <td valign="top">
		<div id="Layer1" style="position:absolute; width:206px; height:171px; z-index:1; left: 345px; top: 120px; visibility: hidden;">
		<!--#include file="scripts/cal_div.htm" -->
		</div>
        <div align="center" class="headline">Add an Announcement</div>
	</td>
	</tr>
	<tr>
		<td class="footer-top">&nbsp;</td>
	</tr>
	<td>
        <form method="POST" action="<%=MM_editAction%>" name="form1">
            <div align="center">
                <table width="390" border="0" cellspacing="0">
                      <tr valign="baseline" > 
                        <td width="103" align="right" valign="middle" nowrap class="table-row-header"><div align="right">Start Date:</div></td>
                        <td width="133"> 
                          <input id="StartDate" type="text" name="StartDate" value="" size="12" onBlur="MM_validateForm('StartDate','','R','EndDate','','R');return document.MM_returnValue">
                        <span id="Start" onclick="toggle_Calendar(id)"><img src="../images/newNav/cal.gif" 
                    alt="Show Calendar" width="20" height="20">
                            </span> 
			            </td>
                        <td width="148" rowspan="2" valign="top" class="table-text">Announcement shows 
            only from start to end date</td>
                        </tr>
                      <tr valign="baseline" > 
                        <td valign="middle" class="table-row-header"><div align="right">End Date:</div></td>
                        <td> 
                          <input id="EndDate" type="text" name="EndDate" value="" size="12"><span id="End" onclick="toggle_Calendar(id)">
					            <img src="../images/newNav/cal.gif" alt="Show Calendar" width="20" height="20">
                            </span> 
                        </td>
                        </tr>
                      <tr valign="baseline" > 
                        <td align="right" valign="middle" nowrap class="table-row-header"><div align="right">Type:</div></td>
                        <td colspan="2"> 
                          <select name="AnnouncementType">
                            <option>Select</option>
                            <option value="Event">Upcoming Event</option>
                            <option value="Info">General Information</option>
                            <option value="Misc">Misc. Information</option>
                          </select>
                        </td>
                        </tr>
                      <tr valign="baseline" >
                          <td align="right" valign="middle" nowrap class="table-text">&nbsp;</td>
                          <td colspan="2">&nbsp;</td>
                      </tr>
                      <tr valign="baseline" > 
                        <td align="right" valign="top" nowrap class="table-text">
					        <div align="right"><span class="table-row-header">Announcement:</span><br>
					            (Limit to <br>240 Characters)</div></td>
                        <td colspan="2"> 
                            <textarea name="Announcement" cols="27" rows="6" wrap="VIRTUAL"></textarea></td>
                        </tr>
                      <tr valign="baseline">
                          <td align="right" valign="middle" nowrap class="table-text">&nbsp;</td>
                          <td colspan="2">&nbsp;</td>
                      </tr>
                      <tr valign="baseline"> 
                        <td align="right" valign="top" nowrap class="table-text"><div align="right" class="table-row-header">Web Page:</div></td>
                        <td colspan="2"> 
                          <input type="text" name="WebPage" value="" size="35"><br>
                          <span class="table-text">For external links use full address (http://www...)<br>
						        For local use no prefix (calendar.asp)</span>                  
				          <input type="hidden" name="hiddenField" value="<%= Session("MM_Username") %>">                            </td>
                        </tr>
                      <tr valign="baseline"> 
                        <td nowrap align="right" colspan="3"> 
                          <div align="center"> 
                            <input type="submit" value="Add Announcement" name="Add">
                          </div>
                        </td>
                      </tr>
                </table>
                <input type="hidden" name="MM_insert" value="true">
            </div>
        </form></td>
	</tr>
</table>
	</table>
</div>
</body>
</html>
// +----------------------------------------------------------------------+
// | Homework 3 - Calendar Date Picker                                    |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005 Ben Candland                                      |
// +----------------------------------------------------------------------+
// | This source code was written by the author for use in ISYS 542       |
// | at Brigham Young University, except were noted.  Feel free to use    |
// | any code as needed.  Thanks!                                         |
// +----------------------------------------------------------------------+
// | Authors: Original Author  Ben Candland <benjammm@hotmail.com>        |
// |          Contributions    Isys 542, S.W. Liddle                      |
// |	   			 Steve Francia <sfrancia@supernerd.com	  |
// +----------------------------------------------------------------------+

// Functions (Sorted by Name)
function change_Month(Name)        // Written by Ben Candland
{
    var month = document.getElementById('cal_Month');
    var year = document.getElementById('cal_Year');
    if (Name=='next') {
        month.title = parseInt(month.title) + 1;
        if (month.title > 11) {
            year.innerHTML = parseInt(year.innerHTML) + 1;
            month.title = 0;
        }
    } else {
        month.title = parseInt(month.title) - 1;
        if (month.title < 0) {
            year.innerHTML = parseInt(year.innerHTML) - 1;
            month.title = 11;
        }
    }
    get_Days();
}                                  // end of change_Month


function change_Year(Name)         // Written by Ben Candland
{
    var month = document.getElementById('cal_Month');
    var year = document.getElementById('cal_Year');
    if (Name=='next') {
        year.innerHTML = parseInt(year.innerHTML) + 1;
    } else {
        year.innerHTML = parseInt(year.innerHTML) - 1;
    }
    get_Days();
}                                  // end of change_Year


function get_Cell(row, col)        // Function model from Class
{
    return (document.getElementById(row + '.' + col));
}                                  // end of get_Cell


function get_Days()                // Adapted from Class by Ben Candland
{
    var days;
    var calYear = document.getElementById('cal_Year');
    var year = calYear.innerHTML;
    if (year == '') {
        year = today.getFullYear();
        calYear.innerHTML = year;
    }
    var calMonth = document.getElementById("cal_Month");
    var month = calMonth.title;
    if (month == "") {
        month = today.getMonth();
        calMonth.title = month;
    }
    calMonth.innerHTML = months[month];
    calMonth.title = month;
    if (month==0 || month==2 || month==4 || month==6 || month==7 || month==9 || month==11) {
        days=31;
    }
    else if (month==3 || month==5 || month==8 || month==10) {
        days=30;
    }
    else if (month==1)  {
        if (is_Leap_Year(year)) {
            days=29;
        } else {
            days=28;
        }
    }
    var start_Date = new Date(year, month, 1);
    var i;
    var col = 0;
    var row = 0;
    var cell1 = get_Cell(row, col);
    for (i = 1; i <= 42; i++) {
        cell1 = get_Cell(row, col);
        cell1.innerHTML = "";
        ++col;
        if (col >= 7) {
            ++row;
            col = 0;
        }
    }
    col = start_Date.getDay();
    row = 0;
    for (i = 1; i <= days; i++) {
        cell1 = get_Cell(row, col);
		cell1.style.cursor = 'pointer';
        cell1.innerHTML = '<span id="' + i + '" onClick=put_Dates(innerHTML)>' + i + '</span>';
        ++col;
        if (col >= 7) {
            ++row;
            col = 0;
        }
    }
    if ((calYear.innerHTML == today.getFullYear()) && (calMonth.title == today.getMonth())) {
        var today_ref = document.getElementById(today.getDate());
        today_ref.style.color = "#E4C43F";
    }
}                                   // end of get_Days()


function go_Today()                // Function written by Ben Candland
{
    var month = document.getElementById("cal_Month");
    var year = document.getElementById("cal_Year");
    month.title = "";
    year.innerHTML = "";
    get_Days();
}

function hide_Calendar()           // Function model from Class
{
    var my_Div = document.getElementById("calendarDiv");
    my_Div.style.visibility = "hidden";
	my_Div.style.display = 'none';
}                                 // end of hide_Calendar()



function is_Leap_Year(year)      // Adapted from 1st Class lesson
{
    if (((year % 4)==0) && ((year % 100)!= 0) || ((year % 400)==0)) {
        return (true);
    } else {
        return (false);
    }
}


function put_Dates(day)          // Written by Ben Candland

{
    // puts the date clicked on into the textbox
    hide_Calendar();
    var month = document.getElementById("cal_Month").innerHTML;
    var year = document.getElementById("cal_Year").innerHTML;
//    month = month.substr(0,3);
    // var chosen_Date =  day + " " + month + " " + year;
    month = getMonthNum(month);
    if (day < 10) day = '0' + day;
    var chosen_Date =  year + "-" + month + "-" + day ; // ADDED BY SPF 2005/07/21 to output universal format
    var sender = document.getElementById("cal_sender").innerHTML;
    var date_Field = document.getElementById(sender);
    date_Field.value = chosen_Date;
}              //  end of put_Dates()


function show_Calendar(sender)         // Written by Ben Candland

{

    var my_Div = document.getElementById("calendarDiv");
    var cal_sender = document.getElementById("cal_sender");
    cal_sender.innerHTML = sender;
    get_Days();
	var newX = findPosX(document.getElementById(sender));
	var newY = findPosY(document.getElementById(sender)) + 30;
	my_Div.style.top = newY + 'px';
	my_Div.style.left = newX + 'px';	
    my_Div.style.visibility = "visible";
	my_Div.style.display = '';
	
	// alert(findPosX(document.getElementById(sender)));
	document.getElementById("calendarToday").innerHTML = "<span style=\"border:0;cursor:pointer;\" onClick=\"go_Today('"
	+ sender +"')\"><img name=\"today_star\" src=\"index.php/zoopfile/guicontrol/js/datechooser/today_star.gif\" width=\"15\" height=\"15\" border=\"0\">Go to Today </span>";

}                             // end of show_Calendar()


function toggle_Calendar(sender)       // Function model from Class
{
    var my_Div = document.getElementById("calendarDiv");
	var cal_sender = document.getElementById("cal_sender");
    if (my_Div.style.visibility == "hidden") {
        show_Calendar(sender);
		cal_sender.innerHTML = sender;
    } else {
        hide_Calendar();
		//cal_sender.innerHTML == "";
    }
    return (false);
}                                 // end of toggle_Calendar()

function findPosX(obj)
{
	var curleft = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	}
	else if (obj.x)
		curleft += obj.x;
	return curleft;
}

function findPosY(obj)
{
	var curtop = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	}
	else if (obj.y)
		curtop += obj.y;
	return curtop;
}

function getMonthNum(inMonth) // ADDED BY SPF 2005/07/21
{
	if (inMonth == "January")
		return '01';
	if (inMonth == "February")
		return '02';
	if (inMonth == "March")
		return '03';
	if (inMonth == "April")
		return '04';
	if (inMonth == "May")
		return '05';
	if (inMonth == "June")
		return '06';
	if (inMonth == "July")
		return '07';
	if (inMonth == "August")
		return '08';
	if (inMonth == "September")
		return '09';
	if (inMonth == "October")
		return '10';
	if (inMonth == "November")
		return '11';
	if (inMonth == "December")
		return '12';
}

// +------------------------------------------------------------+
// |                        Global Variables                    |
// +------------------------------------------------------------+
var today = new Date();
var months = ["January", "February", "March", "April", "May", "June",
              "July", "August", "September", "October", "November",
              "December"];

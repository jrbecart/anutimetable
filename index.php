<?php
header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Pragma: no-cache');

$term = @$_GET["term"] ;


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>uOttawa Science timetable planner</title>
  <meta charset="utf-8">
  <meta name="description" content="Intuitive timetable builder for the Australian National University.">
  <script src="js/underscore.js"></script>
  <script src="js/jquery.js"></script>
  <script src="js/typeahead.bundle.min.js"></script>
  <script src="js/download.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/importDate.js"></script>
  <?php echo($term == "winter" ? '<script src="js/timetableWinter.js" defer></script>' : '<script src="js/timetableFall.js" defer></script>') ?>
  <script src="js/timetable_analyser.js"></script>
  <script src="js/html2canvas.min.js"></script>
  <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
<div class="container_">
  <div class="row noprint">
    <div class="col-sm-12 col-md-12" id="chosenCoursesParent">
      
        <div class="">
        <h1 class="title">uOttawa Science Timetable Planner (<a href="editor/<?php echo($term == "winter" ? 'indexWinter.php' : 'indexFall.php') ?>" target="_new">edit config</a>)   <strong><?php echo($term == "winter" ? 'WINTER' : 'FALL') ?></strong> </h1>        <h2 class="title"> <?php echo($term == "winter" ? '<a href="index.php?term=fall" target="_blank">display fall</a>' : '<a href="index.php?term=winter" target="_blank">display winter</a>') ?></h2>
        </div>
        <p class="course-list-date">Course list updated on <span id="jsonUpdatedTime"></span><br />
        
          <span class="special-note"></span></p>
        <p>
          <i>Courses chosen: 
          <span id="chosenCourses" style="display: inline;">Loading data from data/timetable.json...</span> 
          <span id="courses" style="display: inline;"></span></i>
        </p>
        <p><i>Courses not found: <span id="errorCourses" style="display: inline;color: red;"></span> </i></p>
        
      
    </div>

    <div class="col-xs-12">
      <div class="well form-inline">
        <input type="text" id="course-name" class="form-control" style="width:325px" placeholder="Enter a course code here (for example BIO2133)" autofocus>
        <div class="btn-group">
          <button id="add-course" class="btn btn-default">Add</button>
          <button id="clear-courses" class="btn btn-default">Clear</button>
        </div>
        
        <select id="course-ddl" class="form-control" style="width:325px" placeholder="Choose a course"> </select>
        
        <?php echo($term == "winter" ? '<button id="loadWinter" class="btn btn-primary ">Upload new winter data .xlsx</button>' : ' <button id="loadFall" class="btn btn-primary ">Upload new fall data .xlsx</button>') ?>
        
        <button id="changeView" class="btn btn-default ">Large / Normal view</button>
        <div id="configName"></div>
        <div class="btn-group pull-right">
            <button id="screenshot" class="btn btn-info">Export .png</button>
            <button id="download" class="btn btn-info pull-right">Export .ics</button>
        </div>
        <div class="hiddenfile">
          <input type="file" id="fileFall">
          <input type="file" id="fileWinter">
          <input name="upload" type="file" id="fileinput"/>
        </div>
        
		
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-xs-12">
      <div id="loader" style="display:none;"></div>
      <div id="cal-container"></div>
	</div>
  </div>
</div>

<script type="text/template" id="cal-header">BEGIN:VCALENDAR
PRODID:-//Google Inc//Google Calendar 70.9054//EN
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-WR-CALNAME:ANU Semester 1
X-WR-TIMEZONE:/Australia/Sydney
X-WR-CALDESC:ANU Semester 1.
</script>

<script type="text/template" id="event-template">
BEGIN:VEVENT
DTSTART;TZID=/Australia/Sydney:201902<%= first_day %>T<%= padded_hour %>00
DTEND;TZID=/Australia/Sydney:201902<%= first_day %>T<%= padded_end_hour %>00
RRULE:FREQ=WEEKLY;COUNT=15;BYDAY=<%= day.slice(0,2).toUpperCase() %>
EXDATE;TZID=/Australia/Sydney:201906<%= holiday2 %>T<%= padded_hour %>00
EXDATE;TZID=/Australia/Sydney:201906<%= holiday1 %>T<%= padded_hour %>00
DTSTAMP:20180918T000000Z
CREATED:20180918T000000Z
DESCRIPTION:<%= description %>
LAST-MODIFIED:20180718T000000Z
LOCATION:<%= location %>
SEQUENCE:1
UID:anu2019s1_<%= course %>
STATUS:CONFIRMED
SUMMARY:<%= course %>
TRANSP:OPAQUE
END:VEVENT
</script>

<script type="text/template" id="compulsory-event-template">
  <div class='lesson' data-eventtype='compulsory'
       data-name='<%= item.name %>'>
    <span class="glyphicon glyphicon-pushpin"></span>
    <strong><%= item.name %></strong>.
    <!--<em><%= item.location %></em>.-->
    <%= item.info %>.
    <% if (item.note) { %>
      <span class="glyphicon glyphicon-info-sign" title="<%= item.note %>"></span>
    <% } %>
    <i><%= Tools.pad(Math.floor(item.start), 2) + ':' + (item.start == Math.floor(item.start) ? '0' : '3') + '0-' +
      Math.floor(item.start + item.dur) + ':' + (item.start + item.dur == Math.floor(item.start + item.dur) ? '0' : '3') + '0' %></i>
  </div>
</script>

<script type="text/template" id="group-event-template">
  <div class='lesson' data-eventtype='group'
       data-group='<%= item.name + filterNumbers(item.info) %>'
       data-name='<%= item.name %>'
       data-id='<%= item.id %>'>
       
    <strong><%= item.name %></strong>.
	<%= item.info %>. <br> <br>
    <!--<em><%= item.location %></em>.-->
    <% if (item.note) { %>
      <span class="glyphicon glyphicon-info-sign noprint" title="<%= item.note %>"></span>
    <% } %>
    <br>
    <br>
    <i><%= Tools.pad(Math.floor(item.start), 2) + ':' + (item.start == Math.floor(item.start) ? '0' : '3') + '0-' +
      Math.floor(item.start + item.dur) + ':' 
      + ( ((item.start + item.dur)%1) == 0 ? '00'  : (Math.round((60 * ((item.start + item.dur)%1) )) | 0).toString())
        %></i>
        
      <!--Math.floor(item.start + item.dur) + ':' + (item.start + item.dur == Math.floor(item.start + item.dur) ? '0' : '3') + '0' %></i><br>-->
    <a class="choose" data-html2canvas-ignore="true" href>(choose)&nbsp</a> <a class="hide_temp noprint" data-html2canvas-ignore="true" href>(hide)</a>
    <br/><strong><%= item.day %></strong>
  </div>
</script>

<script type="text/template" id="calendar-template">
  <table class="table table-striped table-condensed" id="cal-container2">
    <tbody>
    <tr>
      <th class="col-sm-1 hours" >
        <!--<span class="glyphicon glyphicon-chevron-left cursor" onclick="Calendar.shiftWeek(-1)"></span><span id="week-num"></span><span class="glyphicon glyphicon-chevron-right cursor" onclick="Calendar.shiftWeek(1)"></span>-->
      </th>
      <% for (var i = 0; i < 5; i++) { %>
      <th class="col-sm-2"><%= Calendar.weekdaysFull[i] %></th>
      <% } %>
    </tr>

    <% for (var hour = start_hour; hour < end_hour; hour += 0.5) { %>
      <tr class="timetable-row" data-hour="<%= hour %>"
      <% if (hour < normal_start_hour || hour >= normal_end_hour) { %>
      style='display:none'
      <% } %>
      >
      <th>
        <%= Tools.pad(Math.floor(hour), 2) %>:<%= hour == Math.floor(hour) ? '0' : '3' %>0
      </th>
      <% for (var i = 0; i < 5; i++) { %>
        <td class="timeslot" data-hour="<%= hour %>" data-day="<%= Calendar.weekdays[i] %>" data-index="-1">
        </td>
      <% } %>
      </tr>
    <% } %>
    </tbody>
  </table>
</script>
</body>
</html>

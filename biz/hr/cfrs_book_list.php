<?php
require_once '../../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');
include_once(G5_PATH . '/head.php');

?>



<link rel="stylesheet" href="<?php echo G5_THEME_URL ?>/plugins/fullcalendar/main.css">


<section class="content">
    <div class="container-fluid">
      <hr>

      <form>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label for="fromDate">Event Name:</label>
              <input type="text" class="form-control" placeholder="Enter event" id="eventName">
            </div>
          </div>
        </div>
        
        <div class="row"> 
          <div class="col-md-6">
            <div class="form-group">
              <label for="fromDate">From:</label>
              <input type="date" class="form-control" placeholder="Enter from date" id="fromDate">
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="form-group">
              <label for="toDate">To:</label>
              <input type="date" class="form-control" id="toDate">
            </div>
          </div>

        </div>

        <button type="button" class="btn btn-primary" id="addEvent">Add Event</button>
      
      </form>
      <hr>

      <div class="row">
        <div class="col-md-12">
            <div id="calendar"></div>
        </div>
      </div>

      
        
    </div>
</section>

<script src="<?php echo G5_THEME_URL ?>/plugins/fullcalendar/main.js"></script>

<script>
$(document).ready(function() {
  ShowCalendar();
});

var events = [];
var calendarEl = document.getElementById('calendar');
var calendar = new FullCalendar.Calendar(calendarEl, {

    initialView: 'dayGridMonth',

    events: function(info, successCallback, failureCallback ) {
      successCallback(events);
    },

  });

function ShowCalendar() {
  calendar.render();
}

$("#addEvent").on("click", function() {
  events.push({
    title: $("#eventName").val(),
    start: $("#fromDate").val(),
    end: $("#toDate").val()
  });

  calendar.refetchEvents();
});
</script>

<?php
include_once(G5_PATH . '/tail.php');

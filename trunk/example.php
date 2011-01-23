<?
require('iCalReader.inc.php');

$ical = new ical('MyCal.ics');
$array= $ical->get_event_array();

// The ical date
$date = $array[0]['DTSTART'];
echo $date;

// The Unix timestamp
echo $ical->ical_date_to_unix_timestamp($date);

// The number of events
echo $ical->event_count;

// The number of events
echo $ical->todo_count;
?>

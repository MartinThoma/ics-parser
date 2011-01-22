<?
require('iCalReader.php');

$ical = new ical('MyCal.ics');
$array= $ical->get_event_array();

// The ical date
$date = $array[0]['DTSTART'];
echo $date;

// The Unix timestamp
echo date('d.m.Y H:i', $date);

?>

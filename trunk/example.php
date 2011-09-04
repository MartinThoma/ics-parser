<?
require('class.iCalReader.php');

$ical = new ical('MyCal.ics');
$array= $ical->get_event_array();

$date = $array[0]['DTSTART'];
echo "The ical date: ";
echo $date;
echo "<br/>";

echo "The Unix timestamp: ";
echo $ical->ical_date_to_unix_timestamp($date);
echo "<br/>";

echo "The number of events: ";
echo $ical->event_count;
echo "<br/>";

echo "The number of todos: ";
echo $ical->todo_count;
echo "<br/>";
?>

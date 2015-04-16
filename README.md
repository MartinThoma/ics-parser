This ics-parser is under MIT license. You may use it for your own sites for
free, but I would like to get a notice when you use it (info@martin-thoma.de).
If you use it for another software project, please let the information / links
to this project in the files.

It is hosted at https://github.com/MartinThoma/ics-parser/ and PEAR coding standard is
used.

**This project will not be continued. [johngrogg/ics-parser](https://github.com/johngrogg/ics-parser) is based on this project, but still supported and a few features were added.**


## Requirements

  * PHP



## Installation

  * Copy all files to a folder where PHP can be executed
  * Include class.iCalReader.php to your project

## Credits

* Martin Thoma (programming, bug-fixing, project management)
* Frank Gregor (programming, feedback, testing)


## Basic Usage ##
The ics-Parser should only provide a very basic object to work with:

```
require 'class.iCalReader.php';

$ical = new ical('MyCal.ics');
print_r($ical->events());
```

## What does $ical->events() return? ##
```
Array
(
    [0] => Array
        (
            [DTSTART] => 20110105T090000Z
            [DTEND] => 20110107T173000Z
            [DTSTAMP] => 20110121T195741Z
            [UID] => 15lc1nvupht8dtfiptenljoiv4@google.com
            [CREATED] => 20110121T195616Z
            [DESCRIPTION] => This is a short description\nwith a new line. Some "special" 'signs' may be <interesting>\, too.
            [LAST-MODIFIED] => 20110121T195729Z
            [LOCATION] => Kansas
            [SEQUENCE] => 2
            [STATUS] => CONFIRMED
            [SUMMARY] => My Holidays
            [TRANSP] => TRANSPARENT
        )

    [1] => Array
        (
            [DTSTART] => 20110112
            [DTEND] => 20110116
            [DTSTAMP] => 20110121T195741Z
            [UID] => 1koigufm110c5hnq6ln57murd4@google.com
            [CREATED] => 20110119T142901Z
            [DESCRIPTION] => 
            [LAST-MODIFIED] => 20110119T152216Z
            [LOCATION] => 
            [SEQUENCE] => 2
            [STATUS] => CONFIRMED
            [SUMMARY] => test 11
            [TRANSP] => TRANSPARENT
        )
)
```

## How do I get a Unix timestamp out of a ical date? ##
If the date is before 1970, it returns false.

```
<?
require 'class.iCalReader.php';

$ical = new ical('MyCal.ics');
$array= $ical->events();

// The ical date
$date = $array[0]['DTSTART'];
echo $date;

// The Unix timestamp
echo $ical->iCalDateToUnixTimestamp($date);

?>
```

## How many events / todos are in the iCal? ##
```
<?
require 'class.iCalReader.php';

$ical = new ical('MyCal.ics');

// The number of events
echo $ical->event_count;

// The number of events
echo $ical->todo_count;

?>
```
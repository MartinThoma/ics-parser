<?
/*
	@fileoverview This PHP-Class should only read a iCal-File (*.ics), parse it

	@author: Martin Thoma
	@version: 1.0
	@website: http://code.google.com/p/ics-parser/
	@example
				$ical = new ical('MyCal.ics');
				print_r( $ical->get_event_array() );
	
	=== Change Log ===
	2011-09-01  fg      * all methods are renamed by the file naming conventions
                          of PHP (and the Zend Framework)
                        * method sortEventsWithOrder(..) added
                        * method eventsFromRange(..) added
                        * metho hasEvents() added
*/


error_reporting(E_ALL);

/**
 * This is the iCal-class
 * @param {string} filename The name of the file which should be parsed
 * @constructor
 */
class ical {
    /* How many ToDos are in this ical? */
    public  /** @type {int} */ $todo_count = 0;

    /* How many events are in this ical? */
    public  /** @type {int} */ $event_count = 0; 

    /* The parsed calendar */
    public /** @type {Array} */ $cal;

    /* Which keyword has been added to cal at last? */
    private /** @type {string} */ $lastKeyWord;

    public function __construct($filename) {
    	if (!$filename) return false;
    	
        $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (stristr($lines[0],'BEGIN:VCALENDAR') === false){
            return false;
        } else {
            foreach ($lines as $line) {
                $line = trim($line);
                $add = $this->split_key_value($line);
                if($add === false){
                    $this->add_to_array($type, false, $line);
                    continue;
                } 

                list($keyword, $value) = $add;

                switch ($line) {
                    // http://www.kanzaki.com/docs/ical/vtodo.html
                    case "BEGIN:VTODO": 
                        $this->todo_count++;
                        $type = "VTODO"; 
                        break; 

                    // http://www.kanzaki.com/docs/ical/vevent.html
                    case "BEGIN:VEVENT": 
                        #echo "vevent gematcht";
                        $this->event_count++;
                        $type = "VEVENT"; 
                        break; 

                    //all other special strings
                    case "BEGIN:VCALENDAR": 
                    case "BEGIN:DAYLIGHT": 

                    // http://www.kanzaki.com/docs/ical/vtimezone.html
                    case "BEGIN:VTIMEZONE": 
                    case "BEGIN:STANDARD": 
                        $type = $value;
                        break; 
                    case "END:VTODO": // end special text - goto VCALENDAR key 
                    case "END:VEVENT": 
                    case "END:VCALENDAR": 
                    case "END:DAYLIGHT": 
                    case "END:VTIMEZONE": 
                    case "END:STANDARD": 
                        $type = "VCALENDAR"; 
                        break; 
                    default:
                        $this->add_to_array($type, $keyword, $value);
                        break; 
                } 
            }
            return $this->cal; 
        }
    }

    /** 
     * Add to $this->ical array one value and key.
     * 
     * @param {string} $type This could be VTODO, VEVENT, VCALENDAR, ... 
     * @param {string} $keyword
     * @param {string} $value 
     */ 
    public function addCalendarComponentWithKeyAndValue( $component, $keyword, $value ) {
        if ($keyword == false) { 
            $keyword = $this->last_keyword; 
            switch ($component) {
              case 'VEVENT': 
                  $value = $this->cal[$component][$this->event_count - 1][$keyword].$value;
                  break;
              case 'VTODO' : 
                  $value = $this->cal[$component][$this->todo_count - 1][$keyword].$value;
                  break;
            }
        }
        
        if (stristr($keyword,"DTSTART") or stristr($keyword,"DTEND")) {
            $keyword = explode(";", $keyword);
            $keyword = $keyword[0];
        }

        switch ($component) { 
            case "VTODO": 
                $this->cal[$component][$this->todo_count - 1][$keyword] = $value;
                #$this->cal[$component][$this->todo_count]['Unix'] = $unixtime;
                break; 
            case "VEVENT": 
                $this->cal[$component][$this->event_count - 1][$keyword] = $value; 
                break; 
            default: 
                $this->cal[$component][$keyword] = $value; 
                break; 
        } 
        $this->last_keyword = $keyword; 
    }

    /**
     * @param {string} $text which is like "VCALENDAR:Begin" or "LOCATION:"
     * @return {Array} array("VCALENDAR", "Begin")
     */
    public function keyValueFromString($text) {
        preg_match("/([^:]+)[:]([\w\W]*)/", $text, $matches);
        if(count($matches) == 0){return false;}
        $matches = array_splice($matches, 1, 2);
        return $matches;
    }

    /** 
     * Return Unix timestamp from ical date time format 
     * 
     * @param {string} $ical_date A Date in the format YYYYMMDD[T]HHMMSS[Z] or
     *                            YYYYMMDD[T]HHMMSS
     * @return {int} 
     */ 
    public function iCalDateToUnixTimestamp($icalDate) { 
        $icalDate = str_replace('T', '', $icalDate); 
        $icalDate = str_replace('Z', '', $icalDate); 

        $pattern = '/([0-9]{4})';   # 1: YYYY
        $pattern.= '([0-9]{2})';    # 2: MM
        $pattern.= '([0-9]{2})';    # 3: DD
        $pattern.= '([0-9]{0,2})';  # 4: HH
        $pattern.= '([0-9]{0,2})';  # 5: MM
        $pattern.= '([0-9]{0,2})/'; # 6: SS
        preg_match($pattern, $icalDate, $date); 

        // Unix timestamp can't represent dates before 1970
        if ($date[1] <= 1970) {
            return false;
        } 
        $timestamp = mktime(
                        (int)$date[4], 
                        (int)$date[5], 
                        (int)$date[6], 
                        (int)$date[2],
                        (int)$date[3], 
                        (int)$date[1]
                      );
        return  $timestamp;
    } 

    /**
     * Returns an array of arrays with all events. Every event is an associative
     * array and each property is an element it.
     * @return {array}
     */
    public function events() {
        $array = $this->cal;
        return $array['VEVENT'];
    }

    /**
     * Returns a boolean value whether thr current calendar has events or not
     *
     * @return {boolean}
     */
    public function hasEvents() {
    	return ( count($this->events()) > 0 ? true : false );
    }

    /**
     * Returns a boolean value whether thr current calendar has events or not
     *
     * @return {boolean}
     */
    public function eventsFromRange( $rangeStart = false, $rangeEnd = false ) {
		$extendedEvents = array();
		
		if (!$rangeStart)
			$rangeStart = new DateTime();
		if (!$rangeEnd)
			$rangeEnd = new DateTime('2038/12/31');

		$rangeStart = $rangeStart->format('U');
		$rangeEnd = $rangeEnd->format('U');

		$events = $this->sortEventsWithOrder( $this->events(), SORT_ASC );

		// loop through all events by adding two new elements
		foreach( $events as $anEvent ) {
			$timestamp = $this->iCalDateToUnixTimestamp($anEvent['DTSTART']);
			if ($timestamp >= $rangeStart)
				$extendedEvents[] = $anEvent;
		}

		return $extendedEvents;
    }

    /**
     * Returns a boolean value whether thr current calendar has events or not
     *
     * @return {boolean}
     */
    public function sortEventsWithOrder( $events, $sortOrder = SORT_ASC ) {
		$extendedEvents = array();
		
		// loop through all events by adding two new elements
		foreach( $events as $anEvent ) {
			if (!array_key_exists( 'UNIX_TIMESTAMP', $anEvent ))
				$anEvent['UNIX_TIMESTAMP'] = $this->iCalDateToUnixTimestamp($anEvent['DTSTART']);

			if (!array_key_exists( 'REAL_DATETIME', $anEvent ))
				$anEvent['REAL_DATETIME'] = date( "d.m.Y", $anEvent['UNIX_TIMESTAMP'] );
			
			$extendedEvents[] = $anEvent;
		}
		
		foreach ($extendedEvents as $key => $value) {
			$timestamp[$key] = $value['UNIX_TIMESTAMP'];
		}
		array_multisort( $timestamp, $sortOrder, $extendedEvents );

		return $extendedEvents;
    }



    /**
     * These methods are marked as deprecated regarding to the naming conventions
     * of PHP (and the Zend Framework) that suggest camelCase for all methods and
     * the iCalendar RFC2445.
     * 
     * @see http://framework.zend.com/manual/en/coding-standard.naming-conventions.html
     * @see http://tools.ietf.org/html/rfc2445
     */
    public function add_to_array($type, $keyword, $value) { return $this->addCalendarComponentWithKeyAndValue( $type, $keyword, $value ); }
    public function split_key_value( $aString ) { return $this->keyValueFromString( $aString ); }
    public function get_event_array() { return $this->events(); }
    public function ical_date_to_unix_timestamp( $iCalDate ) { return $this->iCalDateToUnixTimestamp( $iCalDate ); }
} 
?>

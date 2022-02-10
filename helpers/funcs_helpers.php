<?php
/**
 * View helper & utility functions
 */

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Returns true if the $user is allowed to access the $resource
 * $resource is a string that matches an application relative URI path
 * If $user is null, currently logged in user is tested
 * @param string $resource
 * @param mixed $user
 * @return mixed
 */
function allowed($resource, $user = null)
{
    $acl = app('Acl');
    return $acl->allowed($resource, $user);
}

/**
 * @return \App\Auth\User
 */
function user()
{
    return app('User');
}

/**
 * User information transformed for Rollbar.com error logging
 * @see config/logging.php config('logging.channels.rollbar.person_fn')
 * @return array
 */
function user_rollbar()
{
    $u = user();
    return [
        'id' => $u->person_id,
        'username' => $u->uwnetid,
    ];
}

/**
 * Output an HTML class attribute with one or more css class names
 * Input can include numerically arrayed values which will always be included in output or key => value pairs
 * in which case the key will be added to the class output when value evaluates true
 * @param array $classes
 * @return string
 */
function eCssClasses(array $classes)
{
    $activeClasses = [];
    foreach ($classes as $key => $value) {
        if (is_numeric($key)) {
            $activeClasses[] = $value;
            continue;
        } else {
            if ($value) {
                $activeClasses[] = $key;
            }
        }
    }
    if (count($activeClasses) > 0) {
        $classes = implode(' ', $activeClasses);
        return "class=\"{$classes}\"";
    }
    return '';
}

/**
 * Return a View Response object with "text/csv" header set
 * Use in Controller actions instead of Laravel view() helper for csv downloads for proper handling on MacOS
 * @param $view
 * @param array $data
 * @param int $status
 * @param array $headers
 * @return \Illuminate\Http\Response
 */
function viewCsv($view, $data = [], $status = 200, array $headers = [])
{
    return response()->view($view, $data, $status, $headers)->header('Content-Type', 'text/csv');
}

/**
 * Return a Person record who the current page should be viewed as
 * @return \App\Models\Person\Person
 */
function viewAs()
{
    $person = null;
    $request = request();
    if ($request->has('u')) {
        $person = \App\Models\Person\Person::where('uwnetid', $request->get('u'))->first();
        if ($person instanceof \App\Models\Person\Person) {
            return $person;
        }
    }
    return \App\Models\Person\Person::findOrFail(user()->person_id);
}

/**
 * Return the person_id of the logged in user
 * If this is run in console mode, return zero
 * @return integer
 */
function userPersonIdOrZero()
{
    if (App::runningInConsole()) {
        return 0;
    }
    return user()->person_id;
}

/**
 * Return the rendered input element only
 * @param \App\Core\Forms\Input $input
 * @return string
 */
function input($input)
{
    return app('InputBuilder')->build($input);
}

/**
 * Renders an Input with its label, help message, and errors in project input block markup
 * @param \App\Core\Forms\Input $input
 */
function inputBlock($input)
{
    include __DIR__ . '/../resources/views/layout/part-input-block.php';
}

function inputError($input)
{
    if ($input->hasError()) {
        echo '<div class="inputerror">' . e($input->getError()) .'</div>';
    } else {
        echo '<div class="inputerror" style="display:none;"></div>';
    }
}


/**
 * Display the name of the budget contact with an empty value placeholder
 * Optionally create a link that will trigger an Ajax tool for assigning fiscal manager to a budget
 * @param \App\Models\Budget\EdwBudget $budget
 * @return string
 */
function budgetContact(\App\Models\Budget\EdwBudget $budget, $with_link = false)
{
    if ($budget->fiscal_person_id) {
        $text = htmlspecialchars( eFirstLast($budget->fiscal) );
    } else {
        $text = '<span class="empty">none assigned</span>';
    }
    if ($with_link) {
        $BudgetNbr = $budget->BudgetNbr;
        $id = 'fiscal_' . $BudgetNbr;
        $class = "choose_fiscal";
        return '<a href="javascript:void(0);" class="' . $class . '" id="' . $id . '" data-budgetnbr="' . $BudgetNbr . '">' . $text . '</a>';
    }
    return $text;
}

function budgetStatus(\App\Models\Budget\EdwBudget $budget)
{
    return '<span title="'.$budget->BudgetStatus .' '.$budget->getStatusDescription().'" data-toggle="tooltip" data-placement="bottom">'
    . $budget->getStatusShort() . '</span>';
}

/**
 * Display the work cycle of this budget with an empty value placeholder
 * Optionally create a link that will trigger an Ajax tool for setting work cycle on a budget
 * @param \App\Models\Budget\EdwBudget $budget
 * @return string
 */
function budgetWorkCycle(\App\Models\Budget\EdwBudget $budget, $with_link = false)
{
    if ($budget->workcycle) {
        $text = htmlspecialchars( $budget->workcycle );
    } else {
        $text = '<span class="empty">none</span>';
    }
    if ($with_link) {
        $BudgetNbr = $budget->BudgetNbr;
        $id = 'workcycle_'.$BudgetNbr;
        $class = "choose_workcycle";
        return '<a href="javascript:void(0);" class="'.$class.'" id="'.$id.'" data-budgetnbr="'.$BudgetNbr.'">'.$text.'</a>';
    }
    return $text;
}

/**
 * True if Carbon instance represents an empty date value
 * @param mixed $carbon
 * @return boolean
 */
function carbonEmpty($carbon)
{
    if ($carbon === null) {
        return true;
    }
    if (!$carbon instanceof \Carbon\Carbon) {
        return ($carbon == 0);
    }
    return ($carbon->year < 1200);
}

/**
 * Escape a value to be safely included in a Comma Separated Variable
 * text file. Values that include commas are enclosed in double quotes.
 * When a quoted value contains a double quote character it is escaped
 * by changing it to two sequential double quote characters.
 * @param $value
 * @return string
 */
function csvQuote($value)
{
    // strip new line characters
    if (strpos($value, "\n") !== false) {
        $value = str_replace("\r", '', $value);
        $value = str_replace("\n", ' ', $value);
    }
    // if it has a comma in it, it needs help
    if (strpos($value,',') !== false) {
        // first escape " with and extra "
        $value = str_replace('"', '""', $value);
        return '"' . $value . '"';
    }
    // the string is safe as is
    return $value;
}

/**
 * Convert string reprenting date/time to Carbon instance
 * @param string $date
 * @return \Carbon\Carbon|null
 */
function dateToCarbon($date)
{
    if (empty($date) || $date == '0000-00-00 00:00:00') {
        return null;
    }
    $ts = strtotime($date);
    if ($ts === false) {
        return null;
    }
    return \Carbon\Carbon::createFromTimestamp($ts);
}

function downloadHref()
{
    $out = $_SERVER['REQUEST_URI'];
    if (strpos($out, 'format=csv')) {
        return $out;
    }
    if (empty($_SERVER['QUERY_STRING'])) {
        return $_SERVER['REQUEST_URI'].'?format=csv';
    }
    return $_SERVER['REQUEST_URI'].'&format=csv';
}

/**
 * Format the separate fields of an address into a standard address text
 * block. Gracefully deal with missing fields. HTML escape data where
 * needed.
 * @param mixed $address
 * @param string $line2
 * @param string $city
 * @param string $state
 * @param string $zip
 * @param string $country
 * @return string
 */
function eAddress($address, $line2 = null, $city = null, $state = null, $zip = null, $country = null)
{
    if ($address instanceof \App\Models\Person\Address) {
        $line2 = $address->line2;
        $city = $address->city;
        $state = $address->state;
        $zip = $address->zip;
        $country = $address->country;
        $address = $address->address;
    } elseif ($address instanceof \App\Models\Student\More) {
        $line2 = $address->local_line_2;
        $city = $address->local_city;
        $state = $address->local_state;
        $zip = trim("{$address->local_zip_5} {$address->local_zip_4} {$address->local_postal_cd}");
        $country = $address->local_country;
        $address = $address->local_line_1;
    }
    if ($address) {
        $out[] = e($address);
    }
    if ($line2) {
        $out[] = e($line2);
    }
    if ($city && $state) {
        $out[] = e("{$city}, {$state} {$zip}");
    } else {
        $out[] = e("{$city} {$state} {$zip}");
    }
    if ($country) {
        $out[] = $country;
    }
    return implode('<br />', $out);
}

/**
 * Escape with html break elements.
 * Performs escaping of HTML special characters and then substitutes new line characters with
 * HTML <br /> entities. (BR's in the original input will be escaped and appear literally to
 * the client.)
 * @param string $value
 * @param bool $use_paragraphs change multiple consecutive line breaks into paragraph close/open
 * @return string
 */
function eBreaks($value, $use_paragraphs = false)
{
    $out = htmlspecialchars($value, ENT_COMPAT | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    // $use_paragraph option changes two or three consecutive newlines into a paragraph break
    if ($use_paragraphs) {
        $paragraph_breaks = array("\r\n\r\n\r\n", "\n\n\n", "\r\r\r", "\r\n\r\n", "\n\n", "\r\r");
        $out = str_replace($paragraph_breaks, '</p></p>', $out);
    }
    $newlines = array("\r\n", "\n", "\r");
    $out = str_replace($newlines, '<br />', $out);
    return $out;
}

/**
 * Return a budget number with a hyphen
 * @param $value
 * @return string
 */
function eBudget($value)
{
    if (strpos($value, '-') === false) {
        $parts = str_split($value, 2);
        return array_shift($parts).'-'.implode('', $parts);
    }
    return $value;
}

/**
 * Return string name of UW student class codes
 * @param integer $value
 * @return string
 */
function eClassLevel($value)
{
    if (empty($value) || $value == 8 || $value == 6) {
        return '';
    }
    switch($value) {
        case 0:   return 'Pending';
        case 1:   return 'Freshman';
        case 2:   return 'Sophomore';
        case 3:   return 'Junior';
        case 4:   return 'Senior';
        case 5:   return 'Post-Baccalaureate';
        case 6:   return 'Non-Matriculated';
        case 7:   return 'Invalid (7)';
        case 8:   return 'Graduate';
        case 9:   return 'Invalid (9)';
        case 10:  return 'Invalid (10)';
        case 11:  return '1st Year Professional';
        case 12:  return '2nd Year Professional';
        case 13:  return '3rd Year Professional';
        case 14:  return '4th Year Professional';
        default:  return $value;
    }
}

/**
 * Output an array of data as a comma separated variable row
 * @param array $data
 */
function echoCsvRow(array $data)
{
    $first = true;
    foreach ($data as $value) {
        if (!$first) {
            echo ',';
        }
        $first = false;
        echo csvQuote($value);
    }
    echo PHP_EOL;
}

/**
 * Output a report as a CSV text file download
 * @param string $filename
 * @param array|Traversable $report
 * @param \App\Core\RowBuilder\AbstractRowBuilder $builder
 * @throws Exception
 */
function renderCsv($filename, $report, \App\Core\RowBuilder\AbstractRowBuilder $builder)
{
    if (!is_array($report) && !$report instanceof \Traversable) {
        throw new \Exception('Report must be an array or Traversable');
    }
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    header('Pragma: public');
    header('Content-type: text/csv');
    header('Content-Disposition: attachment; filename='.$filename);
    echoCsvRow($builder->headers());
    foreach ($report as $row) {
        echoCsvRow($builder->build($row));
    }
}


/**
 * Format dates
 * @param $value
 * @param string $format
 * @return bool|string
 */
function eDate($value, $format = 'n/j/Y')
{
    if (empty($value)) {
        return '';
    }
    if ($value instanceof \Carbon\Carbon) {
        if (carbonEmpty($value)) {
            return '';
        }
        return $value->format($format);
    }
    if (is_numeric($value)) {
        return date($format, (int)$value);
    }
    $ts = strtotime($value);
    if ($ts) {
        return date($format, $ts);
    }
    return $value;
}

/**
 * Degree name
 * @param mixed $degreeLevel
 * @param integer|null $degreeType
 * @return string
 */
function eDegree($degreeLevel, $degreeType = null)
{
    if (is_object($degreeLevel)) {
        if ($degreeLevel->degree_name) {
            return $degreeLevel->degree_name;
        }
        $degreeType = $degreeLevel->degree_type;
        $degreeLevel = $degreeLevel->degree_level;
    }
    return Repo::Students()->degreeName($degreeLevel, $degreeType);
}

/**
 * Degree name wrapped in <span> with #-# code as title
 * @param mixed $degreeLevel
 * @param integer|null $degreeType
 * @return string
 */
function eDegreeWithCode($degreeLevel, $degreeType = null)
{
    $name = e(eDegree($degreeLevel, $degreeType));
    if (is_object($degreeLevel)) {
        $degreeType = $degreeLevel->degree_type;
        $degreeLevel = $degreeLevel->degree_level;
    }
    $title = e("{$degreeLevel}-{$degreeType}");
    return "<span title=\"{$title}\">{$name}</span>";
}

/**
 * Degree name and/or class level as appropriate
 * @param integer $degreeLevel
 * @param integer $degreeType
 * @param integer $classLevel
 * @return string
 */
function eDegreeClassLevel($degreeLevel, $degreeType, $classLevel)
{
    if ($degreeLevel == 1 && $degreeType == 1) {
        if ($classLevel > 0) {
            $class = eClassLevel($classLevel);
            return "BA, {$class}";
        }
        return "BA";
    }
    return eDegree($degreeLevel, $degreeType);
}

/**
 * Return person Firstname Lastname if either is set
 * Return person identifier as fallback
 * @param mixed $person
 * @return string
 */
function eFirstLast($person)
{
    if (empty($person)) {
        return '';
    }
    $person = ePersonCache($person);
    $out = trim($person->getFirstName().' '.$person->getLastName());
    if ($out) {
        return $out;
    }
    return $person->getIdentifier();
}

/**
 * Return person Lastname, Firstname if both are set
 * Return person Firstname or Lastname if either is set
 * Return person identifier as fallback
 * @param mixed $person
 * @return string
 */
function eLastFirst($person)
{
    if (empty($person)) {
        return '';
    }
    $person = ePersonCache($person);
    if ($person->getFirstName() && $person->getLastName()) {
        return $person->getLastName().', '.$person->getFirstName();
    }
    $out = trim($person->getLastName().$person->getFirstName());
    if ($out) {
        return $out;
    }
    return $person->getIdentifier();
}

/**
 * Program name
 * @param mixed $minor
 * @param integer|null $pathway
 * @return string
 */
function eMinor($minor, $pathway = null)
{
    if (is_object($minor)) {
        $pathway = $minor->pathway;
        $minor = $minor->minor;
    }
    if ($pathway) {
        return "$minor $pathway";
    }
    return $minor;
}

/**
 * Returns a person name followed by UW NetID in parentheses
 * @param mixed $person
 * @return string
 */
function eNameNetID($person)
{
    if (empty($person)) {
        return '';
    }
    $person = ePersonCache($person);
    if ($person->getFirstName() || $person->getLastName()) {
        return eFirstLast($person).' ('.$person->getIdentifier().')';
    }
    return $person->getIdentifier();
}

/**
 * Return escaped $value, if $value is empty return $emptyMsg in .empty HTML markup
 * @param string $value
 * @param string $emptyMsg
 * @return string
 */
function eOrEmpty($value, $emptyMsg = 'missing')
{
    if ($value === null || $value === '') {
        return '<span class="empty">' . e($emptyMsg) . '</span>';
    }
    return e($value);
}

/**
 * Returns a PersonInterface object
 * If argument is an integer looks up Person by id
 * @param mixed $person_id
 * @return \App\View\PersonInterface
 * @throws Exception
 */
function ePersonCache($person_id)
{
    if ($person_id instanceof \App\View\PersonInterface) {
        return $person_id;
    }
    if (!is_numeric($person_id)) {
        throw new Exception('ePersonCache requires PersonInterface object or person_id');
    }
    return Repo::People()->getCache($person_id);
}

/**
 * Display a phone number stored as only digits with pun
 * @param string $phone
 * @return string
 */
function ePhone($phone)
{
    $m = array();
    preg_match('/(.*)(...)(...)(....)/', $phone, $m);
    $newphone = '';
    if (count($m) == 5){
        if (strlen($m[1]) > 1) {
            $newphone .= $m[1];
        }
        $newphone =  $newphone . '('. $m[2] .') '. $m[3] .'-' .$m[4];
    } else {
        $newphone = $phone;
    }
    return $newphone;
}

/**
 * Program name
 * @param mixed $major
 * @param integer|null $pathway
 * @return string
 */
function eProgram($major, $pathway = null)
{
    if (is_object($major)) {
        if ($major->program_name) {
            return $major->program_name;
        }
        $pathway = $major->pathway;
        $major = $major->major;
    }
    return Repo::Students()->programName($major, $pathway);
}

/**
 * Program name wrapped in <span> with MAJOR PW as title
 * @param mixed $major
 * @param integer|null $pathway
 * @return string
 */
function eProgramWithCode($major, $pathway = null)
{
    $name = e(eProgram($major, $pathway));
    if (is_object($major)) {
        $pathway = $major->pathway;
        $major = $major->major;
    }
    $title = e("{$major} {$pathway}");
    return "<span title=\"{$title}\">{$name}</span>";
}

/**
 * Makes quarters helpers available in unit testing
 * @return \App\Services\UwQuarterService
 */
function quartersService()
{
    static $service = null;
    if ($service === null) {
        try {
            $service = app('quarters');
        } catch (\Exception $e) {
            $service = new \App\Services\UwQuarterService();
        }
    }
    return $service;
}

/**
 * Current quarter as QuarterValue
 * @return App\Models\QuarterInterface
 * @throws \Exception
 */
function currentQuarter()
{
    return quartersService()->current();
}

function reportsCurrentQuarter()
{
    $current = DB::table('uw_quarter')
        ->select(['year', 'quarter'])
        ->orderBy('year')
        ->orderBy('quarter')
        ->whereIn('quarter', [1,2,3,4])
        ->where('start', '>', Carbon::now()->subDays(12))
        ->first();
    if (!$current) {
        throw new \Exception('Missing current quarter data');
    }
    return $current;
}

function eQuarter($year, $quarter = null)
{
    return quartersService()->pretty($year, $quarter);
}

/**
 * Return mixed quarter values as a proper quarter name
 * @param integer|string $qtr
 * @return string
 */
function eQuarterName($qtr)
{
    return quartersService()->quarterToName($qtr);
}

/**
 * Take various UW quarter descriptors and return integer code
 * @param string $quarter
 * @return int
 */
function quarterToInt($quarter)
{
    return quartersService()->quarterToInt($quarter);
}

/**
 * Return year and quarter as a compacted scalar value YYYYQ
 * @param integer $year
 * @param string $quarter
 * @return integer
 */
function quarterToCompact($year, $quarter)
{
    return quartersService()->quarterToCompact($year, $quarter);
}

/**
 * Converts a quarter in compact format YYYYQ to
 * @param integer $compactQuarter
 * @return App\Models\QuarterValue
 * @see App\Services\UwQuarterService
 */
function compactToQuarter($compactQuarter)
{
    return quartersService()->compactToQuarter($compactQuarter);
}

/**
 * Extracts Quarter integer value from a quarter in compact format YYYYQ
 * Winter = 1, Spring = 2, Summer = 3, Autumn = 4
 * @param integer $compactQuarter
 * @return integer
 */
function compactToQuarterNum($compactQuarter)
{
    return quartersService()->extractQuarter($compactQuarter);
}

/**
 * Extracts Year integer value from a quarter in compact format YYYYQ
 * @param integer $compactQuarter
 * @return integer
 */
function compactToYear($compactQuarter)
{
    return quartersService()->extractYear($compactQuarter);
}

/**
 * Return the quarter part (integer 1-4) of a yearq value
 * @param mixed $yearq
 */
function yearqQtr($yearq)
{
    if (strpos($yearq, 'ALL')) {
        return 'ALL';
    }
    if (!isYearq($yearq)) {
        return null;
    }

    return $yearq % 10;
}

/**
 * Return the year part of a yearq value
 * @param mixed $yearq
 * @return int|null
 */
function yearqYear($yearq): ?int
{
    if (strpos($yearq, 'ALL')) {
        $year = explode('A', $yearq);
        return $year[0] + 0;
    }
    if (!isYearq($yearq)) {
        return null;
    }
    return (int) ($yearq / 10);
}

/**
 * True if $value is in compact quarter format YYYYQ
 * @param mixed $yearq
 * @return boolean
 */
function isYearq($yearq): bool
{
    if ($yearq === 0) {
        return true;
    }
    return is_numeric($yearq) && $yearq > 10000 && $yearq < 99995;
}

/**
 * Convert boolean to "Yes" or "No" strings
 * @param mixed $value
 * @param string $yes Display value for true
 * @param string $no Display value for false
 * @param string $empty Display value for empty
 * @return string
 */
function eYesNo($value, $yes = 'Yes', $no = 'No', $empty = '')
{
    if ($value === null) {
        return $empty;
    }
    if ($value && $value !== 'N') {
        return $yes;
    }
    return $no;
}

function eYesNoEm($value, $yes = 'Yes', $no = 'No', $empty = '')
{
    $yn = eYesNo($value, $yes, $no, $empty);
    if ($yn === $empty) {
        return $empty;
    }
    if ($yn === $yes) {
        return e($yes);
    }
    return '<span class="empty">' . e($no) . '</span>';
}

/**
 * Store a flash application message to be displayed on the next page view
 * @param $message
 * @param string $style
 */
function flashMessage($message, $style = 'info')
{
    $request = app('Illuminate\Http\Request');
    $request->session()->put('educ_flash_style', $style);
    $request->session()->put('educ_flash_message', $message);
}

/**
 * @param $uwnetid
 * @param $role
 */
function giveRole($uwnetid, $role)
{
    $person = (new App\Updaters\Person\PersonUpdater('helpers_giveRole'))->updateOrCreate([ 'uwnetid' => $uwnetid ]);
    $handler = new App\Auth\AuthorizationHandler();
    $handler->addRoles($person, $role);
}

/**
 * Returns true if the $user has or inherits the $role
 * If $user is null, currently logged in user is tested
 * @param string $role
 * @param mixed $user
 * @return mixed
 */
function hasRole($role, $user = null)
{
    $acl = App::make('Acl');
    return $acl->hasRole($role, $user);
}

/**
 * Return the formatted completed date of the budgetReport object
 * Wraps in empty class if more than 30 days old
 * Return placeholder if empty
 * @param $budgetReport
 * @return bool|string
 */
function lastBudgetReport($budgetReport)
{
    if (!$budgetReport instanceof App\Models\Budget\BudgetReport) {
        return '<span class="empty">none</span>';
    }
    if ($budgetReport->completed->diff(\Carbon\Carbon::now())->days > 30) {
        return '<span class="empty">'.eDate($budgetReport->completed).'</span>';
    }
    return eDate($budgetReport->completed);
}

/**
 * Returns the $text rendered as a link to URL if the current logged in user is authorized for that resource
 * Otherwise, returns the escaped text with no link
 * @param string $text
 * @param string $url
 * @param string $properties
 * @return string
 */
function linkIfAllowed($text, $url, $properties = '')
{
    if (allowed($url)) {
        return '<a href="'.$url.'" '.$properties.'>'.e($text).'</a>';
    }
    return e($text);
}

/**
 * Displays Attachment (or array of Attachments) as link to download route
 * @param $attachments
 * @param string $glue
 * @return string
 */
function linkDownload($attachments, $glue = '<br />')
{
    if ($attachments instanceof \App\Models\Attachment) {
        $attachments = [ $attachments ];
    }
    $out = [];
    foreach ($attachments as $attachment) {
        if ($attachment->mimetype == 'text/plain' || $attachment->extension == 'txt') {
            $out[] = '<a href="'.action('AttachmentController@text', [$attachment->id]).'" class="download_text" target="_text_">'.e($attachment->description).'</a>';
        } else {
            $out[] = '<a href="'.action('AttachmentController@download', [$attachment->id]).'">'.e($attachment->description).'</a>';
        }
    }
    return implode($glue, $out);
}

/**
 * Assemble and array of attributes into escaped HTML
 * @param array $attributes
 * @return string
 */
function makeAttributes($attributes = [])
{
    if (!$attributes) {
        return '';
    }
    $out = [];
    foreach ($attributes as $attr => $value) {
        $out[] = e($attr) . '="' . e($value) .'"';
    }
    return ' ' . implode(' ', $out);
}

/**
 * Convert mixed input to value "Y", "N" or null
 * @param mixed $value
 * @return string|null
 */
function mixedToYN($value)
{
    if ($value === null || $value === '') {
        return null;
    }
    $value = strtoupper($value);
    if ($value === 'Y' || $value === 'N') {
        return $value;
    }
    if ($value === 'NO') {
        return 'N';
    }
    return ($value) ? 'Y' : 'N';
}

/**
 * Create a pill tag UI element
 * Optionally add HTML $attributes provide in associative array name => value
 * @param $text
 * @param string|null $label
 * @param array $attributes
 * @return string
 */
function pill($text, $label = null, $attributes = [])
{
    if ($label) {
        $out = '<span class="label">'.e($label).'</span> ';
    } else {
        $out = '';
    }
    $out = '<span class="pill"' . makeAttributes($attributes) . '>' . $out . e($text) . '</span>';
    return $out;
}

/**
 * Degree description
 * Degree name, major, dates description
 * @param \App\Models\Appreview\Degree $degree
 * @return string
 */
function degreeDescription(App\Models\Appreview\Degree $degree)
{
    if ($degree->degree_name && $degree->major) {
        $desc = "{$degree->degree_name} in {$degree->major} - ";
    } elseif ($degree->degree_name) {
        $desc = "{$degree->degree_name} - ";
    } elseif ($degree->major) {
        $desc = "{$degree->major} - ";
    } else {
        $desc = '';
    }
    return $desc . $degree->dateText();
}

/**
 * @deprecated added a blade directive @recommendation($rec, $field) for this
 * @param $recommendation_id
 * @return string
 */
function recType($recommendation_id)
{
    return app('RecommendationCodes')->decode($recommendation_id, 'type');
}

/**
 * @deprecated added a blade directive @recommendation($rec, $field) for this
 * @param $recommendation_id
 * @return string
 */
function recFull($recommendation_id)
{
    return app('RecommendationCodes')->decode($recommendation_id, 'full');
}

/**
 * @deprecated added a blade directive @recommendation($rec, $field) for this
 * @param $recommendation_id
 * @return string
 */
function recShort($recommendation_id)
{
    return app('RecommendationCodes')->decode($recommendation_id, 'short');
}

/**
 * Escaped value or &nbsp; entity
 * @param $value
 * @return string
 */
function valueOrNbsp($value)
{
    if (empty($value)) {
        return '&nbsp;';
    }
    return e($value);
}

/**
 * Build a link to the set decision form for an application
 * @param $application
 * @return string
 */
function decisionLink($application)
{
    $text  = e($application->decisionShort());
    $class = $application->decisionType();
    $href = action('Appreview\DecisionsController@decision', [$application->id]);
    return "<a href=\"$href\" class=\"js-decisions js-decision-class js-decision-short $class\">$text</a>";
}

/**
 * Build a link to the set advisor form for an application
 * @param $application
 * @return string
 */
function advisorLink($application)
{
    if ($application->advisor_person_id) {
        $text  = eFirstLast($application->advisor);
        $class = '';
    } else {
        $text = '(no advisor)';
        $class = ' pending';
    }
    $href = action('Appreview\DecisionsController@decision', [$application->id]);
    return "<a href=\"$href\" class=\"js-decisions js-advisor-class js-advisor-short{$class}\">$text</a>";
}

function appIntent($application)
{
    switch ($application->gsappstatus) {
        case 1:
        case 2:
        case 3:
        case 4:
        case 18:
        case 20:
        case 22:
            $text = 'COE Reviewing';
            break;
        case 24:
            $text = 'Offer declined';
            break;
        case 8:
        case 13:
        case 17:
            $text = 'Denied';
            break;
        case 5:
        case 9:
            $text = 'Hold';
            break;
        case 0:
            $text = 'MyGrad Error';
            break;
        case 15:
        case 16:
            $text = 'Offer Accepted';
            break;
        case 10:
        case 14:
            $text = 'Offered';
            break;
        case 6:
            $text = 'Petition';
            break;
        case 11:
        case 12:
            $text = 'Registered';
            break;
        case 7:
            $text = 'Application withdrawn';
            break;
        default:
            $text = '<span class="empty">missing</span>';
            break;
    }
    return e($text);
}

function scrubDefault($value)
{
    return trim(strip_tags($value));
}

function scrubIdNumber($value)
{
    $value = scrubDefault($value);
    return str_replace('-', '', $value);
}

function scrubInteger($value)
{
    return (int) $value;
}

function scrubUwnetid($value)
{
    $value = scrubDefault($value);
    $uwnetid = strtolower($value);
    $atloc = strpos($uwnetid, '@');
    if ($atloc !== false) {
        $uwnetid = substr($uwnetid, 0, $atloc);
    }
    return $uwnetid;
}

/**
 * String SQL statement from include file
 * If $substitutions is an associative array [ KEY => VALUE ]
 * each instance of KEY in SQL will be replaced with VALUE
 * @param string $filename
 * @param array $substitutions
 * @return string
 */
function sqlInclude($filename, $substitutions = [])
{
    $sql = file_get_contents($filename);
    foreach ($substitutions as $placeholder => $value) {
        $sql = str_replace($placeholder, $value, $sql);
    }
    return $sql;
}

/**
 * Run a SQL statement from include file and return the query results as array of stdClass objects
 * @param string $filename
 * @param array $substitutions
 * @return stdClass[]
 */
function sqlIncludeGet($filename, $substitutions = [])
{
    $sql = sqlInclude($filename, $substitutions);
    return DB::select(DB::raw($sql));
}

/**
 * Returns a Logger instance that writes to standard output
 * @return \Monolog\Logger
 */
function stdoutLog()
{
    $log = new Monolog\Logger('testing');
    $log->pushHandler(new Monolog\Handler\StreamHandler('php://stdout', Monolog\Logger::DEBUG));
    return $log;
}

/**
 * Return a year value parse from input
 * Returns null for null or empty string
 * Returns an integer value if year is in range
 * Returns false if a valid value could not be parsed
 * @param mixed $value
 * @param int $minValid
 * @param int $maxValid
 * @return int|false
 */
function validYear($value, $minValid = 1900, $maxValid = 2050)
{

    if ($value === null || $value === '') {
        return null;
    }
    $value = (int) $value;
    if ($value < 100 && $value >= 70) {
        $value = $value + 1900;
    }
    if ($value <= 50 && $value >= 10) {
        $value = $value + 2000;
    }
    if ($value >= $minValid && $value <= $maxValid) {
        return $value;
    }
    return false;
}

function withdrawnBug()
{
    echo '<span class="withdrawn_bug" title="Applicant has withdrawn their application"><img src="/images/withdrawn.png" alt="Withdrawn" /></span>';
}

function pp($object)
{
    echo json_encode($object, JSON_PRETTY_PRINT);
}


/**
 * If content is longer than $maxLength truncate it
 * @param string $content
 * @param int $maxLength
 * @return string
 */
function truncate($content, $maxLength = 70)
{
    if (strlen($content) <= $maxLength) {
        return $content;
    }
    $newLength = $maxLength - 3;
    return substr($content, 0, $newLength) . '...';
}

/**
 * If content is longer than $maxLength truncate it and provide a Bootstrap.js
 * Popover widget with the full content
 * @param $content
 * @param int $maxLength
 * @param null $title
 * @return string
 */
function truncAndPop($content, $maxLength = 70, $title = null)
{
    if (strlen($content) <= $maxLength) {
        return e($content);
    }
    $__trunc = e(substr($content, 0, 67) . '...');
    $__title = ($title) ? ' title="' . e($title) . '"' : '';
    $__content = e($content);
    return "<span data-toggle=\"popover\" data-content=\"{$__content}\" data-placement=\"bottom\"{$__title}>{$__trunc}</span>";
}

/**
 * True if HTTP request was an XMLHTTPRequest (Ajax API interaction)
 * @return boolean
 */
function isAjax()
{
    return request()->ajax();
}

/**
 * True if HTTP request contains format=csv
 * This is our application standard for request report view download as spreadsheet
 * @return boolean
 */
function wantsCsv()
{
    return request('format') == 'csv';
}

/**
 * Send RESTful response, JSON data, for an API route
 * @param array $data
 * @return \Illuminate\Http\JsonResponse
 */
function apiResponse(array $data)
{
    $statusCode = (isset($data['status'])) ? $data['status'] : 200;
    return response()->json($data, $statusCode);
}

/**
 * Send RESTful response, JSON data, but formatted plain-text response
 * Use when an API endpoint is accessed from browser
 * @param array $data
 * @return \Illuminate\Http\Response
 */
function jsonDebugResponse(array $data)
{
    $pretty = json_encode($data, JSON_PRETTY_PRINT);
    return response($pretty, 200)
        ->header('Content-Type', 'text/plain');

}


<?php

namespace App\Models;

use App\Models\AbstractModel;
use App\View\PersonInterface;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Person extends AbstractModel implements PersonInterface
{

    protected $table;

    public function __construct() {
            $this->table = env('DB_DATABASE_SHARED') . 'uw_persons';
    } 


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uwnetid',
        'person_id',
        'firstname',
        'lastname',
        'studentno',
        'employeeid',
        'email',
    ];


    protected $dates = [
        'created_at',
        'updated_at',
    ];

    //protected $table = 'shared.uw_persons';

    protected $primaryKey = 'person_id';

    protected $akas;

    protected $user;

    /**
     * Check for and records a name change
     * Checks whether non-null values differ from last & first name. If different, fields are
     * updated and a Person\Aka record is created for the old name
     * $weight prioritizes input from various sources to make sure better values are not replaced with lesser
     * @param string|null $lastname
     * @param string|null $firstname
     * @param integer $weight
     * @param string|null $legal_name
     */
    public function changeName($lastname = null, $firstname = null, $weight = 0, $legal_name = null)
    {
        if (!$this->exists) {
            // not a name change if this is new record
            $this->lastname = $lastname;
            $this->firstname = $firstname;
            $this->name_weight = $weight;
            return;
        }
        if ($weight < $this->name_weight) {
            return;
        }
        if (!$legal_name) {
            $legal_name = $this->makeLegalName($lastname, $firstname);
        }
        if ($weight > $this->name_weight) {
            $this->name_weight = $weight;
        }
        if ($this->lastname == '') {
            $this->lastname = $lastname;
        }
        if ($this->firstname == '') {
            $this->firstname = $firstname;
        }
        $oldFirst = $this->firstname;
        $oldLast  = $this->lastname;
        $changed  = false;
        if ($lastname !== null && $lastname != $this->lastname) {
            $changed = true;
            $this->lastname = $lastname;
        }
        if ($firstname !== null && $firstname != $this->firstname) {
            $changed = true;
            $this->firstname = $firstname;
        }
        if ($legal_name !== null && $legal_name != $this->legal_name) {
            $this->legal_name = $legal_name;
        }
        if ($changed) {
            $exists = Aka::where('person_id', $this->person_id)
                ->where('lastname', $oldLast)
                ->where('firstname', $oldFirst)
                ->first();
            if (!$exists) {
                $aka = new Aka();
                $aka->person_id = $this->person_id;
                $aka->lastname = $oldLast;
                $aka->firstname = $oldFirst;
                if (!is_array($this->akas)) {
                    $this->akas = [];
                }
                $this->akas[] = $aka;
            }
        }
    }

    /**
     * First or new Email of $type belonging to this person
     * @param string $type
     * @return Email
     */
    public function emailType($type)
    {
        $out = Email::where('person_id', $this->person_id)->where('emailtype', $type)->first();
        if ($out) {
            return $out;
        }
        $out = new Email();
        $out->person_id = $this->person_id;
        $out->emailtype = $type;
        return $out;
    }

    /**
     * True if record has a value for a person identifier within UW data sources
     * @return bool
     */
    public function isUwPerson()
    {
        if ($this->uwnetid || $this->systemkey || $this->employeeid || $this->studentno) {
            return true;
        }
        return false;
    }


    /**
     * Return an email address for this person
     * If UW email address is available, return that first
     * @return string
     */
    public function getEmailPreferUw()
    {
        if ($this->uwnetid) {
            return "{$this->uwnetid}@uw.edu";
        }

        $recentEmail = Email::where('person_id', $this->person_id)->orderBy('updated_at', 'desc')->first();

        return ($recentEmail) ? $recentEmail->email : null;
    }


    /**
     * Return email address in brackets with contact name before it
     * @return string
     */
    public function getEmailWithName(): string
    {
        $email = $this->getEmailPreferUw();

        return ($email) ? "\"{$this->firstname} {$this->lastname}\" <{$email}>" : '';
    }

    public function getIdentifier()
    {
        if ($this->uwnetid) {
            return $this->uwnetid;
        }
        return 'person id '.$this->person_id;
    }

    public function getFirstName()
    {
        return $this->firstname;
    }

    public function getLastName()
    {
        return $this->lastname;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        if (!$this->uwnetid) {
            return null;
        }
        if ($this->user === null) {
           $this->user = new User($this->uwnetid);
        }
        return $this->user;
    }

    public static function makeSearchString($input)
    {
        $value = preg_replace('/[^a-z]/', '', strtolower($input));
        return substr($value, 0, 8);
    }

    public function markSharedUwnetid()
    {
        $this->firstname = 'Shared NetID';
        $this->lastname  = strtoupper($this->uwnetid);
        $this->primaryemail = $this->uwnetid.'@uw.edu';
        $this->uwperson  = false;
        $this->save();
    }

    public function markTechLocal()
    {
        $this->firstname = 'Tech user';
        $this->lastname  = strtoupper($this->uwnetid);
        $this->uwperson  = false;
        $this->save();
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }


protected function preSave()
    {
        /*if (!$this->legal_name) {
            $this->legal_name = $this->makeLegalName($this->lastname, $this->firstname);
        }
        $this->uwperson = $this->isUwPerson();*/
        return true;
    }

    protected function postSave()
    {
        //$this->saveAkas();
        //$this->logChanges();
        return true;
    }

    /**
     * Create value for legal_name field from lastname, firstname
     * @param string $lastname
     * @param string $firstname
     * @return null|string
     */
    protected function makeLegalName($lastname, $firstname)
    {
        if ($firstname && $lastname) {
            return "{$lastname}, {$firstname}";
        }
        $out = "{$lastname}{$firstname}";
        return ($out) ?: null;
    }



}

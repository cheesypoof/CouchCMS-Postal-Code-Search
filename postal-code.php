<?php

/**
 *  Modified from the source of PHP-ZipCode-Class created by Micah Carrick
 *  Original details reproduced below
 */

/**
 *  Zip Code Range and Distance Calculation
 *
 *  Find all zip codes within a given distance of a known zip code.
 *
 *  Project page: https://github.com/Quixotix/PHP-ZipCode-Class
 *  Live example: http://www.micahcarrick.com/code/PHP-ZipCode/example.php
 *
 *  @package    zipcode
 *  @author     Micah Carrick
 *  @copyright  (c) 2011 - Micah Carrick
 *  @version    2.0
 *  @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License v3
 */

class PostalCode
{
    public $mysql_table = 'postal_codes';
    private $template_name = 'search.php'; // The clonable template
    private $field_name = 'postal_code'; // Name of the editable region containing the Postal Code
    private $field_id = 0; // Can be hard-coded to avoid making a database query
                           // Look in phpMyAdmin under the couch_fields table and then the id column

    private $pc;
    private $latitude;
    private $longitude;

    public $error = 0;

    private $print_name;

    /**
     *  Constructor
     *
     *  Instantiate a new PostalCode object by passing in a location. The location
     *  can be specified by a string containing a 5-digit postal code.
     *
     *  @param  string
     *  @return PostalCode
     */
    public function __construct($location)
    {
        $this->pc = $location;
        $this->print_name = $this->pc;
        $this->error = $this->setPropertiesFromDb();
    }

    public function getSqlForPcsInRange($range_from = 0, $range_to = 0)
    {
        if (!$this->error) {
            return $this->getPcsInRange($range_from, $range_to, 1);
        }
    }

    public function getPcsInRange($range_from = 0, $range_to = 0, $return_sql = 0)
    {
        $a = array();
        if ($this->error) return $a;

        $sql = "SELECT '{$this->template_name}' AS template_name, 3956 * 2 * ATAN2(SQRT(POW(SIN((RADIANS({$this->latitude}) - "
              ."RADIANS(z.latitude)) / 2), 2) + COS(RADIANS(z.latitude)) * "
              ."COS(RADIANS({$this->latitude})) * POW(SIN((RADIANS({$this->longitude}) - "
              ."RADIANS(z.longitude)) / 2), 2)), SQRT(1 - POW(SIN((RADIANS({$this->latitude}) - "
              ."RADIANS(z.latitude)) / 2), 2) + COS(RADIANS(z.latitude)) * "
              ."COS(RADIANS({$this->latitude})) * POW(SIN((RADIANS({$this->longitude}) - "
              ."RADIANS(z.longitude)) / 2), 2))) AS \"distance\", z.pc, d.page_id FROM {$this->mysql_table} z "

              ."INNER join ".K_TBL_DATA_TEXT." d on z.pc = d.value "
              ."AND d.field_id = '{$this->field_id}' ";

        if ($range_to > 0) {
            $sql .= "AND latitude BETWEEN ROUND({$this->latitude} - ($range_to / 69.172), 4) "
                   ."AND ROUND({$this->latitude} + ($range_to / 69.172), 4) "
                   ."AND longitude BETWEEN ROUND({$this->longitude} - ABS($range_to / COS({$this->latitude}) * 69.172)) "
                   ."AND ROUND({$this->longitude} + ABS($range_to / COS({$this->latitude}) * 69.172)) "
                   ."AND 3956 * 2 * ATAN2(SQRT(POW(SIN((RADIANS({$this->latitude}) - "
                   ."RADIANS(z.latitude)) / 2), 2) + COS(RADIANS(z.latitude)) * "
                   ."COS(RADIANS({$this->latitude})) * POW(SIN((RADIANS({$this->longitude}) - "
                   ."RADIANS(z.longitude)) / 2), 2)), SQRT(1 - POW(SIN((RADIANS({$this->latitude}) - "
                   ."RADIANS(z.latitude)) / 2), 2) + COS(RADIANS(z.latitude)) * "
                   ."COS(RADIANS({$this->latitude})) * POW(SIN((RADIANS({$this->longitude}) - "
                   ."RADIANS(z.longitude)) / 2), 2))) <= $range_to "
                   ."AND 3956 * 2 * ATAN2(SQRT(POW(SIN((RADIANS({$this->latitude}) - "
                   ."RADIANS(z.latitude)) / 2), 2) + COS(RADIANS(z.latitude)) * "
                   ."COS(RADIANS({$this->latitude})) * POW(SIN((RADIANS({$this->longitude}) - "
                   ."RADIANS(z.longitude)) / 2), 2)), SQRT(1 - POW(SIN((RADIANS({$this->latitude}) - "
                   ."RADIANS(z.latitude)) / 2), 2) + COS(RADIANS(z.latitude)) * "
                   ."COS(RADIANS({$this->latitude})) * POW(SIN((RADIANS({$this->longitude}) - "
                   ."RADIANS(z.longitude)) / 2), 2))) >= $range_from ";
        }


        if ($return_sql) return $sql; // this query will fetch 'template_name', 'page_id', 'pc', 'distance'

        $sql .= "ORDER BY distance ASC";

        $r = mysql_query($sql);
        if ($r) {
            while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {
                $a[$row['distance']] = $row['pc'];
            }
        }

        return $a;
    }

    private function setPropertiesFromDb()
    {
        $sql = "SELECT * FROM {$this->mysql_table} t "
              ."WHERE pc = '{$this->pc}' LIMIT 1 ";

        $r = mysql_query($sql);
        $row = mysql_fetch_array($r, MYSQL_ASSOC);
        mysql_free_result($r);

        if (!$row) {
            return 1;
        }

        foreach ($row as $key => $value) {
            $this->$key = $value;
        }

        // If not set, fetch id of Couch field containing the postal code
        if (!$this->field_id) {
            $sql = "SELECT f.id FROM ".K_TBL_TEMPLATES." t "
                  ."INNER JOIN ".K_TBL_FIELDS." f ON t.id = f.template_id "
                  ."WHERE t.name = '{$this->template_name}' AND f.name = '{$this->field_name}' LIMIT 1";
            $r = mysql_query($sql);
            $row = mysql_fetch_array($r, MYSQL_ASSOC);
            mysql_free_result($r);

            if (!$row) {
                return 1;
            }
            $this->field_id = $row['id'];
        }

        return 0;
    }

    public function __toString()
    {
        return $this->print_name;
    }
}

<?php

namespace Rekurzia\SlovakHolidays;

class Holidays
{
    /**
     * @var array
     */
    private static $fixedHolidays = [
    '01-01' => 'Deň vzniku Slovenskej republiky',
    '01-06' => 'Zjavenie Pána (Traja králi)',

    '05-01' => 'Sviatok práce',
    '05-08' => 'Deň víťazstva nad fašizmom',

    '07-05' => 'Sviatok svätého Cyrila a Metoda',

    '08-29' => 'Výročie SNP',

    '<2024-09-01' => 'Deň Ústavy Slovenskej republiky',
    '09-15' => 'Sedembolestná Panna Mária',

    '11-01' => 'Sviatok všetkých svätých',
    '11-17' => 'Deň boja za slobodu a demokraciu',

    '12-24' => 'Štedrý deň',
    '12-25' => 'Prvý sviatok vianočný',
    '12-26' => 'Druhý sviatok vianočný',

    '=2018-10-30' => '100. výročie prijatia Deklarácie slovenského národa'
    ];

    /**
     * @var array
     */
    private static $easterHolidays = [
    'friday' => 'Veľký piatok',
    'monday' => 'Veľkonočný pondelok'
    ];

    /**
     * Constructor to disable instantiation
     *
     * @throws Exception
     */
    public function __construct()
    {
        throw new Exception('Class cannot be instantiated');
    }

    /**
     * Gets holidays for specified year
     *
     * @param  int $year
     * @param  int $month
     * @return array
     */
    public static function getHolidays($year = null, $month = null)
    {
        $year = $year ?: date('Y');
        $easterSunday = (new \DateTime)->setTimestamp(EasterDate::get($year));

        $holidays = [
        $easterSunday->sub(new \DateInterval('P2D'))->format('Y-m-d') => self::$easterHolidays['friday'],
        $easterSunday->add(new \DateInterval('P3D'))->format('Y-m-d') => self::$easterHolidays['monday'],
        ];

        foreach (self::$fixedHolidays as $key => $holiday) {
            $split = explode("-", $key);
            if(count($split) == 2){
                $holidays[$year . '-' . $key] = $holiday;
            }else{
                $key = $split[1] . "-" . $split[2];
                $comparator = preg_replace('/[\d]/', '$1', $split[0]);
                $_year = preg_replace('/[=<>]/', '$1', $split[0]);
                if(($comparator === "" || $comparator === "=") && $year == $_year){
                    $holidays[$year . '-' . $key] = $holiday;
                }elseif($comparator === "<" && $year < $_year){
                    $holidays[$year . '-' . $key] = $holiday;
                }elseif($comparator === "<=" && $year <= $_year){
                    $holidays[$year . '-' . $key] = $holiday;
                }elseif($comparator === ">" && $year > $_year){
                    $holidays[$year . '-' . $key] = $holiday;
                }elseif($comparator === ">=" && $year >= $_year){
                    $holidays[$year . '-' . $key] = $holiday;
                }
            }

        }

        ksort($holidays);

        if ($month !== null) {
            return self::getHolidaysForYearAndMonth($holidays, $year, $month);
        } else {
            return $holidays;
        }
    }

    /**
     * Gets holiday for specified year and month
     *
     * @param  array $holidays
     * @param  int   $year
     * @param  int   $month
     * @return array
     * @throws Exception
     */
    private static function getHolidaysForYearAndMonth(array $holidays, $year, $month)
    {
        if (!checkdate($month, 1, $year)) {
            throw new Exception('Invalid input year or month');
        }

        foreach ($holidays as $key => $holiday) {
            if (substr($key, 0, 7) !== sprintf("%4d-%02d", $year, $month)) {
                unset($holidays[$key]);
            }
        }

        return $holidays;
    }

    /**
     * Returns if day is holiday
     *
     * @param  int
     * @param  int
     * @param  int
     * @return bool
     * @throws Exception
     */
    public static function isDayHoliday($year, $month, $day)
    {
        if (!checkdate($month, $day, $year)) {
            throw new Exception('Invalid input year, month or day');
        }

        $isHoliday = false;

        foreach (self::getHolidays($year) as $key => $holiday) {
            if ($key === sprintf("%4d-%02d-%02d", $year, $month, $day)) {
                $isHoliday = true;
                break;
            }
        }

        return $isHoliday;
    }

    /**
     * Returns if today is holiday
     *
     * @return bool
     * @throws Exception
     */
    public static function isTodayHoliday()
    {
        return self::isDayHoliday(date('Y'), date('m'), date('d'));
    }
}

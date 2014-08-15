<?php

namespace App;

/**
 * DateInterval
 *
 * @author Petr Poupě
 */
class DateInterval extends \DateInterval
{

    /**
     * Recalculate interval
     * @return self
     */
    public function recalculate()
    {
        $from = new \Nette\Utils\DateTime;
        $toCloned = clone $from;
        $to = $toCloned->add($this);
        $diff = $from->diff($to);
        foreach ($diff as $k => $v) {
            $this->$k = $v;
        }
        return $this;
    }
    
    public static function create($years, $months, $days, $minutes, $seconds)
    {
        $self = new DateInterval("P{$years}Y{$months}M{$days}DT{$minutes}M{$seconds}S");
        return $self->recalculate();
    }

}

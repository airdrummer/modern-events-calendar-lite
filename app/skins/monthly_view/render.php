<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_monthly_view $this */

if(in_array($this->style, ['clean', 'modern'])) $calendar_type = 'calendar_clean';
elseif($this->style == 'novel') $calendar_type = 'calendar_novel';
elseif($this->style == 'simple') $calendar_type = 'calendar_simple';
elseif($this->style == 'admin') $calendar_type = 'calendar_admin';
else $calendar_type = 'calendar';

echo MEC_kses::full($this->draw_monthly_calendar($this->year, $this->month, $this->events, $calendar_type));
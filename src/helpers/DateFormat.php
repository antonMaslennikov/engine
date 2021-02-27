<?php

    namespace tomdom\core\helpers;

	class DateFormat
	{
		public static function month2textmonth($input)
		{
			$months = [
				1 => 'января',
				2 => 'февраля',
				3 => 'марта',
				4 => 'апреля',
				5 => 'мая',
				6 => 'июня',
				7 => 'июля',
				8 => 'августа',
				9 => 'сентября',
				10 => 'октября',
				11 => 'ноября',
				12 => 'декабря',
			];
			
			return $months[(int) $input];
		}
		
		public static function day2textday($input)
		{
			$days = [
				1 => 'пн',
				2 => 'вт',
				3 => 'ср',
				4 => 'чт',
				5 => 'пт',
				6 => 'сб',
				0 => 'вс',
			];
			
			return $days[(int) $input];
		}
	}
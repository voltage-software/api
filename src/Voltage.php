<?php

namespace Cegrent\Voltage;

use Illuminate\Support\Facades\Facade;

class Voltage extends Facade
{
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
	protected static function getFacadeAccessor()
	{
  	return "Voltage";
	}
}

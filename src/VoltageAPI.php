<?php

namespace Cegrent\Voltage;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Request;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\ClientException;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7;

class VoltageAPI
{
	protected $client;

	public function __construct()
  {
		$this->cache_length = Config::get('voltage.cache_length');
		$this->log_message = "voltage/api/";

		$this->client = new Client([
			'cookies' => false,
			'headers' => array('Authorization' => 'Bearer ' . Config::get('voltage.api_password')),
			'base_uri' => Config::get('voltage.api_url'),
			'http_errors' => true
		]);
  }

	/**
	*	get
	*
	*	@param string			$method
	* @param array 			$params
	* @param boolean		$cache
	*	@return $this->build()
	**/
	public function get($stub, $params, $array = array(), $cache = false)
	{
		return $this->build('get', $stub, $params, $array, $cache);
	}

	/**
	*	post
	*
	*	@param string			$method
	* @param array 			$params
	* @param boolean		$cache
	*	@param array 			$array
	*	@return $this->build()
	**/
	public function post($stub, $params, $array = array(), $cache = false)
	{
		return $this->build('post', $stub, $params, $array, $cache);
	}

	/**
	*	put
	*
	*	@param string			$method
	* @param array 			$params
	* @param boolean		$cache
	*	@param array 			$array
	*	@return $this->build()
	**/
	public function put($stub, $params, $array = array(), $cache = false)
	{
		return $this->build('put', $stub, $params, $array, $cache);
	}

	/**
	*	delete
	*
	*	@param string			$method
	* @param array 			$params
	* @param boolean		$cache
	*	@param array 			$array
	*	@return $this->build()
	**/
	public function delete($stub, $params, $array = array(), $cache = false)
	{
		return $this->build('delete', $stub, $params, $array, $cache);
	}

	/**
	*	request
	*
	*
	*	@param string			$method
	* @param array 			$params
	* @param boolean		$cache
	*	@param array 			$array
	*	@return array  		$data
	**/
	public function build($method, $stub, $params, $array = array(), $cache = true)
	{
		try {
			$path = $stub."?".$this->params($params);
			// create a cache key
			$cache_key = base64_encode($path);
	
			// check cache exists
			if($cache && $this->cache_length > 0 && $this->hasCache($cache_key)) {
				// get cached object
				return $this->getCache($cache_key);
			} else {
				// log info for request
				Log:info($this->log_message.' ('.$method.') '.$path);
	
				// do live request
	
				if($method == "get") {
					$data = $this->request->get($stub, $params);
				}

				if($method == "put") {
					$data = $this->request->put($stub, $array);
				}
	
				if($method == "post") {
					$data = $this->request->post($stub, $array);
				}

				if($method == "delete") {
					$data = $this->request->delete($stub, $params);
				}
			}
	
			if($data->successful()) {
				// collect
				$return = $data->collect();
	
				// are we caching?
				if($this->cache_length > 0) {
					// cache request
					$this->cache($data, $cache_key);
				}
	 				
				return $return;
			} elseif($data->serverError()) {
				$data->throw();			
			} elseif($data->failed()) {
				$data->throw();			
			}
		} catch(RequestException $e) {
			report($e);	
		} catch(ConnectionException $e) {
			report($e);	
		}
	}

	/**
	* clearCache
	*
	*	@return void
	*/
	public function clearCache()
	{
		// Log message
		Log::info($this->log_message.' clearing cache');

		// Clear cache
		Cache::flush();
	}

	// Private functions

	/**
	* cache
	*
	* @param  array  $data
  * @param  string  $key
  * @return void
  */
	private function cache($data, $key)
	{
		Log::info($this->log_message.' caching: '.$key);
		Cache::put($key, $data, Carbon::now()->addMinutes($this->cache_length));
	}

	/**
	* hasCache
	*
  * @param  string  $key
  * @return object	Cache
  */
	private function hasCache($key)
	{
		return Cache::has($key);
	}

	/**
	* getCache
	*
  * @param  string  $key
  * @return object	Cache
  */
	private function getCache($key)
	{
		Log:info($this->log_message.' getting cache: "'.$key.'"');
		return Cache::get($key);
	}

	/**
	*	Params string builder
	*
	*	@param array 		$array
	* @return string	$str
	*/
	private function params($array)
	{
		$str = "";
		$i = 0;

		foreach($array as $a => $k) {
			$str .= $a."=".$k;

			if(++$i != count($array)) {
				$str .= "&";
			}
		}

		return $str;
	}
}

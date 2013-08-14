<?php
/**
 *  Simple PHP Wrapper for Crunchbase API
 */
class CrunchBase {
	private $api_key = null;
	private $api_url = "http://api.crunchbase.com/v/1/";

	/**
	 * Class constructor
	 * @param str $api_key API key to use in requests
	 */
	public function __construct($api_key) {
		$this->api_key = $api_key;

		// Setup Curl
		$this->curl = curl_init();
		curl_setopt_array( $this->curl, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array('Content-type: application/json'),
		));
	}

	/**
	 * Performs API Request
	 * @param  str $request 	url for request
	 * @return obj 				object containing results
	 */
	public function do_request($request){
		// Build Request URL
		// TODO: double check for ? before appending api_key
		$resource = $this->api_url.$request."&api_key=".$this->api_key;

		// Getting results
		curl_setopt($this->curl, CURLOPT_URL, $resource);
		$result =  curl_exec($this->curl); // Getting JSON result string

		// TODO: Error handling
		return $result;
	}

	/**
	 * Performs a search on API
	 * @param  mixed $query string or array of query params
	 * @param  int $pages number of pages to get
	 * @return ob
	 */
	public function search($query, $pages = 1){
		// Convert array/object to query string
		if(!is_string($query)){
			$query = http_build_query($query);
		}

		return json_decode($this->do_request("search.js?".$query))->results;
	}

	/**
	 * Gets details on an entity
	 * @param  mixed $query string or array of query params
	 * @return obj
	 */
	public function entity($namespace,$permalink){
		// Do Request
		return json_decode($this->do_request($namespace."/".$permalink.".js?"));
	}

}
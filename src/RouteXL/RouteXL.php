<?php

/*
 * This file is part of the RouteXL package.
 *
 * (c) Tom Coonen <tom@creacoon.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Location example:
 * array(
 *  'name' => '5',
 *   'lat' => 52.3702,
 *   'lng' => 4.8951,
 *   'servicetime' => 5,
 *   'restrictions' => array(
 *       'ready' => 15,
 *       'due' => 60
 *   )
 *
 */

namespace RouteXL;

class RouteXL
{

    /**
     * RouteXL API Endpoint URL
     * @var string
     */
    protected $api_endpoint = 'https://api.routexl.nl/';

    /**
     * API Username
     * @var string
     */
    protected $username = '';

    /**
     * API Password
     * @var string
     */
    protected $password = '';

    /**
     * Array of locations
     * @var array
     */
    protected $itinerary = array();

    /**
     * API Request result
     * @var Object
     */
    protected $result;

    /**
     * API Request HTTP response
     * @var integer
     */
    protected $http_code = 0;

    /**
     * Possible HTTP responses
     * @var array
     */
    protected static $http_codes = array(
        200 => 'OK',
        204 => 'No distance matrix, tour or route was found',
        401 => 'Authentication problem',
        403 => 'Too many locations for your subscription',
        409 => 'No input or no locations found',
        429 => 'Another route in progress',
    );

    /**
     * Create RouteXL instance
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * RouteXL API status
     * @return boolean Returns true when API is online
     */
    public function apiStatus()
    {
        $this->doRequest(
            'status/creacoon',
            ['auth' => [$this->username, $this->password]]);

        return (($this->http_code == 200 && $this->result->echo == 'creacoon') ? true : false);
    }

    /**
     * Fill the itinerary
     * @param array $locations
     */
    public function addLocations(array $locations)
    {
        foreach ($locations as $location) {
            $this->itinerary[] = $location;
        }
    }

    /**
     * Optimize the itinerary
     * @return boolean Returns true on success
     */
    public function tour()
    {
        if (count($this->itinerary) < 2) return false;
        $body = 'locations=' . json_encode($this->itinerary);

        $this->doRequest(
            'tour',
            [
                'auth' => [$this->username, $this->password],
                'body' => $body
            ],
            'POST');

        return (($this->http_code == 200) ? true : false);
    }

    /**
     * Get the result from the API
     * @return Object
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get the message belonging to a HTTP status
     * @return string HTTP Message
     */
    public function getHttpMessage()
    {
        return $http_codes[$this->http_code];
    }

    /**
     * Make a request to the RouteXL API
     * @param  string $method  API Method to call
     * @param  array  $options Options array
     * @param  string $type    Request type
     * @return Object          Result object
     */
    protected function doRequest($method, $options, $type='GET')
    {
        $client = new \GuzzleHttp\Client();
        $r = $client->request(
            $type,
            $this->api_endpoint . $method,
            $options);

        $this->http_code = $r->getStatusCode();

        if ($this->http_code == 200) $this->result = json_decode((string) $r->getBody());
        else $this->result = '';
    }

}
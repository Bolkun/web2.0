<?php
/**
 * This class calls the VAT validator at http://ec.europa.eu/taxation_customs/vies/vatRequest.html
 * and checks the given input against it. If the VAT Validator is not available there is
 * a RegEx Fallback.
 *
 * Example:
 *
 *    if ( isset( $_POST[ 'country_code' ] ) && isset( $_POST[ 'uid' ] ) ) {
 *        $validator = new WC_VAT_Validator( array ( $_POST[ 'country_code' ], $_POST[ 'uid' ] ) );
 *        if ( $validator->is_valid() )
 *            echo 'Valid VAT';
 *        else
 *            echo 'invalid VAT';
 * }
 *
 * $validator = new WC_VAT_Validator( array( 'DE', '263849534' ) );
 * $validator->is_valid();
 */
class RGN_VAT_Validator {

	/**
	 * Location of the wsdl file
	 */
	private $wdsl = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

	/**
	 * Instance of our SOAP client
	 */
	private $client;

	/**
	 * Flag to check if we can make SOAP calls. Is set to true after successfully connecting the SOAP client
	 */
	private $hasClient = false;

	/**
	 * Store the result of the last API call, in case there's more to do with it
	 */
	private $last_api_call;

	/**
	 * Store the result of the last validation
	 */
	private $last_result;

	/**
	 * If the input did not change, we just return the result of the last call using this flag
	 */
	private $is_dirty = true;

	/**
	 * Array of 2 strings: Country Code and UID
	 */
	private $input;

	/**
	 * The country code
	 *
	 * @var    string
	 */
	private $country;

	/**
	 * Errors are handles with HTML-Like error codes
	 */
	private $error = array();

	/**
	 * the error messages
	 */
	private $error_msgs = array();

	public function __construct( $input, $billing_country = '' ) {

		// set the error message
		$this->error_msgs = array(
			'200' => __( 'Successfully validated. (Whew!)', 'b2b-market' ),
			'400' => __( 'API server rejects input. Have you entered a valid country code? A valid country code consists of 2 capitals, like DE for Germany. Check allowed country codes <a href="http://ec.europa.eu/taxation_customs/vies/help.html" target="_blank">here</a>.', 'b2b-market' ),
			'404' => __( 'Could not establish connection to API server. Falling back to static validation. (<a href="http://ec.europa.eu/taxation_customs/vies/help.html" target="_blank">Why?</a>)', 'b2b-market' ),
			'500' => __( 'Validation aborted: No input found. Input might have been rejected, or the number you have entered simply cannot be found.', 'b2b-market' ),
			'501' => __( 'Invalid input: Value passed does not seem to be a string or an array. (Aka something’s wrong here technically.)', 'b2b-market' ),
			'502' => __( 'Invalid input: Array does not seem to consist of 2 strings. (Aka something’s wrong here technically.)', 'b2b-market' ),
			'550' => __( 'RegEx: Country Code not recognized. Have you entered a valid country code? A valid country code consists of 2 capitals, like DE for Germany. Check allowed country codes <a href="http://ec.europa.eu/taxation_customs/vies/help.html" target="_blank">here</a>', 'b2b-market' ),
		);

		// set the input
		$this->set_input( $input );
		$this->country = $billing_country;

		// Try to establish a connection to the API server
		try {

			if ( class_exists( 'SoapClient' ) ) {
				$this->client    = new SoapClient( $this->wdsl );
				$this->hasClient = true;
			} else {
				$this->hasClient = false;
			}

		} catch ( Exception $e ) {
			$this->hasClient = false;
		}

	}

	/**
	 *
	 * @param mixed $input
	 *
	 * @return boolean
	 */
	public function is_valid() {

		// check the cache first
		$cached_vat_numbers = get_option( 'wcvat_cached_vat_numbers' );

		// check if hash is same as the country
		if ( ! empty( $this->country ) && $this->country != $this->input[0] ) {
			return false;
		}

		// set hash
		$hash = md5( $this->input[0] . $this->input[1] );

		// set cache
		if ( get_transient( 'wcvat_cached_vat_number_' . $hash ) == true ) {
			return true;
		}

		if ( $this->is_dirty ) {
			$this->error = array(); // Clear any errors we might have had previously
			// Only do something if there's input to work with
			if ( $this->input != null ) {

				// If we have a SOAP client, call it, otherwise fall back to regex
				if ( $this->hasClient ) {
					$this->last_result = $this->check_API();
				} else {
					$this->error[]     = 404;
					$this->last_result = $this->check_regex( $this->input[0], $this->input[1] );
				}
			} else {
				$this->error[]     = 500;
				$this->last_result = false;
			}

			$this->is_dirty = false;
		}

		// set the last result as cache object
		// if it is valid
		if ( $this->last_result == true ) {
			// set cache for 7 days
			set_transient( 'wcvat_cached_vat_number_' . $hash, $this->last_result, 7 * 24 * HOUR_IN_SECONDS );
		} else {
			// remove from cache
			delete_transient( 'wcvat_cached_vat_number_' . $hash );
		}

		return $this->last_result;
	}

	/**
	 * Sets a new input array, throwing errors along the way if anything's sketchy
	 * TRUE if nothing's sketchy, otherwise FALSE
	 *
	 * @param mixed $input
	 *
	 * @return boolean
	 */
	public function set_input( $input ) {

		// Check if we have valid input
		// If a string was passed, split it and work with the resulting array
		if ( ! is_array( $input ) ) {
			if ( is_string( $input ) ) {
				$input = $this->_parse_string( $input );
			} else {
				$this->error[]  = 501;
				$this->input    = null;
				$this->is_dirty = true;

				return false;
			}
		}

		// Check if there are 2 elements in the array
		if ( isset( $input[0] ) && isset( $input[1] ) ) {
			// Both elements should be strings
			if ( is_string( $input[0] ) && is_string( $input[1] ) ) {
				//Make sure everything's UPPERCASE and set the input array
				$this->input    = array( strtoupper( $input[0] ), strtoupper( $input[1] ) );
				$this->is_dirty = true;
			} else {
				$this->error[]  = 502;
				$this->input    = null;
				$this->is_dirty = true;

				return false;
			}
		}

		$this->is_dirty = true;

		return true;
	}

	/**
	 * Check if there are elements in the errors array
	 *
	 * @return boolean
	 */
	public function has_errors() {

		return ( count( $this->error ) > 0 ) ? true : false;
	}

	/**
	 * Returns an array of all error messages occured during the last validation attempt
	 *
	 * @return string
	 */
	public function get_error_messages() {

		$messages = array();
		foreach ( $this->error as $error_code ) {
			$messages[] = $this->error_msgs[ $error_code ];
		}

		return $messages;
	}

	/**
	 * Returns an array of all error codes occured during the last validation attempt
	 *
	 * @return string
	 */
	public function get_error_codes() {

		return $this->error;
	}

	/**
	 * Returns the description of the current error code
	 *
	 * @return string
	 */
	public function get_last_error_message() {

		return $this->error_msgs[ end( $this->error ) ];
	}

	/**
	 * Returns the current error code
	 *
	 * @return type
	 */
	public function get_last_error_code() {

		return end( $this->error );
	}

	private function check_API() {

		try {
			$result = $this->client->__soapCall( 'checkVat', array(
				'checkVat' => array(
					'countryCode' => $this->input[0],
					'vatNumber'   => $this->input[1]
				)
			) );
		} catch ( Exception $e ) {
			$this->error[]       = 400;
			$this->last_api_call = null;

			return false; // Or fallback here
		}
		$this->last_api_call = $result;

		// set valid error message
		$this->error[] = 200;

		return $result->valid;
	}

	/**
	 * Validates a UID based on country code
	 *  It returns boolean ( match | no match )
	 *
	 * For real validation, use MIAS ->
	 *  http://ec.europa.eu/taxation_customs/taxation/vat/traders/vat_number/index_de.htm
	 *  http://ec.europa.eu/taxation_customs/vies/vieshome.do?selectedLanguage=de
	 *
	 * @param string $country_code
	 * @param string $uid
	 *
	 * @return boolean
	 */
	private function check_regex( $country_code, $uid ) {

		$regex = '';
		switch ( $country_code ) {
			case 'AT':
				$regex = '/^ATU[0-9]{8}$/';
				break;
			case 'BE':
				$regex = '/^BE(?:[0-9]{9}|[0-9]{10})$/';
				break;
			case 'BG':
				$regex = '/^BG(?:[0-9]{9}|[0-9]{10})$/';
				break;
			case 'CY':
				$regex = '/^CY[0-9]{8}[a-zA-Z]$/';
				break;
			case 'CZ':
				$regex = '/^CZ(?:[0-9]{8}|[0-9]{9}|[0-9]{10})$/';
				break;
			case 'DE':
				$regex = '/^DE[0-9]{9}$/';
				break;
			case 'DK':
				$regex = '/^DK(?:[0-9]{2}\s?){4}$/';
				break;
			case 'EE':
				$regex = '/^EE[0-9]{9}$/';
				break;
			case 'GR':
				$regex = '/^EL[0-9]{9}$/';
				break;
			case 'ES':
				$regex = '/^ES[a-zA-Z0-9][0-9]{7}[a-zA-Z0-9]$/';
				break;
			case 'FI':
				$regex = '/^FI[0-9]{9}$/';
				break;
			case 'FR':
				$regex = '/^FR[a-zA-Z0-9]{2}\s?[0-9]{9}$/';
				break;
			case 'GB':
				$regex = '/^GB(?:[0-9]{3}\s?[0-9]{4}\s?[0-9]{2}\s?(?:[0-9]{3})?|[a-zA-Z0-9]{5})$/';
				break;
			case 'HR':
				$regex = '/^HR[0-9]{11}$/';
				break;
			case 'HU':
				$regex = '/^HU[0-9]{8}$/';
				break;
			case 'IE':
				$regex = '/^IE[0-9][a-zA-Z0-9][0-9]{5}[a-z-A-Z]$/';
				break;
			case 'IT':
				$regex = '/^IT[0-9]{11}$/';
				break;
			case 'LT':
				$regex = '/^LT(?:[0-9]{9}|[0-9]{12})$/';
				break;
			case 'LU':
				$regex = '/^LU[0-9]{8}$/';
				break;
			case 'LV':
				$regex = '/^LV[0-9]{11}$/';
				break;
			case 'MT':
				$regex = '/^MT[0-9]{8}$/';
				break;
			case 'NL':
				$regex = '/^NL[0-9]{9}[bB][0-9]{2}$/';
				break;
			case 'PL':
				$regex = '/^PL[0-9]{10}$/';
				break;
			case 'PT':
				$regex = '/^PT[0-9]{9}$/';
				break;
			case 'RO':
				$regex = '/^RO[0-9]{2,10}$/';
				break;
			case 'SE':
				$regex = '/^SE[0-9]{12}$/';
				break;
			case 'SI':
				$regex = '/^SI[0-9]{8}$/';
				break;
			case 'SK':
				$regex = '/^SK[0-9]{10}$/';
				break;
		}

		$result = false;

		if ( $regex !== '' ) {
			$result = preg_match( $regex, $country_code . $uid ) === 1 ? true : false;
		} else {
			$this->error[] = 550;
			$result        = false;
		}

		return $result;
	}

	/**
	 * Split a string after 2 characters and return an array of the results
	 *
	 * @param string $string
	 *
	 * @return array
	 */
	private function _parse_string( $string ) {

		return array( strtoupper( substr( $string, 0, 2 ) ), strtoupper( substr( $string, 2 ) ) );
	}
}

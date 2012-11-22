<?php
/*
* Copyright (c) 2011 Mailjet SAS
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*
*  @author Mailjet SAS
*  @copyright  2011 Mailjet SAS
*  @license    hhttp://opensource.org/licenses/mit-license  MIT License
*  International Registred Trademark & Property of Mailjet SAS
*/

// Security
if (!defined('_PS_VERSION_'))
	exit;

class MailjetAPI
{
	var $version = '0.1';

	# Choose your weapon : php, json, xml, serialize, html, csv
	var $output = 'json'; 
    
	# Connect thru https protocol
	var $secure = true;
	
	# Mode debug ? 0 none / 1 errors only / 2 all
	var $debug = 2;

	# Edit with your Mailjet Infos
	var $apiKey = ''; 
	var $secretKey = '';  


	// Constructor function
	public function __construct($apiKey = false, $secretKey = false)
	{
		if( $apiKey ) $this->apiKey =$apiKey;
		if( $secretKey ) $this->secretKey =$secretKey;
		$this->apiUrl = (($this->secure) ? 'https' : 'http').'://api.mailjet.com/'.$this->version.''; 
	}
    
	public function __call($method,$args) {

    	# params
    	$params = (sizeof($args) > 0) ? $args[0] : array();

    	# request method
    	$request = isset($params["method"]) ? strtoupper($params["method"]) : 'GET';

    	# unset useless params
    	if(isset($params["method"])) unset($params["method"]);

		# Make request
		$result = $this->sendRequest($method,$params,$request);

		# Return result
		$return = ($result === true) ? $this->_response : false;
		
		if( $this->debug == 2 || ( $this->debug == 1 && $return == false ) ){
			$this->debug();
		}
		
		return $return;
	}
    
	public function requestUrlBuilder($method,$params=array(),$request) {



		$query_string = array('output'=>'output='.$this->output);


    	foreach($params as $key=>$value) {
	    	if($request == "GET" || in_array($key,array('apikey','output'))) $query_string[$key] = $key.'='.urlencode($value);
	    	if($key == "output") $this->output = $value;
    	}
    
    	$this->call_url = $this->apiUrl.'/'.$method.'/?'.join('&',$query_string);
    	
    	return $this->call_url;

	}
    
	public function sendRequest($method = false,$params=array(),$request="GET") {
		# Method
		$this->_method = $method;
		$this->_request = $request;

		# Build request URL
		$url = $this->requestUrlBuilder($method,$params,$request);

		# Set up and execute the curl process  
		$curl_handle = curl_init();  
		curl_setopt($curl_handle, CURLOPT_URL, $url);  
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);  
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 2); 
		curl_setopt($curl_handle, CURLOPT_USERPWD, $this->apiKey.':'.$this->secretKey);

		$this->_request_post = false;
		if($request == 'POST') :
			curl_setopt($curl_handle, CURLOPT_POST, count($params));  
			curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($params));  
			$this->_request_post = $params; 
		endif;


		$buffer = curl_exec($curl_handle);  
		
		if($this->debug>2) var_dump($buffer);

		# Response code
		$this->_response_code = curl_getinfo($curl_handle,CURLINFO_HTTP_CODE);

		# Close curl process
		curl_close($curl_handle);  

		# RESPONSE 
		$this->_response = ($this->output == 'json') ? json_decode($buffer) : $buffer;

		return ($this->_response_code == 200) ? true : false;
	
	}



	public function debug() {
		echo '<style type="text/css">';
		echo '

		#debugger {width: 100%; font-family: arial;}
		#debugger table {padding: 0; margin: 0 0 20px; width: 100%; font-size: 11px; text-align: left;border-collapse: collapse;}
		#debugger th, #debugger td {padding: 2px 4px;}
		#debugger tr.h {background: #999; color: #fff;}
		#debugger tr.Success {background:#90c306; color: #fff;}
		#debugger tr.Error {background:#c30029 ; color: #fff;}
		#debugger tr.Not-modified {background:orange ; color: #fff;}
		#debugger th {width: 20%; vertical-align:top; padding-bottom: 8px;}

		';
		echo '</style>';

		echo '<div id="debugger">';

		if(isset($this->_response_code)) :

			if($this->_response_code == 200) :

				echo '<table>';
				echo '<tr class="Success"><th>Success</th><td></td></tr>';
				echo '<tr><th>Status code</th><td>'.$this->_response_code.'</td></tr>';

				if(isset($this->_response)) :
					echo '<tr><th>Response</th><td><pre>'.utf8_decode(print_r($this->_response,1)).'</pre></td></tr>';
				endif;

				echo '</table>';

			elseif($this->_response_code == 304) :

				echo '<table>';
				echo '<tr class="Not-modified"><th>Error</th><td></td></tr>';
				echo '<tr><th>Error no</th><td>'.$this->_response_code.'</td></tr>';
				echo '<tr><th>Message</th><td>Not Modified</td></tr>';
				echo '</table>';

			else :

				echo '<table>';
				echo '<tr class="Error"><th>Error</th><td></td></tr>';
				echo '<tr><th>Error no</th><td>'.$this->_response_code.'</td></tr>';
				if(isset($this->_response)) :
					if( is_array($this->_response) OR  is_object($this->_response) ):
						echo '<tr><th>Status</th><td><pre>'.print_r($this->_response,true).'</pre></td></tr>';
					else:
						echo '<tr><th>Status</th><td><pre>'.$this->_response.'</pre></td></tr>';
					endif;
				endif;
				echo '</table>';

			endif;

		endif;

		$call_url = parse_url($this->call_url);

		echo '<table>';
		echo '<tr class="h"><th>API config</th><td></td></tr>';
		echo '<tr><th>Protocole</th><td>'.$call_url['scheme'].'</td></tr>';
		echo '<tr><th>Host</th><td>'.$call_url['host'].'</td></tr>';
		echo '<tr><th>Version</th><td>'.$this->version.'</td></tr>';
		echo '</table>';

		echo '<table>';
		echo '<tr class="h"><th>Call infos</th><td></td></tr>';
		echo '<tr><th>Method</th><td>'.$this->_method.'</td></tr>';
		echo '<tr><th>Request type</th><td>'.$this->_request.'</td></tr>';
		echo '<tr><th>Get Arguments</th><td>';

		$args = explode("&",$call_url['query']);
		foreach($args as $arg) {
			$arg = explode("=",$arg);
			echo ''.$arg[0].' = <span style="color:#ff6e56;">'.$arg[1].'</span><br/>';
		}
		
		echo '</td></tr>';
		
		if($this->_request_post){
			echo '<tr><th>Post Arguments</th><td>';
		
			foreach($this->_request_post as $k=>$v) {
				echo $k.' = <span style="color:#ff6e56;">'.$v.'</span><br/>';
			}
	
			echo '</td></tr>';
		}

		echo '<tr><th>Call url</th><td>'.$this->call_url.'</td></tr>';
		echo '</table>';

		echo '</div>';
	}
}

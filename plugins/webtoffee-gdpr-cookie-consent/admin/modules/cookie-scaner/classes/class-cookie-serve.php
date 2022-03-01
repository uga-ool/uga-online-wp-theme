<?php
if ( ! defined( 'ABSPATH' ) ) {
	//exit;
}
class Cookie_Law_Info_Cookie_Serve_Api extends Cookie_Law_Info_Cookie_Scaner
{

	public $api_url				=	"https://wp-scanner.cookieyes.com/api/";
	public $api_url_bulk		=	"https://wp-scanner.cookieyes.com/api/v2/";
	public $api_url_alternate	=	"https://scanner.cookieyes.com/api/v2/";
	public $api_path			=	"scan";
	

	private $cookie_types=array(
		'gtag'=>'Non-necessary',
		'ga'=>'Non-necessary',
		'optimize360'=>'Non-necessary',
		'facebook-pixel'=>'Non-necessary',
		'cookie-law-info'=>'Necessary',
		'php'=>'Necessary',
	);
	private $cookies_arr=array(
		'_ga'=>array('gtag','2 year','persistent'),
		'_gid'=>array('gtag','1 day','persistent'),
		'_gat'=>array('gtag','1 minute','session'),
		'AMP_TOKEN'=>array('gtag','1 year','persistent'),
		'__utma'=>array('ga','2 year','persistent'),
		'__utmt'=>array('ga','10 minute','persistent'),
		'__utmb'=>array('ga','30 minute','persistent'),
		'__utmc'=>array('ga','0 minute','session'),
		'__utmz'=>array('ga','6 months','persistent'),
		'__utmv'=>array('ga','2 year','persistent'),
		'__utmx'=>array('ga','18 months','persistent'),
		'__utmxx'=>array('ga','18 months','persistent'),
		'_gaexp'=>array('ga','90 days','persistent'),
		'PHPSESSID'=>array('php','0 minute','session'),
	);
	private $cookie_name_prefix_arr=array(
		'_gac_'=>array('gtag','90 days','persistent'),
		'cookielawinfo-checkbox-'=>array('cookie-law-info','1 year','persistent')
	);
	private $already_processed_cookies=array();
	public function __construct()
	{
		//
	}

	/*
	* check server availability, If primary server not available then check the alternative server.
	*/
	public function check_server()
	{	
		$this->api_url = apply_filters('wt_cli_cookie_scanner_api_url',$this->api_url);
		
		if(self::curl_enabled())
		{
			$response=$this->curl_check($this->api_url);
		}else
		{
			$response=$this->default_check($this->api_url);
		}
		if($response!==false)
		{
			update_option('cli_scanner_api',1);
			return true;
		}else
		{
			if(self::curl_enabled())
			{
				$response_alternate=$this->curl_check($this->api_url_alternate);
			}else
			{
				$response_alternate=$this->default_check($this->api_url_alternate);
			}
			if($response_alternate!==false)
			{
				update_option('cli_scanner_api',2);
				return true;
			}else
			{
				return false;
			}
		}
	}
	
	private function default_check($url)
	{
		return @file_get_contents($url);
	}
	
	private function curl_check($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$out=curl_exec($ch);
		if(curl_error($ch))
		{
			$out=false;
		}
		curl_close($ch);
		return true;
	}

	/*
	*  adding url parameter for cookiebar bypassing to scan urls
	*/
	public function append_bypasspath($url)
	{
		$url_arr=explode("?",$url);
		if(count($url_arr)>1)
		{
		    if(trim($url_arr[1])!="") //query params
		    {
		        parse_str($url_arr[1],$params);
		        $params['cli_bypass']=1;
		        $url_arr[1]=http_build_query($params);
		    }else
		    {
		       $url_arr[1]="cli_bypass=1"; 
		    }
		}else
		{
		   $url_arr[]="cli_bypass=1"; 
		}
		return implode("?",$url_arr);
	}

	/*
	*  removing cookie bar bypassing url parameter from scan urls
	*/
	public function remove_bypasspath($url)
	{
		$url_arr=explode("?",$url);
		if(count($url_arr)>1)
		{
		    if(trim($url_arr[1])!="") //query params
		    {
		        parse_str($url_arr[1],$params);
		        if(isset($params['cli_bypass']))
		        {
		            unset($params['cli_bypass']);
		        }
		        if(count($params)>0)
		        {
		          $url_arr[1]=http_build_query($params);
		          $url=implode("?",$url_arr);
		        }else
		        {
		            $url=$url_arr[0];
		        }
		    }
		}
		return $url;
	}

	/*
	* scan cookies
	*/
	public function get_cookies($url_arr, $status )
	{
		@ini_set('MAX_EXECUTION_TIME',30);
		@set_time_limit(30);
		foreach($url_arr as $k=>$v)
		{
			$url_arr[$k]=$this->append_bypasspath($v);
		}
		$out = $this->fetch_cookies( $url_arr,$status);
		
		if($out!==false)
		{
			$out=json_decode($out,true);
		}
		return $this->process_cookies_main($out);   
	}

	/*
	* Process cookies list
	*
	*/
	public function process_cookies_main($cookies_arr)
	{
		$out=array();
		if($cookies_arr && is_array($cookies_arr))
    	{	
    		foreach($cookies_arr as $url=>$cookies)
    		{
				$url=$this->remove_bypasspath($url);
				
    			$out[$url]=$cookies;
    		}
		}
		
    	return $out;
	}

	/*
	* Process cookies list
	*
	*/
	public function process_cookies_sub($cookies)
	{	
		
		if($cookies && is_array($cookies))
		{
			foreach($cookies as $k=>$cookie)
    		{
    			$cookie_id=trim($cookie['cookie_id']);
    			$cookie['category']=''; //just empty it for below checking
    			
    			//already processed then use it from the backup array
    			if(array_key_exists($cookie_id,$this->already_processed_cookies)) 
    			{
    				$cookie=$this->already_processed_cookies[$cookie_id];
    			}
    			if($cookie['category']=="") //cookie not already processed
    			{
	    			if(array_key_exists($cookie_id,$this->cookies_arr))
					{
						$cookie_detail=$this->cookies_arr[$cookie_id];
						$cookie['category']=$this->cookie_types[$cookie_detail[0]];
						$cookie['duration']=$cookie_detail[1];
						$cookie['type']=$cookie_detail[2];
						$this->already_processed_cookies[$cookie_id]=$cookie;
					}
				}

    			if($cookie['category']=="") //cookie is not in the above list
    			{
    				foreach ($this->cookie_name_prefix_arr as $cookie_prefix=>$cookie_detail) 
	    			{
	    				if(strpos($cookie_id,$cookie_prefix)!==false) //plugin cookies
		    			{	    				
		    				$cookie['category']=$this->cookie_types[$cookie_detail[0]];
		    				$cookie['duration']=$cookie_detail[1];
		    				$cookie['type']=$cookie_detail[2];
		    				$this->already_processed_cookies[$cookie_id]=$cookie;
		    				break 1;
		    			}
	    			}	
    			}
    			if($cookie['category']=="") //cookie is not in our list
    			{
    				$cookie['category']="Non-necessary";
    				$cookie['type']="persistent";
    				if(strpos($cookie['duration'],'ago')!==false)
    				{
    					$cookie['duration']='0 minute';
    					$cookie['type']="session";
    				}
    				$this->already_processed_cookies[$cookie_id]=$cookie;
    			}
    			$cookies[$k]=$cookie;
    		}
		}
		
		return $cookies;
	}

	/*
	* supply API URL based on server availability
	*/
	private function get_api_url()
	{	
		$url = $this->api_url;
		if(get_option('cli_scanner_api')==2)
		{
			$url = $this->api_url_alternate;
		}
		return apply_filters('wt_cli_cookie_scanner_api_url',$url);
	}

	/*
	* call api via default php post request
	*/
	private function default_fetch($url)
	{
		$api_url=$this->get_api_url();
		$postdata = http_build_query(
		    array(
		        'url'=>$url,
		    )
		);
		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => 'Content-type: application/x-www-form-urlencoded',
		        'content' => $postdata
		    )
		);
		$context = stream_context_create($opts);
		$result=@file_get_contents($api_url.$this->api_path, false, $context);
		return $result;
	}

	/*
	* call api via C url
	*/
	private function curl_fetch($url)
	{
		$api_url=$this->get_api_url();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$api_url.$this->api_path);
		curl_setopt($ch, CURLOPT_POST, 1);

		// In real life you should use something like:
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query(array('url' =>$url)));

		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$out=curl_exec($ch);
		if(curl_error($ch))
		{
			$out=false;
		}
		curl_close($ch);
		return $out;
	}
	
	/*
	* check curl available
	*/
	public static function curl_enabled()
	{
		return function_exists('curl_version');
	}
	/**
	* Initiate scanning using Wordpress default function
	*
	* @since  2.3.2
	* @access public
	* @param  Array  URLs
	* @return array
	*/
	public function fetch_cookies( $urls, $status ){

		$endpoint           =   $this->api_url.$this->api_path;
		$token				=	$this->get_ckyes_scan_token();
		$scan_id			=	$this->get_ckyes_scan_id();
		
		$request_body       =   array(
			'domain'		=>	home_url(),
			'urls'  		=>	$urls,
			'isFinished'	=>	$status,
			'scanId'		=>	intval( $scan_id )
		);
		
		$raw_response = wp_remote_post(
			$endpoint,
			array(
				'body'        => wp_json_encode( $request_body ),
				'headers'     => array( 
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer ' . $token,
				),
				'timeout'     => 60,
			)
		);
		$response_code = wp_remote_retrieve_response_code( $raw_response );
	
		if ( 200 !== $response_code ) {
			return false;
		}
		$response = wp_remote_retrieve_body( $raw_response );
		if( $response ) {
			if( $status === true ) {
				$this->reset_scan_token();
			}
			return $response;
		}
		return false;   
	}
	

	public function fetch_all_cookies( $urls ) {

		$endpoint           =   $this->api_url_bulk.$this->api_path;
		$token				=	$this->get_ckyes_scan_token();
		$scan_id			=	$this->get_ckyes_scan_id();
		
		foreach($urls as $key => $url )
		{	
			$urls[ $key ]   = 	$this->append_bypasspath( $url );
		}
		$request_body       =   array(
			'domain'		=>	home_url(),
			'urls'  		=>	$urls,
			'scanId'		=>	intval( $scan_id )
		);
		
		$raw_response = wp_remote_post(
			$endpoint,
			array(
				'body'        => wp_json_encode( $request_body ),
				'headers'     => array( 
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer ' . $token,
				),
				'timeout'     => 60,
			)
		);
		$response_code = wp_remote_retrieve_response_code( $raw_response );
		
		if ( 200 !== $response_code ) {
			return false;
		}
		$response = wp_remote_retrieve_body( $raw_response );
		
		if( $response ) {
			return $response;
		}
		return false;   
	}
}
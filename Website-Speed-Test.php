<?php  
	class TestUrl  
	{  
		var $url;
		var $raw_data_result;
		var $auth_id = "### ENTER AUTH ID ###";
		/*****************************
		 * Function: set_url
		 *
		 * Sets the URL to be tested.
		 *
		 * @param URL 
		 * @return none
		*****************************/
		
		function set_url($testingURL) {
			$this->url = $testingURL;
		}
		
		
		
		/**************************************************************
		 * Function: run_test
		 *
		 * Sends API Key and URL to Speed Test API
		 * and Runs the speed test.
		 * @param none
		 * @return Array (ret_val)returns raw data array of API Response
		***************************************************************/
		
		function run_test() {
			$ch = curl_init("https://www.googleapis.com/pagespeedonline/v1/runPagespeed?url=http://" . $this->url . "&key=" . $this->auth_id);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
			$response = curl_exec($ch);
			$this->raw_data_result = json_decode($response, TRUE);
			curl_close($ch);
			
			//Uncomment below line to debug raw response from API
			return $this->raw_data_result;
		}
		
		
		
		/************************************************
		 * Function: get_url
		 *
		 * Provides URL that was tested
		 * 
		 * @param none
		 * @return String - returns URL that was tested
		***********************************************/
		
		function get_url() {
			return $this->raw_data_result['id'];
		}
		
		
		
		/******************************************
		 * Function: get_overall_score
		 *
		 * Returns overall Score
		 *
		 * @param none
		 * @return Array 
		*****************************************/
		
		function get_overall_score() {
			return $this->raw_data_result['score'];
		}
		
		
		
		/******************************************
		 * Function: get_page_stats
		 *
		 * Returns basic test results. Specifically, 
		 * breaks down bytes sent and requested.
		 *
		 * @param none
		 * @return Array 
		*****************************************/
			
		function get_page_stats() {
			return $this->raw_data_result['pageStats'];			
		}
		
		
		
		/******************************************
		 * Function: get_result_categories
		 *
		 * Returns list of the test categories 
		 * that were run during testing.
		 *
		 * @param none
		 * @return Array 
		*****************************************/
		
		function get_result_categories() {
			$ret_val = array();
			/*foreach($this->raw_data_result['formattedResults']['ruleResults'] as $key=>$value) {
				$ret_val[] = $key;
			}*/
			return $this->raw_data_result['formattedResults'];
		}
		
		
		
		/******************************************
		 * Function: results_less_than_100
		 *
		 * Returns all tests that received a 
		 * less than 100% score.
		 *
		 * @param none
		 * @return Array 
		*****************************************/
			
		function results_less_than_100() {
			$ret_val = array();
			foreach($this->raw_data_result['formattedResults']['ruleResults'] as $key=>$value)
			{
				if($value['ruleScore'] < 100) {
					$ret_val[$key] = $value;
				}
			}
			return $ret_val;
			
		}
		
		
		
		/******************************************
		 * Function: get_basic_results
		 *
		 * Returns test results. Specificaly,
		 * Sitename,Overall Score and HTTP Response Code
		 *
		 * @param none
		 * @return Array 
		*****************************************/
		
		function get_basic_results() {
			$ret_val = array(
				"Tested URL" =>  $this->raw_data_result['id'],
				"Overall Grade" => $this->raw_data_result['score'],
				"HTTP Response Code" => $this->raw_data_result['responseCode'],
			);
			return $ret_val;
		}
		
		
		/******************************************
		 * Function: get_basic_pageStats
		 *
		 * Returns basic test results. Specifically, 
		 * breaks down bytes sent and requested.
		 *
		 * @param none
		 * @return Array 
		*****************************************/
		function get_basic_pageStats() {
			$ret_val = array (
				"Number of Resources Loaded" => $this->raw_data_result['pageStats']['numberResources'],
				"Number of Hosts" =>  $this->raw_data_result['pageStats']['numberHosts'],
				"Total Request Bytes" => $this->raw_data_result['pageStats']['totalRequestBytes'],
				"Number of Static Resources" => $this->raw_data_result['pageStats']['numberStaticResources'],
				"HTML Response Bytes" => $this->raw_data_result['pageStats']['htmlResponseBytes'],
				"Text Response Bytes" => $this->raw_data_result['pageStats']['textResponseBytes'],
				"CSS Response Bytes" => $this->raw_data_result['pageStats']['cssResponseBytes'],
				"Image Response Bytes" => $this->raw_data_result['pageStats']['imageResponseBytes'],
				"Java Script Response Bytes" => $this->raw_data_result['pageStats']['javascriptResponseBytes'],
				"Number of JavaScript Resources" => $this->raw_data_result['pageStats']['numberJsResources'],
				"Number of CSS Resources" => $this->raw_data_result['pageStats']['numberCssResources']
			);
			return $ret_val;
		}
		
		
		/******************************************
		 * Function: get_details
		 *
		 * Returns formatted string of rule details
		 * explained.
		 *
		 * @param INT Key of the urlBlock to query
		 * @return Array 
		*****************************************/
		
		function get_details($key) {
			$details = $this->raw_data_result['formattedResults']['ruleResults'][$key]['urlBlocks'][0]['header']['format'];
			
			if(isset($this->raw_data_result['formattedResults']['ruleResults'][$key]['urlBlocks'][0]['header']['args'])){
				$value1 = $this->raw_data_result['formattedResults']['ruleResults'][$key]['urlBlocks'][0]['header']['args'][0]['value'];
				$details = str_replace("$1",$value1,$details);
			}
			if(isset($this->raw_data_result['formattedResults']['ruleResults'][$key]['urlBlocks'][0]['header']['args'][1])) {
				$value2 = $this->raw_data_result['formattedResults']['ruleResults'][$key]['urlBlocks'][0]['header']['args'][1]['value'];
				$details = str_replace("$2",$value2,$details);
			}
			return $details;
		}
		
		/******************************************
		 * Function: get_urls
		 *
		 * Returns formated list of URLs violating
		 * the rule queried.
		 *
		 * @param key
		 * @return key of Rule to query for URLS
		*****************************************/
		
		function get_urls($key) {
			$ret_val = array();
			$my_arr = $this->raw_data_result['formattedResults']['ruleResults'][$key]['urlBlocks'][0];
			if(isset($my_arr['urls']))
			{
				foreach($my_arr['urls'] as $data)
				{
					$details = $data['result']['format'];
					//print_r($data['result']);
					if(isset($data['result']['args'][0])) {
						$value1 = $data['result']['args'][0]['value'];
						$details = str_replace("$1",$value1,$details);
					}
					if(isset($data['result']['args'][1])) {
						$value2 = $data['result']['args'][1]['value'];
						$details = str_replace("$2",$value2,$details);
					}
					if(isset($data['result']['args'][2])) {
						$value3 = $data['result']['args'][2]['value'];
						$details = str_replace("$3",$value3,$details);
					}
					$ret_val[] = $details;
				}
				return $ret_val;
			}
			else return NULL;
		}
		
		/******************************************
		 * Function: get_problem_areas
		 *
		 * Returls a formatted array of results less than 100,
		 * includes all example URLS, scores and details.
		 *
		 * @param none
		 * @return Array 
		*****************************************/
		function get_problem_areas() {
			$issue = $this->results_less_than_100();
			$name_arr = array();
			$ret_val = array();
			$deetz_arr = array();
			foreach ($issue as $key=>$value)
			{
				
					$name_arr[] = $value['localizedRuleName'];
					
				
				
					$score_arr[] = $value['ruleScore'];
					
				
				
					$impact_arr[] = $value['ruleImpact'];
					
				
				
					$deetz_arr[] = $this->get_details($key);
				
				
					$url_arr[] = $this->get_urls($key);
				
			}
			$index = 0;
			foreach($name_arr as $ruleName)
			{
				$url_index = 0;
				$ret_val[] = array(
					"Rule" => $name_arr[$index],
					"Score" => $score_arr[$index],
					"Impact" => $impact_arr[$index],
					"Details" => $deetz_arr[$index],
					"URLS" => $url_arr[$index]
				);
				
				$index++;
			}
			
			return $ret_val;
		}
		
		
		
		/******************************************
		 * Function: get_perfect_results
		 *
		 * Returns tests that resulted in 100%
		 * @param none
		 * @return Array 
		*****************************************/
		function perfect_results() {
			foreach($this->raw_data_result['formattedResults']['ruleResults'] as $key=>$value)
			{
				if($value['ruleScore'] == 100) {
					print_r($value);
				}
			}
		}
	}  
?>

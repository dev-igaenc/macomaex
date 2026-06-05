<?php

/* !
 * HybridAuth
 * http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
 * (c) 2009-2015, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
 */

/**
 * Hybrid_Providers_Google provider adapter based on OAuth2 protocol
 *
 * http://hybridauth.sourceforge.net/userguide/IDProvider_info_Google.html
 */
class Hybrid_Providers_Google extends Hybrid_Provider_Model_OAuth2 {

	/**
	 * > more infos on google APIs: http://developer.google.com (official site)
	 * or here: http://discovery-check.appspot.com/ (unofficial but up to date)
	 * default permissions
	 * {@inheritdoc}
	 */
	// 기본 동의항목에 전화번호 읽기 권한(People API)을 포함합니다.
	public $scope = "https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/user.phonenumbers.read https://www.google.com/m8/feeds/";

	/**
	 * {@inheritdoc}
	 */
	function initialize() {
		parent::initialize();

		// Provider api end-points
		$this->api->authorize_url = "https://accounts.google.com/o/oauth2/auth";
		$this->api->token_url = "https://accounts.google.com/o/oauth2/token";
		$this->api->token_info_url = "https://www.googleapis.com/oauth2/v2/tokeninfo";

		// Google POST methods require an access_token in the header
		$this->api->curl_header = array("Authorization: OAuth " . $this->api->access_token);

		// Override the redirect uri when it's set in the config parameters. This way we prevent
		// redirect uri mismatches when authenticating with Google.
		if (isset($this->config['redirect_uri']) && !empty($this->config['redirect_uri'])) {
			$this->api->redirect_uri = $this->config['redirect_uri'];
		}
	}

	/**
	 * {@inheritdoc}
	 */
	function loginBegin() {
		$parameters = array("scope" => $this->scope, "access_type" => "offline");
		$optionals = array("scope", "access_type", "redirect_uri", "approval_prompt", "hd", "state");

		foreach ($optionals as $parameter) {
			if (isset($this->config[$parameter]) && !empty($this->config[$parameter])) {
				$parameters[$parameter] = $this->config[$parameter];
			}
			if (isset($this->config["scope"]) && !empty($this->config["scope"])) {
				$this->scope = $this->config["scope"];
			}
		}

		if (isset($this->config['force']) && $this->config['force'] === true) {
			$parameters['approval_prompt'] = 'force';
		}

		Hybrid_Auth::redirect($this->api->authorizeUrl($parameters));
	}

	/**
	 * {@inheritdoc}
	 */
	function getUserProfile() {
		// refresh tokens if needed
		$this->refreshToken();

		$response = $this->api->api("https://www.googleapis.com/oauth2/v3/userinfo");
		if (!isset($response->sub) || isset($response->error)) {
			throw new Exception("User profile request failed! {$this->providerId} returned an invalid response:" . Hybrid_Logger::dumpData( $response ), 6);
		}

		$this->user->profile->identifier = (property_exists($response, 'sub')) ? $response->sub : "";
		$this->user->profile->firstName = (property_exists($response, 'given_name')) ? $response->given_name : "";
		$this->user->profile->lastName = (property_exists($response, 'family_name')) ? $response->family_name : "";
		$this->user->profile->displayName = (property_exists($response, 'name')) ? $response->name : "";
		$this->user->profile->photoURL = (property_exists($response, 'picture')) ? $response->picture : "";
		$this->user->profile->profileURL = (property_exists($response, 'profile')) ? $response->profile : "";
		$this->user->profile->gender = (property_exists($response, 'gender')) ? $response->gender : "";
		$this->user->profile->language = (property_exists($response, 'locale')) ? $response->locale : "";
			$this->user->profile->email = (property_exists($response, 'email')) ? $response->email : "";
			$this->user->profile->emailVerified = (property_exists($response, 'email_verified')) ? ($response->email_verified === true || $response->email_verified === 1 ? $response->email : "") : "";
        
		if (property_exists($response, 'emails')) {
			if (count($response->emails) == 1) {
				$this->user->profile->email = $response->emails[0]->value;
			} else {
				foreach ($response->emails as $email) {
					if ($email->type == 'account') {
						$this->user->profile->email = $email->value;
						break;
					}
				}
			}
			if (property_exists($verified, 'emails')) {
				if (count($verified->emails) == 1) {
					$this->user->profile->emailVerified = $verified->emails[0]->value;
				} else {
					foreach ($verified->emails as $email) {
						if ($email->type == 'account') {
							$this->user->profile->emailVerified = $email->value;
							break;
						}
					}
				}
				}
			}

	        // People API에서 이름/전화번호를 보강 조회합니다.
	        $peopleProfile = $this->getPeopleProfile();
	        if (is_array($peopleProfile)) {
	            // userinfo 이름이 비어있을 때만 People 이름으로 보강합니다.
	            if (empty($this->user->profile->displayName) && !empty($peopleProfile['name'])) {
	                $this->user->profile->displayName = $peopleProfile['name'];
	            }
	            // 소셜 공통 매핑에서 사용할 실명 필드를 함께 채웁니다.
	            if (empty($this->user->profile->username) && !empty($peopleProfile['name'])) {
	                $this->user->profile->username = $peopleProfile['name'];
	            }
	            // 휴대폰은 People API 결과를 우선 사용합니다.
	            if (!empty($peopleProfile['phone'])) {
	                $this->user->profile->phone = $peopleProfile['phone'];
	            }
	        }

	        $this->user->profile->sid         = get_social_convert_id( $this->user->profile->identifier, $this->providerId );

			return $this->user->profile;
		}

		// Google People API에서 이름/전화번호를 조회합니다.
		function getPeopleProfile() {
			try {
				$response = $this->api->api("https://people.googleapis.com/v1/people/me?personFields=names,phoneNumbers");
				// TEMP DEBUG: Google People API 응답 원문 확인용 (확인 후 제거)
				$debug_payload = json_encode(array(
					'provider' => 'google',
					'people_response' => $response,
				), JSON_UNESCAPED_UNICODE);
				error_log('[social_google_people_api_response] ' . $debug_payload);
				if (defined('G5_PATH')) {
					$debug_dir = G5_PATH . '/logs/social';
					if (!is_dir($debug_dir)) {
						@mkdir($debug_dir, G5_DIR_PERMISSION, true);
						@chmod($debug_dir, G5_DIR_PERMISSION);
					}
					$debug_file = $debug_dir . '/social_login_log_' . date('Ymd') . '.php';
					$debug_line = '[' . date('Y-m-d H:i:s') . '] ' . $debug_payload . PHP_EOL;
					if (!file_exists($debug_file)) {
						@file_put_contents($debug_file, "<?php if (!defined('G5_PATH')) exit; ?>\n", FILE_APPEND);
					}
					@file_put_contents($debug_file, $debug_line, FILE_APPEND);
					@chmod($debug_file, G5_FILE_PERMISSION);
				}
				if (!$response || isset($response->error)) {
					return array('name' => '', 'phone' => '');
				}

				$name = '';
				$phone = '';

				// 이름은 displayName을 우선 사용합니다.
				if (property_exists($response, 'names') && is_array($response->names)) {
					foreach ($response->names as $nameItem) {
						if (property_exists($nameItem, 'displayName') && !empty($nameItem->displayName)) {
							$name = (string)$nameItem->displayName;
							break;
						}
					}
				}

				// 전화번호는 mobile > main > 첫 값 순으로 선택합니다.
				if (property_exists($response, 'phoneNumbers') && is_array($response->phoneNumbers) && count($response->phoneNumbers) > 0) {
					$firstValue = '';
					foreach ($response->phoneNumbers as $phoneItem) {
						$phoneValue = '';
						if (property_exists($phoneItem, 'canonicalForm') && !empty($phoneItem->canonicalForm)) {
							$phoneValue = (string)$phoneItem->canonicalForm;
						} else if (property_exists($phoneItem, 'value') && !empty($phoneItem->value)) {
							$phoneValue = (string)$phoneItem->value;
						}

						if ($phoneValue !== '') {
							if ($firstValue === '') {
								$firstValue = $phoneValue;
							}
							$type = property_exists($phoneItem, 'type') ? (string)$phoneItem->type : '';
							if ($type === 'mobile') {
								$phone = $phoneValue;
								break;
							}
							if ($phone === '' && $type === 'main') {
								$phone = $phoneValue;
							}
						}
					}

					if ($phone === '' && $firstValue !== '') {
						$phone = $firstValue;
					}
				}

				return array('name' => $name, 'phone' => $phone);
			} catch (Exception $e) {
				// People API 오류가 나더라도 로그인 자체는 유지합니다.
				return array('name' => '', 'phone' => '');
			}
		}

	/**
	 * {@inheritdoc}
	 */
	function getUserContacts() {
		// refresh tokens if needed
		$this->refreshToken();
		
		$contacts = array();
		if (!isset($this->config['contacts_param'])) {
			$this->config['contacts_param'] = array("max-results" => 500);
		}
		
		// Google Gmail and Android contacts
		if (strpos($this->scope, '/m8/feeds/') !== false) {
			
			$response = $this->api->api("https://www.google.com/m8/feeds/contacts/default/full?"
					. http_build_query(array_merge(array('alt' => 'json'), $this->config['contacts_param'])));
			
			if (!$response) {
				return array();
			}
			
			if (isset($response->feed->entry)) {
				foreach ($response->feed->entry as $idx => $entry) {
					$uc = new Hybrid_User_Contact();
					$uc->email = isset($entry->{'gd$email'}[0]->address) ? (string) $entry->{'gd$email'}[0]->address : '';
					$uc->displayName = isset($entry->title->{'$t'}) ? (string) $entry->title->{'$t'} : '';
					$uc->identifier = ($uc->email != '') ? $uc->email : '';
					$uc->description = '';
					if (property_exists($entry, 'link')) {
						/**
						 * sign links with access_token
						 */
						if (is_array($entry->link)) {
							foreach ($entry->link as $l) {
								if (property_exists($l, 'gd$etag') && $l->type == "image/*") {
									$uc->photoURL = $this->addUrlParam($l->href, array('access_token' => $this->api->access_token));
								} else if ($l->type == "self") {
									$uc->profileURL = $this->addUrlParam($l->href, array('access_token' => $this->api->access_token));
								}
							}
						}
					} else {
						$uc->profileURL = '';
					}
					if (property_exists($response, 'website')) {
						if (is_array($response->website)) {
							foreach ($response->website as $w) {
								if ($w->primary == true)
									$uc->webSiteURL = $w->value;
							}
						} else {
							$uc->webSiteURL = $response->website->value;
						}
					} else {
						$uc->webSiteURL = '';
					}
					
					$contacts[] = $uc;
				}
			}
		}
		
		// Google social contacts
		if (strpos($this->scope, '/auth/plus.login') !== false) {
			
			$response = $this->api->api("https://www.googleapis.com/plus/v1/people/me/people/visible?"
					. http_build_query($this->config['contacts_param']));
			
			if (!$response) {
				return array();
			}
			
			foreach ($response->items as $idx => $item) {
				$uc = new Hybrid_User_Contact();
				$uc->email = (property_exists($item, 'email')) ? $item->email : '';
				$uc->displayName = (property_exists($item, 'displayName')) ? $item->displayName : '';
				$uc->identifier = (property_exists($item, 'id')) ? $item->id : '';
				
				$uc->description = (property_exists($item, 'objectType')) ? $item->objectType : '';
				$uc->photoURL = (property_exists($item, 'image')) ? ((property_exists($item->image, 'url')) ? $item->image->url : '') : '';
				$uc->profileURL = (property_exists($item, 'url')) ? $item->url : '';
				$uc->webSiteURL = '';
				
				$contacts[] = $uc;
			}
		}
		
		return $contacts;
	}
	
	/**
	 * Add query parameters to the $url
	 *
	 * @param string $url    URL
	 * @param array  $params Parameters to add
	 * @return string
	 */
	function addUrlParam($url, array $params){		
		$query = parse_url($url, PHP_URL_QUERY);

		// Returns the URL string with new parameters
		if ($query) {
			$url .= '&' . http_build_query($params);
		} else {
			$url .= '?' . http_build_query($params);
		}
		return $url;
	}

}

<?php
/**
 * YAmoCRM class. amocrm.ru
 *
 * @package    YAmoCRM
 * @author     Davtyan David <iam@ddavtyan.com>
 * @link http://www.aboutgadget.ru
 * @link https://github.com/dotzero/YiiAmoCRM
 * @link https://www.amocrm.ru/add-ons/api.php
 * @license MIT
 * @version 1.0 (31-07-2014)
 * @copyright  (c) 2014 Davtyan David (aboutgadget.ru)
 */
class YAmoCRM extends CComponent {
	
	private $subdomain = 'example'; #Субдомен
	private $user=array(
		'USER_LOGIN'=>'example@example.ru', #Логин пользователя
		'USER_HASH'=>'c123ae456cd7891246bffb1e654abb9d' #Хэш для доступа к API (смотрите в профиле пользователя)
	);
	public $UserInfo;

	public function Auth()
	{
		$url = 'https://'.$this->subdomain.'.amocrm.ru/private/api/auth.php?type=json';
		$res = $this->POST($url, $this->user);
		if(isset($res['auth']))
			return true;
		else
			return false;
	}
	
	public function AddContacts($newcontacts = array(), $updatecontacts = array())
	{
		$contacts['request']['contacts']['add'] = $newcontacts;
		$contacts['request']['contacts']['update'] = $updatecontacts;
		$url = 'https://'.$this->subdomain.'.amocrm.ru/private/api/v2/json/contacts/set';
		$res = $this->POST($url, $contacts);
		return $res['contacts'];
	}
	
	public function GetContacts($quer = "")
	{
		$url = 'https://'.$this->subdomain.'.amocrm.ru/private/api/v2/json/contacts/list';
		if(isset($quer) && $quer != "")
			$url = $url."?query=".$quer;
		$res = $this->GET($url);
		return $res;
	}
	
	public function GetContact($id)
	{
		$url = 'https://'.$this->subdomain.'.amocrm.ru/private/api/v2/json/contacts/list?id='.$id;
		$res = $this->GET($url);
		return $res['contacts'][0];
	}
	
	public function AddLeads($newleads = array(), $updateleads = array())
	{
		$leads['request']['leads']['add'] = $newleads;
		$leads['request']['leads']['update'] = $updateleads;
		$url = 'https://'.$this->subdomain.'.amocrm.ru/private/api/v2/json/leads/set';
		$res = $this->POST($url, $leads);
		return $res['leads'];
	}
	
	public function GetLeads($quer = "")
	{
		$url = 'https://'.$this->subdomain.'.amocrm.ru/private/api/v2/json/leads/list';
		if(isset($quer) && $quer != "")
			$url = $url."?query=".$quer;
		$res = $this->GET($url);
		return $res;
	}
	
	public function GetLead($id)
	{
		$url = 'https://'.$this->subdomain.'.amocrm.ru/private/api/v2/json/leads/list?id='.$id;
		$res = $this->GET($url);
		return $res['leads'][0];
	}
	
	public function AddTasks($newtasks = array(), $updatetasks = array())
	{
		$tasks['request']['tasks']['add'] = $newtasks;
		$tasks['request']['tasks']['update'] = $updatetasks;
		$url = 'https://'.$this->subdomain.'.amocrm.ru/private/api/v2/json/tasks/set';
		$res = $this->POST($url, $tasks);
		return $res['tasks'];
	}
	
	public function GetTasks($quer = "")
	{
		$url = 'https://'.$this->subdomain.'.amocrm.ru/private/api/v2/json/tasks/list';
		if(isset($quer) && $quer != "")
			$url = $url."?query=".$quer;
		$res = $this->GET($url);
		return $res;
	}
	
	public function AccountInfo()
	{
		$url = 'https://'.$this->subdomain.'.amocrm.ru/private/api/v2/json/accounts/current';
		$res = $this->GET($url);
		$this->UserInfo = $res['account'];
	}
	
	public function GetContactByNameAndPhone($name, $phone)
	{
		$contact = array();
		$contacts = $this->GetContacts($name);
		$contacts = $contacts['contacts'];
		if(is_array($contacts) && count($contacts) > 0){
			foreach($contacts as $cont){
				$cf = $cont['custom_fields'];
				foreach($cf as $filed){
					if($filed['code'] == "PHONE"){
						foreach($filed['values'] as $val){
							if($val['value'] == $phone){
								return $cont;
							}
						}
					}
				}
			}
		}
		return $contact;
	}

	public function POST($url, $param)
	{
		$curl=curl_init();
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
		curl_setopt($curl,CURLOPT_URL,$url);
		curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
		curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($param));
		curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
		curl_setopt($curl,CURLOPT_HEADER,false);
		curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
		curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
		
		$out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
		$code=curl_getinfo($curl,CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
		curl_close($curl); #Заверашем сеанс cURL
		try
		{
			if($code!=200 && $code!=204)
				throw new CHttpException($code,isset($this->errors[$code]) ? $this->errors[$code] : 'Undescribed error');
			}
		catch(Exception $E)
		{
			die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
		}
		$Response=json_decode($out,true);
		return $Response['response'];
	}
	
	public function GET($url)
	{
		$curl=curl_init();
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
		curl_setopt($curl,CURLOPT_URL,$url);
		curl_setopt($curl,CURLOPT_HEADER,false);
		curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
		curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
		
		$out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
		$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
		curl_close($curl);
		try
		{
			if($code!=200 && $code!=204)
				throw new CHttpException($code,isset($this->errors[$code]) ? $this->errors[$code] : 'Undescribed error');
			}
		catch(Exception $E)
		{
			die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
		}
		$Response=json_decode($out,true);
		return $Response['response'];
	}
	
	private $errors=array(
		301=>'Moved permanently',
		400=>'Bad request',
		401=>'Unauthorized',
		403=>'Forbidden',
		404=>'Not found',
		500=>'Internal server error',
		502=>'Bad gateway',
		503=>'Service unavailable'
	);

}
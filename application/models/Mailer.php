<?php
/**
 * Created by PhpStorm.
 * User: Passika
 * Date: 27.08.14
 * Time: 17:57
 */

class Application_Model_Mailer{

	public function create($data){

		$email["body"] = "<p>{$data["username"]}</p>";
		$email["header"] = $data["header"];

		return $email;

	}

	public function contact($data){

		$email["body"] = "";
		$email["body"].= "<p>Ім'я: {$data["name"]}</p>";
		$email["body"].= "<p>Email: {$data["email"]}</p>";
		$email["body"].= "<p>Тема:{$data["theme"]}</p>";
		$email["body"].= "<p>Текст:{$data["text"]}</p>";

		$email["header"] = "Звортній зв'язок";

		return $email;

	}

	public function subscribe($data){

		$home = My_View_Helper_Url::url(1);

		$email["body"] = "<p>За сегодня на Vata.Club добавлено ({$data["num"]})";
		foreach($data["terror"] as $value){
			$value = (array)$value;
			$email["body"].= "<p><a href = '{$home}/ivan/{$value["id"]}'>Имя: {$value["name"]} Тип: {$value["type"]}</a></p>";
		}
		$email["body"].= "<p>С уважением, команда Vata.Club</p>";
		$email["body"].= "<p><a href = '{$home}/?unsubscribe={$data["unsubscribe"]}'>Отписаться от новостей</a></p>";

		$email["header"] = "Vata.Club новости за сутки";

		return $email;

	}

	public function edit($data){

		$email["body"] = "";
		$email["body"].= "<p>Porposer: {$data["proposer"]}</p>";
		$email["body"].= "<p>name: {$data["name"]}</p>";
		$email["body"].= "<p>birthdate: {$data["birthdate"]}</p>";
		$email["body"].= "<p>city: {$data["city"]}</p>";
		$email["body"].= "<p>army: {$data["army"]}</p>";
		$email["body"].= "<p>vk: {$data["vk"]}</p>";
		$email["body"].= "<p>fb: {$data["fb"]}</p>";
		$email["body"].= "<p>tw: {$data["tw"]}</p>";
		$email["body"].= "<p>status: {$data["status"]}</p>";
		$email["body"].= "<p>type: {$data["type"]}</p>";
		$email["body"].= "<p>video: {$data["video"]}</p>";
		$email["body"].= "<p>description: {$data["description"]}</p>";

		$email["header"] = "Пропозиції редагування Івана";

		return $email;

	}

	public function propose($data){

		$email["body"] = "";
		$email["body"].= "<p>Porposer: {$data["name"]}</p>";
		$email["body"].= "<p>Email: {$data["email"]}</p>";
		$email["body"].= "<p>Text: {$data["text"]}</p>";

		$email["header"] = "Пропозиції видалення";

		return $email;

	}

	public function subscribeForNews($params){

		$identity = Zend_Auth::getInstance()->getStorage()->read();
		$notifyDb    = new Application_Model_DbTable_Notify();
		$validator = new Zend_Validate_EmailAddress();
		$subscribeDb = new Application_Model_DbTable_Subscribe();

		if(isset($params["subscribe"])){
			if($validator->isValid($params["subscribe"]))$subscribeDb->createItem(array("email" => $params["subscribe"], "unsubscribe" => md5($params["subscribe"])));
		}

		$vars = array(

			"email" => $params["email"],
			"name" => $params["name"],
			"theme" => $params["theme"],
			"text" => $params["description"],

		);

		$arr = array(

			"email" => "vataclub@gmail.com",
			"vars" => json_encode($vars),
			"action" => "contact"

		);

		if(isset($params["description"]) || isset($params["email"]) && isset($identity->id))$notifyDb->createItem($arr);

	}

	public function approved($data){


		$email["body"] = "";
		$email["body"].= "<p>{$data["first_name"]} {$data["last_name"]} тепер доступний за посиланням <a href = 'http://vata.club/member/{$data["id"]}'>http://vata.club/member/{$data["id"]}</a></p>";
		$email["body"].= "<p>Дякуюємо за допомогу проекту.</p>";
		$email["body"].= "<p>З повагою, команда vata.club</p>";

		$email["header"] = "Доданий Вами {$data["first_name"]} {$data["last_name"]} пройшов модерацію";

		return $email;

	}

}
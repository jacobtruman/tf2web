<?php

/**
 * Created by PhpStorm.
 * User: jacobtruman
 * Date: 4/30/16
 * Time: 1:00 PM
 */
require_once("DBConn.class.php");

class TF2Backpack {

	public $steam_id;
	public $username;
	protected $config;
	protected $date;
	protected $schema_file = NULL;
	protected $item_schema = array();
	protected $attribute_schema = array();
	protected $origin_schema = array();
	protected $raw = NULL;
	protected $backpack = array();
	protected $item_ids = array();
	protected $quality_colors = array(
		0 => "B2B2B2", // Normal
		1 => "4D7455", // Genuine
		3 => "476291", // Vintage
		6 => "FFD700", // Unique
		7 => "70B04A", // Community
		8 => "A50F79", // Valve
		9 => "70B04A", // Self Made
		11 => "CF6A32", // Strange
		13 => "38F3AB", // Haunted
		14 => "AA0000" // Collectors
		#"8650AC", // Unusual
		#"FAFAFA", // Decorated
	);
	protected $game_id = 440;
	protected $version = "v0001";

	/**
	 * @var DBConn
	 */
	protected $db;
	protected $history = array();

	public function __construct($username) {
		$this->username = $username;
		$this->date = date("Y-m-d");
		$this->setSchemaFile($this->date);
		$this->db = new DBConn();

		$config_file = dirname(__FILE__)."/../config.json";

		$this->config = json_decode(file_get_contents($config_file), true);
		$this->steam_id = "76561198022319482";
		if(isset($this->config['admin_accounts'])) {
			if(isset($this->config['admin_accounts'][$this->username]['id'])) {
				$this->steam_id = $this->config['admin_accounts'][$this->username]['id'];
			} else {
				echo "Username \"{$this->username}\" not found; defaulting to username \"jacobtruman\"<br /><br />";
				$this->username = "jacobtruman";
			}
		}

		$this->buildSchema();
		$this->getBackpackHistory();
		$this->getBackpack();
	}

	protected function getMostRecentSchemaFile() {
		$i = 0;
		$date = $this->date;
		while(!file_exists($this->getSchemaFile($date))) {
			$i++;
			if($i > 100) {
				throw new Exception("Failed to get a schema file");
			}
			$ts = strtotime($date);
			$date = date("Y-m-d", strtotime("-1 day", $ts));
		}
		return $this->getSchemaFile($date);
	}

	protected function setSchemaFile($date) {
		$this->schema_file = $this->getSchemaFile($date);
	}

	protected function getSchemaFile($date) {
		return dirname(__FILE__)."/../assets/tf2itemschema_{$date}.json";
	}

	protected function buildSchema() {
		if(!file_exists($this->schema_file)) {
			$this->buildSchemaFile($this->schema_file);
		}
		$contents = file_get_contents($this->schema_file);
		if(!$this->isJSON($contents)) {
			unlink($this->schema_file);
			$this->schema_file = $this->getMostRecentSchemaFile();
			$this->buildSchema();
		}
		echo "Using schema file: {$this->schema_file}<br />\n";
		$schema = json_decode($contents, true);

		$items = $schema['result']['items'];
		foreach($items as $item) {
			$this->item_schema[$item['defindex']] = $item;
		}

		$attributes = $schema['result']['attributes'];
		foreach($attributes as $attribute) {
			$this->attribute_schema[$attribute['defindex']] = $attribute;
		}

		$origins = $schema['result']['originNames'];
		foreach($origins as $origin) {
			$this->origin_schema[$origin['origin']] = $origin;
		}
	}

	public function displayBackpack($timestamp = NULL) {
		if($this->getBackpack($timestamp)) {
			echo "<a href='{$this->getBackpackURL()}' target='backpack_{$this->steam_id}'>Backpack Source</a><br />";
			echo "<a href='{$this->getSchemaURL()}' target='tf2_schema'>Schema Source</a><br /><br />";
			$quests = array();
			$new_quests = array();
			$items_not_in_backpack = array();
			$items_in_backpack = array();

			foreach($this->backpack as $item) {
				$item_schema = $this->item_schema[$item['defindex']];
				$item_slot = isset($item_schema['item_slot']) ? $item_schema['item_slot'] : '';
				$inventory_bin = decbin($item['inventory']);
				if(isset($inventory_bin[1])) {
					$not_in_backpack = $inventory_bin[1];
				} else {
					$not_in_backpack = 1;
				}
				if($not_in_backpack) {
					if($item_slot == 'quest') {
						$new_quests[] = $item;
					} else {
						$items_not_in_backpack[] = $item;
					}
				} else {
					if($item_slot == 'quest') {
						$quests[] = $item;
					} else {
						$position = $item['inventory'] & 65535;
						$items_in_backpack[$position] = $item;
					}
				}
			}

			$this->displayItems($new_quests, "New quests");
			$this->displayItems($quests, "Existing quests");
			$this->displayItems($items_not_in_backpack, "Items NOT in backpack");

			ksort($items_in_backpack);
			$this->displayItems($items_in_backpack, "Items in backpack");
		}
	}

	protected function displayItems($items, $title) {
		if(count($items) > 0) {
			echo "{$title}<br />";
			$i = 0;
			foreach($items as $key=>$item) {
				$id = $item['id'];
				$item_schema = $this->item_schema[$item['defindex']];
				$title_text = $this->getTitleText($item, $item_schema);
				$i++;
				$color = isset($this->quality_colors[$item['quality']]) ? $this->quality_colors[$item['quality']] : "C0C0C0";
				$border = "border:4px solid #{$color}";
				$params = array(
					"image_url" => $item_schema['image_url'],
					"defindex" => $item['defindex']//,
					//"dimensions" => true
				);
				echo "<a href='#' class='tooltip' id='tooltip_{$id}' title='{$title_text}'><img width='128' height='128' style='margin:2px; {$border};' src='get_image.php?p=".base64_encode(json_encode($params))."' /></a>";
			}
			echo "<br /><br />";
		}
	}

	protected function getTitleText($item, $item_schema) {
		$title_text = array();
		//$title_text[] = "{$item['defindex']}";
		$title_text[] = "<h2>{$item_schema['name']}</h2>";
		if(isset($item['origin']) && isset($this->origin_schema[$item['origin']])) {
			$title_text[] = "<b>Origin:</b> {$this->origin_schema[$item['origin']]['name']}";
		}
		if(isset($item['attributes'])) {
			foreach($item['attributes'] as $attribute) {
				$title_text[] = "<b>{$this->attribute_schema[$attribute['defindex']]['name']}:</b> {$attribute['value']}";
			}
		}
		return nl2br(htmlspecialchars(implode(PHP_EOL, $title_text), ENT_QUOTES));
	}

	protected function buildSchemaFile($file) {
		echo "Building schema file: {$file}<br />\n";

		// create curl resource
		$ch = curl_init();

		$url = $this->getSchemaURL();

		// set url
		curl_setopt($ch, CURLOPT_URL, $url);

		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		// $output contains the output string
		$output = curl_exec($ch);
		if($this->isJSON($output)) {
			file_put_contents($file, $output);
		} else {
			echo "Building schema file failed: {$output} is not a valid JSON string<br /><br />";
		}

		// close curl resource to free up system resources
		curl_close($ch);
	}

	protected function getBackpack($timestamp = NULL) {
		if($timestamp !== NULL && isset($this->history[$timestamp])) {
			$this->backpack = json_decode($this->history[$timestamp], true)['result']['items'];
		}
		if(empty($this->backpack)) {
			// create curl resource
			$ch = curl_init();

			$url = $this->getBackpackURL();

			// set url
			curl_setopt($ch, CURLOPT_URL, $url);

			//return the transfer as a string
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$this->raw = curl_exec($ch);

			// $output contains the output string
			$output = json_decode($this->raw, true);

			// close curl resource to free up system resources
			curl_close($ch);
			if(isset($output['result']['items'])) {
				$this->backpack = $output['result']['items'];
				$this->recordBackpack();
			} else {
				echo "There was a problem loading the backpack :(";
				return false;
			}
		}
		return true;
	}

	protected function getBackpackURL() {
		return "http://api.steampowered.com/IEconItems_{$this->game_id}/GetPlayerItems/{$this->version}/?key={$this->config['web_api_key']}&steamID={$this->steam_id}";
	}

	protected function getSchemaURL() {
		return "http://api.steampowered.com/IEconItems_{$this->game_id}/GetSchema/{$this->version}/?key={$this->config['web_api_key']}";
	}

	protected function recordBackpack() {
		$this->getBackpackHistory();
		// to flatten out the json string
		$raw = json_encode(json_decode($this->raw, true));
		if(!in_array($raw, $this->history)) {
			$ts = date("Y-m-d H:i:s");
			$sql = "INSERT INTO tf2_backpacks SET
				username = '".$this->db->real_escape_string($this->username)."',
				timestamp = '".$this->db->real_escape_string($ts)."',
				backpack_json = '".$this->db->real_escape_string($raw)."'";
			if($this->db->query($sql)) {
				$this->history[$ts] = $this->raw;
			}
		} else {
			// backpack state already recorded
		}
	}

	protected function getBackpackHistory() {
		if(empty($this->history)) {
			$sql = "SELECT * FROM tf2_backpacks WHERE username = '{$this->username}' ORDER BY timestamp LIMIT 10";

			$res = $this->db->query($sql);
			while($row = $res->fetch_assoc()) {
				$this->history[$row['timestamp']] = $row['backpack_json'];
			}
		}
	}

	public function getBackpackHistoryList() {
		return array_keys($this->history);
	}

	public function displaySchema() {
		foreach($this->item_schema as $item) {
			echo "<div>\n";
			echo "Name: {$item['name']}<br />\n";
			echo "defindex: {$item['defindex']}<br />\n";
			echo "Class: {$item['item_class']}<br />\n";
			echo "Slot: {$item['item_slot']}<br />\n";
			echo "<img src='{$item['image_url']}' />\n";
			echo "</div>\n";
		}
	}

	public function getBackpackRaw() {
		return $this->raw;
	}

	protected function isJSON($string) {
		if(!is_string($string) || json_decode($string) === NULL) {
			return false;
		}
		return true;
	}
}
<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once __DIR__ . '/../../../../core/php/core.inc.php';

/*
 * Plugin M5Dial : un equipement Jeedom par bouton M5Dial.
 *
 * Topics utilises (voir CLAUDE.md du firmware) :
 *   m5dial/<nom>/status        bouton -> "online" / "offline" (retenu, LWT)
 *   m5dial/<nom>/info          bouton -> JSON (version, ip, rssi, ...) retenu
 *   m5dial/<nom>/config        -> bouton : configuration JSON retenue
 *   m5dial/<nom>/config/erreur bouton -> erreur si la config est invalide
 *
 * Le logicalId de l'equipement est le nom du bouton (device.name du
 * config.json local du bouton, ex. "m5dial-test").
 */
class m5dial extends eqLogic {

	const TOPIC_RACINE = 'm5dial';

	// Commandes creees automatiquement sur chaque equipement :
	// logicalId => [nom, type, sous-type, unite, historisee]
	private static $_commandes = array(
		'online'        => array('En ligne', 'info', 'binary', '', 1),
		'version'       => array('Version firmware', 'info', 'string', '', 0),
		'ip'            => array('Adresse IP', 'info', 'string', '', 0),
		'rssi'          => array('Signal Wi-Fi', 'info', 'numeric', 'dBm', 1),
		'config_source' => array('Source de la configuration', 'info', 'string', '', 0),
		'uptime'        => array('Durée de fonctionnement', 'info', 'numeric', 'min', 0),
		'memoire'       => array('Mémoire libre', 'info', 'numeric', 'Ko', 1),
		'config_erreur' => array('Dernière erreur de configuration', 'info', 'string', '', 0),
		'envoyer_config'=> array('Envoyer la configuration', 'action', 'other', '', 0),
		'config_locale' => array('Revenir à la configuration locale', 'action', 'other', '', 0),
	);

	/* ---------------------------------------------------------------- */
	/* Lien avec MQTT Manager                                            */
	/* ---------------------------------------------------------------- */

	// Demande a MQTT Manager de transmettre au plugin tout ce qui arrive
	// sous m5dial/... (appelee a l'installation et a chaque mise a jour).
	public static function enregistrerTopic() {
		if (!class_exists('mqtt2')) {
			log::add(__CLASS__, 'error', __('Le plugin MQTT Manager (mqtt2) est absent', __FILE__));
			return;
		}
		mqtt2::addPluginTopic(__CLASS__, self::TOPIC_RACINE);
		log::add(__CLASS__, 'info', __('Abonnement au topic', __FILE__) . ' ' . self::TOPIC_RACINE);
	}

	// Appelee par MQTT Manager pour chaque message recu sous m5dial/...
	// $_datas = array('m5dial' => array('<nom>' => array('status' => ..., 'info' => array(...))))
	public static function handleMqttMessage($_datas) {
		log::add(__CLASS__, 'debug', 'MQTT : ' . json_encode($_datas, JSON_UNESCAPED_UNICODE));
		if (!isset($_datas[self::TOPIC_RACINE]) || !is_array($_datas[self::TOPIC_RACINE])) {
			return;
		}
		foreach ($_datas[self::TOPIC_RACINE] as $nom => $topics) {
			if (!is_array($topics)) {
				continue;
			}
			$eqLogic = self::byLogicalId($nom, __CLASS__);
			if (!is_object($eqLogic)) {
				// Nouveau bouton : creation automatique, seulement s'il se
				// presente lui-meme (status/info), pas sur un simple message de
				// configuration publie par quelqu'un d'autre.
				if (!isset($topics['status']) && !isset($topics['info'])) {
					continue;
				}
				$eqLogic = new m5dial();
				$eqLogic->setEqType_name(__CLASS__);
				$eqLogic->setLogicalId($nom);
				$eqLogic->setName($nom);
				$eqLogic->setIsEnable(1);
				$eqLogic->setIsVisible(1);
				$eqLogic->save();
				log::add(__CLASS__, 'info', __('Nouveau bouton détecté', __FILE__) . ' : ' . $nom);
			}
			if ($eqLogic->getIsEnable() != 1) {
				continue;
			}
			$eqLogic->traiterMessages($topics);
		}
	}

	// Met a jour les commandes info a partir des topics recus.
	public function traiterMessages($_topics) {
		if (isset($_topics['status']) && !is_array($_topics['status'])) {
			$this->checkAndUpdateCmd('online', ($_topics['status'] == 'online') ? 1 : 0);
		}
		if (isset($_topics['info']) && is_array($_topics['info'])) {
			$info = $_topics['info'];
			$correspondance = array(
				'version'        => 'version',
				'ip'             => 'ip',
				'rssi'           => 'rssi',
				'config'         => 'config_source',
				'uptimeMin'      => 'uptime',
				'memoireLibreKo' => 'memoire',
			);
			foreach ($correspondance as $cle => $logicalId) {
				if (isset($info[$cle])) {
					$this->checkAndUpdateCmd($logicalId, $info[$cle]);
				}
			}
			// Infos utiles conservees dans la configuration de l'equipement.
			foreach (array('clientId', 'mac', 'ssid') as $cle) {
				if (isset($info[$cle]) && $this->getConfiguration($cle) != $info[$cle]) {
					$this->setConfiguration($cle, $info[$cle]);
					$this->save(true);
				}
			}
		}
		// m5dial/<nom>/config/erreur : le topic "config" contient alors un
		// tableau avec la cle "erreur" (le JSON de config lui-meme est ignore).
		if (isset($_topics['config']) && is_array($_topics['config']) && isset($_topics['config']['erreur'])) {
			$this->checkAndUpdateCmd('config_erreur', $_topics['config']['erreur']);
		}
	}

	/* ---------------------------------------------------------------- */
	/* Envoi de la configuration au bouton                               */
	/* ---------------------------------------------------------------- */

	public function getTopicConfig() {
		return self::TOPIC_RACINE . '/' . $this->getLogicalId() . '/config';
	}

	// Publie la configuration JSON de l'equipement (message retenu). La
	// section "device" est retiree : le bouton l'ignore de toute facon (il
	// garde son nom et son broker locaux).
	// Jeedom transforme automatiquement un texte JSON en tableau a la
	// sauvegarde : la configuration peut donc etre lue sous les deux formes.
	// Retourne le tableau, ou null si vide ; exception si JSON invalide.
	public static function decoderConfig($_valeur) {
		if (is_array($_valeur)) {
			return (count($_valeur) > 0) ? $_valeur : null;
		}
		$texte = trim((string) $_valeur);
		if ($texte == '') {
			return null;
		}
		$config = json_decode($texte, true);
		if (!is_array($config)) {
			throw new Exception(__('La configuration n\'est pas un JSON valide', __FILE__) . ' : ' . json_last_error_msg());
		}
		return $config;
	}

	// $_texte : contenu du champ de la page (envoye directement par le bouton
	// "Envoyer", pour ne pas dependre d'une sauvegarde prealable). Il est
	// aussi enregistre dans l'equipement.
	public function envoyerConfiguration($_texte = null) {
		if ($this->getLogicalId() == '') {
			throw new Exception(__('Renseignez le nom du bouton avant d\'envoyer la configuration', __FILE__));
		}
		if ($_texte !== null) {
			$config = self::decoderConfig($_texte);
			if ($config !== null) {
				$this->setConfiguration('configJson', json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
				$this->save(true);
			}
		} else {
			$config = self::decoderConfig($this->getConfiguration('configJson', ''));
		}
		if ($config === null) {
			throw new Exception(__('La configuration est vide', __FILE__));
		}
		unset($config['device']);
		$message = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		if (strlen($message) > 8000) {
			throw new Exception(__('Configuration trop grande pour le bouton (8 Ko maximum)', __FILE__) . ' : ' . strlen($message) . ' ' . __('octets', __FILE__));
		}
		mqtt2::publish($this->getTopicConfig(), $message, array('retain' => true, 'qos' => 1));
		$this->checkAndUpdateCmd('config_erreur', '');
		log::add(__CLASS__, 'info', $this->getHumanName() . ' ' . __('configuration envoyée', __FILE__) . ' (' . strlen($message) . ' ' . __('octets', __FILE__) . ')');
	}

	// Message vide retenu : le bouton efface sa configuration MQTT et revient
	// a son config.json local.
	public function revenirConfigurationLocale() {
		if ($this->getLogicalId() == '') {
			throw new Exception(__('Renseignez le nom du bouton', __FILE__));
		}
		mqtt2::publish($this->getTopicConfig(), '', array('retain' => true, 'qos' => 1));
		log::add(__CLASS__, 'info', $this->getHumanName() . ' ' . __('retour à la configuration locale demandé', __FILE__));
	}

	/* ---------------------------------------------------------------- */
	/* Cycle de vie de l'equipement                                      */
	/* ---------------------------------------------------------------- */

	public function preSave() {
		if ($this->getLogicalId() != '') {
			$autre = self::byLogicalId($this->getLogicalId(), __CLASS__);
			if (is_object($autre) && $autre->getId() != $this->getId()) {
				throw new Exception(__('Un autre équipement utilise déjà ce nom de bouton', __FILE__) . ' : ' . $this->getLogicalId());
			}
		}
		// Toujours stocker la configuration sous forme de texte lisible (et non
		// du tableau produit par Jeedom), pour l'afficher telle quelle.
		$config = self::decoderConfig($this->getConfiguration('configJson', ''));
		$this->setConfiguration('configJson', ($config === null) ? '' : json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	}

	// Cree les commandes manquantes (aussi appelee apres une mise a jour du plugin).
	public function postSave() {
		$ordre = 0;
		foreach (self::$_commandes as $logicalId => $def) {
			$cmd = $this->getCmd(null, $logicalId);
			if (!is_object($cmd)) {
				$cmd = new m5dialCmd();
				$cmd->setLogicalId($logicalId);
				$cmd->setEqLogic_id($this->getId());
				$cmd->setName(__($def[0], __FILE__));
				$cmd->setType($def[1]);
				$cmd->setSubType($def[2]);
				$cmd->setUnite($def[3]);
				$cmd->setIsHistorized($def[4]);
				$cmd->setOrder($ordre);
				$cmd->save();
			}
			$ordre++;
		}
	}
}

class m5dialCmd extends cmd {

	public function dontRemoveCmd() {
		return true;
	}

	public function execute($_options = array()) {
		$eqLogic = $this->getEqLogic();
		switch ($this->getLogicalId()) {
			case 'envoyer_config':
				$eqLogic->envoyerConfiguration();
				break;
			case 'config_locale':
				$eqLogic->revenirConfigurationLocale();
				break;
		}
	}
}

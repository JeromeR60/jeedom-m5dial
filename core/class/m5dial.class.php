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
		'maj_etat'      => array('État de la mise à jour', 'info', 'string', '', 0),
		'maj_firmware'  => array('Mettre à jour le firmware', 'action', 'other', '', 0),
		'maj_dispo'     => array('Mise à jour disponible', 'info', 'binary', '', 0),
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
				// Bouton inconnu sous ce nom. On attend son message info (retenu,
				// publie a chaque connexion) qui contient l'adresse MAC : un simple
				// status ne suffit pas, sinon un bouton renomme ou reinitialise
				// creerait un doublon avant d'etre reconnu.
				$info = (isset($topics['info']) && is_array($topics['info'])) ? $topics['info'] : null;
				if ($info === null) {
					continue;
				}
				$mac = isset($info['mac']) ? self::normaliserMac($info['mac']) : '';
				$eqLogic = ($mac != '') ? self::parMac($mac) : null;
				if (is_object($eqLogic)) {
					// Bouton deja connu (meme MAC) revenu sous un autre nom :
					// on lui rend son equipement et sa configuration.
					$eqLogic->rattacher($nom);
				} else {
					$eqLogic = new m5dial();
					$eqLogic->setEqType_name(__CLASS__);
					$eqLogic->setLogicalId($nom);
					$eqLogic->setName($nom);
					$eqLogic->setIsEnable(1);
					$eqLogic->setIsVisible(1);
					if ($mac != '') {
						$eqLogic->setConfiguration('mac', $mac);
					}
					$eqLogic->save();
					log::add(__CLASS__, 'info', __('Nouveau bouton détecté', __FILE__) . ' : ' . $nom . ($mac != '' ? ' (' . $mac . ')' : ''));
				}
			}
			if ($eqLogic->getIsEnable() != 1) {
				continue;
			}
			$eqLogic->traiterMessages($topics);
		}
	}

	public static function normaliserMac($_mac) {
		return strtoupper(preg_replace('/[^0-9A-Fa-f]/', '', (string) $_mac));
	}

	// Equipement deja associe a cette adresse MAC (ou null).
	public static function parMac($_mac) {
		$mac = self::normaliserMac($_mac);
		if ($mac == '') {
			return null;
		}
		foreach (self::byType(__CLASS__) as $eqLogic) {
			if (self::normaliserMac($eqLogic->getConfiguration('mac', '')) == $mac) {
				return $eqLogic;
			}
		}
		return null;
	}

	// Le bouton revient sous un nouveau nom (reinitialisation, renommage) :
	// l'equipement prend ce nom, l'ancien topic de configuration est vide et
	// la configuration est renvoyee sur le nouveau.
	public function rattacher($_nouveauNom) {
		$ancien = $this->getLogicalId();
		if ($ancien == $_nouveauNom) {
			return;
		}
		$this->setLogicalId($_nouveauNom);
		$this->save(true);
		log::add(__CLASS__, 'info', $this->getHumanName() . ' ' . __('reconnu par son adresse MAC', __FILE__) . ' : ' . $ancien . ' -> ' . $_nouveauNom);
		if ($ancien != '' && class_exists('mqtt2')) {
			mqtt2::publish(self::TOPIC_RACINE . '/' . $ancien . '/config', '', array('retain' => true, 'qos' => 1));
		}
		try {
			if (self::decoderConfig($this->getConfiguration('configJson', '')) !== null) {
				$this->envoyerConfiguration();
			}
		} catch (Exception $e) {
			log::add(__CLASS__, 'warning', $this->getHumanName() . ' ' . __('configuration non renvoyée', __FILE__) . ' : ' . $e->getMessage());
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
				$valeur = isset($info[$cle]) ? (($cle == 'mac') ? self::normaliserMac($info[$cle]) : $info[$cle]) : null;
				if ($valeur !== null && $valeur != '' && $this->getConfiguration($cle) != $valeur) {
					$this->setConfiguration($cle, $valeur);
					$this->save(true);
				}
			}
			if (isset($info['version'])) {
				$this->majDisponible();
			}
		}
		// m5dial/<nom>/ota/etat : avancement d'une mise a jour du firmware.
		if (isset($_topics['ota']) && is_array($_topics['ota']) && isset($_topics['ota']['etat'])) {
			$this->checkAndUpdateCmd('maj_etat', $_topics['ota']['etat']);
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
	/* Appairage des nouveaux boutons (core/php/appairage.php)           */
	/* ---------------------------------------------------------------- */

	const APPAIRAGE_DUREE = 900; // une demande expire au bout de 15 min sans nouvelle

	// Demandes en cours, indexees par adresse MAC (sans les demandes expirees).
	public static function lireAppairages() {
		$liste = config::byKey('appairages', __CLASS__, array());
		if (!is_array($liste)) {
			$liste = array();
		}
		foreach ($liste as $mac => $demande) {
			if (time() - intval($demande['date']) > self::APPAIRAGE_DUREE) {
				unset($liste[$mac]);
			}
		}
		return $liste;
	}

	private static function ecrireAppairages($_liste) {
		config::save('appairages', $_liste, __CLASS__);
	}

	// Appelee par le bouton (page publique). Retourne la reponse a lui envoyer.
	public static function demandeAppairage($_mac, $_code, $_nom, $_version, $_ip) {
		$liste = self::lireAppairages();
		if (isset($liste[$_mac]) && $liste[$_mac]['code'] == $_code) {
			if ($liste[$_mac]['etat'] == 'accepte') {
				// Les identifiants ne sont remis qu'a l'adresse IP qui a fait la
				// demande acceptee (meme MAC, meme code, meme IP).
				if ($_ip != '' && isset($liste[$_mac]['ip']) && $liste[$_mac]['ip'] != '' && $liste[$_mac]['ip'] != $_ip) {
					log::add(__CLASS__, 'warning', __('Appairage : demande acceptée mais adresse IP différente, identifiants non transmis', __FILE__) . ' (' . $_mac . ', ' . $_ip . ')');
					return array('etat' => 'attente');
				}
				unset($liste[$_mac]);
				self::ecrireAppairages($liste);
				log::add(__CLASS__, 'info', __('Identifiants transmis au bouton', __FILE__) . ' ' . $_nom . ' (' . $_mac . ')');
				return array_merge(array('etat' => 'accepte'), self::identifiantsBoutons());
			}
			if ($liste[$_mac]['etat'] == 'refuse') {
				unset($liste[$_mac]);
				self::ecrireAppairages($liste);
				return array('etat' => 'refuse');
			}
			$liste[$_mac]['date'] = time();
			$liste[$_mac]['ip'] = $_ip;
			self::ecrireAppairages($liste);
			return array('etat' => 'attente');
		}
		if (!isset($liste[$_mac]) && count($liste) >= 10) {
			return array('etat' => 'erreur', 'message' => 'trop de demandes en cours');
		}
		$liste[$_mac] = array(
			'mac' => $_mac,
			'code' => $_code,
			'nom' => $_nom,
			'version' => $_version,
			'ip' => $_ip,
			'date' => time(),
			'debut' => date('H:i:s'),
			'etat' => 'attente',
		);
		self::ecrireAppairages($liste);
		log::add(__CLASS__, 'info', __('Demande d\'appairage', __FILE__) . ' : ' . $_nom . ' (' . $_mac . ', ' . $_ip . '), code ' . $_code);
		return array('etat' => 'attente');
	}

	public static function reponseAppairage($_mac, $_accepte) {
		$liste = self::lireAppairages();
		if (!isset($liste[$_mac])) {
			throw new Exception(__('Demande introuvable ou expirée', __FILE__));
		}
		if ($_accepte) {
			$infos = self::identifiantsBoutons();
			if ($infos['utilisateur'] == '') {
				throw new Exception(__('Choisissez l\'utilisateur MQTT des boutons dans la configuration du plugin', __FILE__));
			}
		}
		$liste[$_mac]['etat'] = $_accepte ? 'accepte' : 'refuse';
		self::ecrireAppairages($liste);
		log::add(__CLASS__, 'info', __('Appairage', __FILE__) . ' ' . $liste[$_mac]['nom'] . ' : ' . ($_accepte ? __('accepté', __FILE__) : __('refusé', __FILE__)));
	}

	// Utilisateurs MQTT definis dans MQTT Manager (lignes "utilisateur:motdepasse").
	public static function utilisateursMqtt() {
		$utilisateurs = array();
		if (!class_exists('mqtt2')) {
			return $utilisateurs;
		}
		foreach (explode("\n", config::byKey('mqtt::password', 'mqtt2', '')) as $ligne) {
			$parts = explode(':', trim($ligne), 2);
			if (count($parts) == 2 && $parts[0] != '') {
				$utilisateurs[$parts[0]] = $parts[1];
			}
		}
		return $utilisateurs;
	}

	// Mot de passe des mises a jour (ArduinoOTA), genere une fois par le plugin.
	public static function motDePasseOta() {
		$mdp = config::byKey('ota_mdp', __CLASS__, '');
		if ($mdp == '') {
			$mdp = config::genKey(16);
			config::save('ota_mdp', $mdp, __CLASS__);
		}
		return $mdp;
	}

	// Identifiants transmis aux boutons lors de l'appairage.
	public static function identifiantsBoutons() {
		$hote = network::getNetworkAccess('internal', 'ip');
		$port = 1883;
		if (class_exists('mqtt2')) {
			$infos = mqtt2::getFormatedInfos();
			if (isset($infos['ip']) && $infos['ip'] != '' && strpos($infos['ip'], '127.') !== 0) {
				$hote = $infos['ip'];
			}
			if (isset($infos['port']) && intval($infos['port']) > 0) {
				$port = intval($infos['port']);
			}
		}
		$utilisateurs = self::utilisateursMqtt();
		$utilisateur = config::byKey('mqtt_utilisateur', __CLASS__, '');
		if ($utilisateur == '' || !isset($utilisateurs[$utilisateur])) {
			$utilisateur = isset($utilisateurs['m5dial']) ? 'm5dial' : (count($utilisateurs) > 0 ? array_keys($utilisateurs)[0] : '');
		}
		return array(
			'hote' => $hote,
			'port' => $port,
			'utilisateur' => $utilisateur,
			'mdp' => ($utilisateur != '') ? $utilisateurs[$utilisateur] : '',
			'ota' => self::motDePasseOta(),
		);
	}

	/* ---------------------------------------------------------------- */
	/* Mise a jour du firmware depuis Jeedom                             */
	/* ---------------------------------------------------------------- */
	// Le firmware.bin (genere par PlatformIO : .pio/build/m5dial/firmware.bin)
	// est depose dans plugins/m5dial/data/ ; le bouton le telecharge en HTTP.

	public static function dossierFirmware() {
		return __DIR__ . '/../../data';
	}

	// Informations sur le firmware depose : version, taille, date (ou null).
	public static function infoFirmware() {
		$fichier = self::dossierFirmware() . '/firmware.json';
		if (!file_exists($fichier) || !file_exists(self::dossierFirmware() . '/firmware.bin')) {
			return null;
		}
		$info = json_decode(file_get_contents($fichier), true);
		return is_array($info) ? $info : null;
	}

	// Verifie et enregistre un firmware envoye depuis la page du plugin.
	public static function enregistrerFirmware($_cheminTemp, $_nomOrigine) {
		if (strtolower(pathinfo($_nomOrigine, PATHINFO_EXTENSION)) != 'bin') {
			throw new Exception(__('Le fichier doit être un .bin (firmware.bin de PlatformIO)', __FILE__));
		}
		$taille = filesize($_cheminTemp);
		if ($taille < 100000 || $taille > 3300000) {
			throw new Exception(__('Taille de fichier inattendue pour un firmware M5Dial', __FILE__) . ' : ' . $taille . ' ' . __('octets', __FILE__));
		}
		$contenu = file_get_contents($_cheminTemp);
		if (ord($contenu[0]) != 0xE9) {
			throw new Exception(__('Ce fichier n\'est pas une image de firmware ESP32', __FILE__));
		}
		if (!preg_match('/M5DIAL_FW_VERSION=([A-Za-z0-9._-]{1,20});/', $contenu, $m)) {
			throw new Exception(__('Version introuvable dans le fichier : ce n\'est pas un firmware M5Dial (version 2.1 ou plus)', __FILE__));
		}
		$dossier = self::dossierFirmware();
		if (!file_exists($dossier)) {
			mkdir($dossier, 0775, true);
		}
		if (!move_uploaded_file($_cheminTemp, $dossier . '/firmware.bin') && !rename($_cheminTemp, $dossier . '/firmware.bin')) {
			throw new Exception(__('Impossible d\'enregistrer le fichier dans', __FILE__) . ' ' . $dossier);
		}
		$info = array(
			'version' => $m[1],
			'taille' => $taille,
			'md5' => md5($contenu),
			'date' => date('Y-m-d H:i:s'),
		);
		file_put_contents($dossier . '/firmware.json', json_encode($info));
		log::add(__CLASS__, 'info', __('Firmware déposé', __FILE__) . ' : ' . $m[1] . ' (' . $taille . ' ' . __('octets', __FILE__) . ')');
		return $info;
	}

	/* ---- Firmware publie sur GitHub (releases du depot du plugin) ---- */
	// Une release GitHub contient un fichier firmware.bin (compile SANS mot
	// de passe : environnement PlatformIO m5dial_release). Jeedom le
	// telecharge puis le sert aux boutons comme un fichier depose a la main.

	const GITHUB_DEPOT = 'JeromeR60/jeedom-m5dial';

	private static function requeteGithub($_url) {
		$ch = curl_init($_url);
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_USERAGENT => 'jeedom-m5dial',
			CURLOPT_HTTPHEADER => array('Accept: application/vnd.github+json'),
		));
		$reponse = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$erreur = curl_error($ch);
		curl_close($ch);
		if ($reponse === false || $code != 200) {
			throw new Exception(__('GitHub injoignable ou réponse inattendue', __FILE__) . ' (' . ($erreur != '' ? $erreur : 'HTTP ' . $code) . ')');
		}
		return $reponse;
	}

	// Derniere release : version (tag), date, URL du firmware.bin joint.
	public static function derniereReleaseGithub() {
		$depot = config::byKey('github_depot', __CLASS__, self::GITHUB_DEPOT);
		$release = json_decode(self::requeteGithub('https://api.github.com/repos/' . $depot . '/releases/latest'), true);
		if (!is_array($release) || !isset($release['tag_name'])) {
			throw new Exception(__('Aucune release trouvée sur GitHub', __FILE__) . ' (' . $depot . ')');
		}
		$url = '';
		foreach ((isset($release['assets']) ? $release['assets'] : array()) as $asset) {
			if (substr(strtolower($asset['name']), -4) == '.bin') {
				$url = $asset['browser_download_url'];
				break;
			}
		}
		if ($url == '') {
			throw new Exception(__('La release', __FILE__) . ' ' . $release['tag_name'] . ' ' . __('ne contient pas de fichier .bin', __FILE__));
		}
		$info = array(
			'tag' => $release['tag_name'],
			'nom' => isset($release['name']) ? $release['name'] : '',
			'date' => isset($release['published_at']) ? substr($release['published_at'], 0, 10) : '',
			'url' => $url,
		);
		config::save('github_derniere', $info, __CLASS__);
		return $info;
	}

	// Telecharge le firmware de la derniere release et le depose dans le plugin.
	public static function telechargerFirmwareGithub() {
		$info = self::derniereReleaseGithub();
		$contenu = self::requeteGithub($info['url']);
		$temp = tempnam(jeedom::getTmpFolder(__CLASS__), 'fw');
		file_put_contents($temp, $contenu);
		try {
			$resultat = self::enregistrerFirmware($temp, 'firmware.bin');
		} finally {
			if (file_exists($temp)) {
				unlink($temp);
			}
		}
		$resultat['source'] = 'GitHub ' . $info['tag'];
		foreach (self::byType(__CLASS__) as $eqLogic) {
			$eqLogic->majDisponible();
		}
		log::add(__CLASS__, 'info', __('Firmware récupéré depuis GitHub', __FILE__) . ' : ' . $info['tag'] . ' (v' . $resultat['version'] . ')');
		return $resultat;
	}

	// Verifie et consomme un jeton de telechargement (core/php/firmware.php).
	// Le jeton reste valable 15 min pour permettre les 3 essais du bouton.
	public static function jetonValide($_jeton) {
		$jetons = config::byKey('ota_jetons', __CLASS__, array());
		if (!is_array($jetons) || $_jeton == '' || !isset($jetons[$_jeton])) {
			return false;
		}
		return $jetons[$_jeton] >= time();
	}

	/* ---- Mises a jour disponibles (verification quotidienne) ---- */

	// Version du firmware de la derniere release connue ("firmware-2.3" -> "2.3").
	public static function versionDerniereRelease() {
		$info = config::byKey('github_derniere', __CLASS__, array());
		if (!is_array($info) || !isset($info['tag'])) {
			return '';
		}
		return preg_replace('/^[^0-9]*/', '', $info['tag']);
	}

	// Met a jour la commande "Mise a jour disponible" de ce bouton.
	public function majDisponible($_versionRelease = null) {
		$release = ($_versionRelease === null) ? self::versionDerniereRelease() : $_versionRelease;
		$cmd = $this->getCmd('info', 'version');
		$version = is_object($cmd) ? (string) $cmd->execCmd() : '';
		$dispo = ($release != '' && $version != '' && version_compare($release, $version, '>')) ? 1 : 0;
		$this->checkAndUpdateCmd('maj_dispo', $dispo);
		return $dispo == 1;
	}

	// Appelee chaque nuit (cronDaily) : interroge GitHub, signale les boutons
	// en retard et, si l'option est cochee, les met a jour.
	public static function verifierMisesAJour() {
		try {
			self::derniereReleaseGithub();
		} catch (Exception $e) {
			log::add(__CLASS__, 'warning', __('Vérification des mises à jour impossible', __FILE__) . ' : ' . $e->getMessage());
			return;
		}
		$release = self::versionDerniereRelease();
		$enRetard = array();
		foreach (self::byType(__CLASS__, true) as $eqLogic) {
			if ($eqLogic->majDisponible($release)) {
				$enRetard[] = $eqLogic;
			}
		}
		log::add(__CLASS__, 'info', __('Dernier firmware publié', __FILE__) . ' : ' . $release . ', ' . count($enRetard) . ' ' . __('bouton(s) à mettre à jour', __FILE__));
		if (count($enRetard) == 0) {
			return;
		}
		// Un seul message par nouvelle version.
		if (config::byKey('notif_version', __CLASS__, '') != $release) {
			$noms = array();
			foreach ($enRetard as $eqLogic) {
				$noms[] = $eqLogic->getName();
			}
			message::add(__CLASS__, __('Firmware M5Dial', __FILE__) . ' ' . $release . ' ' . __('disponible pour', __FILE__) . ' : ' . implode(', ', $noms), '', 'm5dial_maj_' . $release);
			config::save('notif_version', $release, __CLASS__);
		}
		if (config::byKey('maj_auto', __CLASS__, 0) != 1) {
			return;
		}
		try {
			$depose = self::infoFirmware();
			if ($depose === null || version_compare($release, $depose['version'], '>')) {
				self::telechargerFirmwareGithub();
			}
		} catch (Exception $e) {
			log::add(__CLASS__, 'error', __('Mise à jour automatique : téléchargement impossible', __FILE__) . ' : ' . $e->getMessage());
			return;
		}
		foreach ($enRetard as $eqLogic) {
			$cmd = $eqLogic->getCmd('info', 'online');
			if (!is_object($cmd) || $cmd->execCmd() != 1) {
				continue;
			}
			try {
				$eqLogic->lancerMajFirmware();
			} catch (Exception $e) {
				log::add(__CLASS__, 'error', $eqLogic->getHumanName() . ' ' . $e->getMessage());
			}
		}
	}

	public static function cronDaily() {
		self::verifierMisesAJour();
	}

	// Demande au bouton de telecharger le firmware depose (message non retenu).
	public function lancerMajFirmware() {
		if ($this->getLogicalId() == '') {
			throw new Exception(__('Renseignez le nom du bouton', __FILE__));
		}
		$info = self::infoFirmware();
		if ($info === null) {
			throw new Exception(__('Aucun firmware déposé : envoyez d\'abord un firmware.bin depuis la page du plugin', __FILE__));
		}
		$base = network::getNetworkAccess('internal');
		if (strpos($base, 'http://') !== 0) {
			throw new Exception(__('L\'adresse interne de Jeedom doit être en http:// (Réglages > Système > Configuration > Réseaux)', __FILE__) . ' : ' . $base);
		}
		// Le dossier data/ est interdit par Apache (403) : le fichier est servi
		// par core/php/firmware.php, protege par la cle API du plugin.
		// Jeton a usage unique (valable 15 min) plutot que la cle API du plugin :
		// le message MQTT peut etre lu par tout client du broker.
		$jeton = config::genKey(32);
		$jetons = config::byKey('ota_jetons', __CLASS__, array());
		if (!is_array($jetons)) {
			$jetons = array();
		}
		foreach ($jetons as $j => $expire) {
			if ($expire < time()) {
				unset($jetons[$j]);
			}
		}
		$jetons[$jeton] = time() + 900;
		config::save('ota_jetons', $jetons, __CLASS__);
		$url = rtrim($base, '/') . '/plugins/m5dial/core/php/firmware.php?jeton=' . $jeton;
		$message = json_encode(array('url' => $url, 'version' => $info['version']), JSON_UNESCAPED_SLASHES);
		mqtt2::publish(self::TOPIC_RACINE . '/' . $this->getLogicalId() . '/ota', $message, array('retain' => false, 'qos' => 1));
		$this->checkAndUpdateCmd('maj_etat', __('demandée', __FILE__) . ' (' . $info['version'] . ')');
		log::add(__CLASS__, 'info', $this->getHumanName() . ' ' . __('mise à jour demandée vers', __FILE__) . ' ' . $info['version']);
	}

	/* ---------------------------------------------------------------- */
	/* Cycle de vie de l'equipement                                      */
	/* ---------------------------------------------------------------- */

	// Image des vignettes : le bouton rond (l'icone du plugin, au format du
	// Market, est reservee a la liste des plugins).
	public function getImage() {
		return 'plugins/m5dial/desktop/images/bouton.png';
	}

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
			case 'maj_firmware':
				$eqLogic->lancerMajFirmware();
				break;
		}
	}
}

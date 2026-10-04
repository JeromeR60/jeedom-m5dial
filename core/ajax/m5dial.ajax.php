<?php
/* This file is part of Jeedom - plugin M5Dial (licence AGPL). */

try {
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	include_file('core', 'authentification', 'php');

	if (!isConnect('admin')) {
		throw new Exception(__('401 - Accès non autorisé', __FILE__));
	}

	ajax::init();

	// Boutons de la page de l'equipement : envoi de la configuration ou
	// retour a la configuration locale du bouton.
	if (init('action') == 'envoyerConfig' || init('action') == 'configLocale') {
		$eqLogic = m5dial::byId(init('id'));
		if (!is_object($eqLogic)) {
			throw new Exception(__('Equipement introuvable', __FILE__) . ' : ' . init('id'));
		}
		if (init('action') == 'envoyerConfig') {
			$eqLogic->envoyerConfiguration(init('config', null));
		} else {
			$eqLogic->revenirConfigurationLocale();
		}
		ajax::success();
	}

	// Depot d'un firmware.bin (page du plugin).
	if (init('action') == 'envoyerFirmware') {
		if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] != UPLOAD_ERR_OK) {
			throw new Exception(__('Fichier non reçu', __FILE__) . (isset($_FILES['fichier']) ? ' (' . $_FILES['fichier']['error'] . ')' : ''));
		}
		ajax::success(m5dial::enregistrerFirmware($_FILES['fichier']['tmp_name'], $_FILES['fichier']['name']));
	}

	if (init('action') == 'infoFirmware') {
		ajax::success(m5dial::infoFirmware());
	}

	if (init('action') == 'majFirmware') {
		$eqLogic = m5dial::byId(init('id'));
		if (!is_object($eqLogic)) {
			throw new Exception(__('Equipement introuvable', __FILE__) . ' : ' . init('id'));
		}
		$eqLogic->lancerMajFirmware();
		ajax::success();
	}

	// Appairage des nouveaux boutons (page d'accueil du plugin).
	if (init('action') == 'appairages') {
		ajax::success(array_values(m5dial::lireAppairages()));
	}
	if (init('action') == 'reponseAppairage') {
		m5dial::reponseAppairage(init('mac'), init('accepte') == 1);
		ajax::success();
	}

	// Noms complets ([Objet][Equipement][Commande]) des commandes de l'editeur.
	if (init('action') == 'nomsCommandes') {
		$ids = json_decode(init('ids'), true);
		$noms = array();
		if (is_array($ids)) {
			foreach ($ids as $id) {
				$cmd = cmd::byId(intval($id));
				$noms[$id] = is_object($cmd) ? $cmd->getHumanName() : '#' . $id . '# (' . __('introuvable', __FILE__) . ')';
			}
		}
		ajax::success($noms);
	}

	throw new Exception(__('Aucune méthode correspondante à', __FILE__) . ' : ' . init('action'));
} catch (Exception $e) {
	ajax::error(displayException($e), $e->getCode());
}

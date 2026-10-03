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

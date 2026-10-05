<?php
/* This file is part of Jeedom - plugin M5Dial (licence AGPL).
 *
 * Sert le firmware.bin depose dans le plugin aux boutons M5Dial (mise a jour
 * demandee par Jeedom). Le dossier data/ n'etant pas accessible par le web,
 * le fichier passe par cette page, protegee par un jeton a usage limite
 * (15 min) cree a chaque demande de mise a jour.
 */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

if (!m5dial::jetonValide(preg_replace('/[^A-Za-z0-9]/', '', init('jeton')))) {
	http_response_code(403);
	echo 'Acces refuse';
	die();
}
$fichier = m5dial::dossierFirmware() . '/firmware.bin';
if (!file_exists($fichier)) {
	http_response_code(404);
	echo 'Aucun firmware';
	die();
}
while (ob_get_level() > 0) {
	ob_end_clean();
}
log::add('m5dial', 'info', __('Téléchargement du firmware par', __FILE__) . ' ' . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '?'));
header('Content-Type: application/octet-stream');
header('Content-Length: ' . filesize($fichier));
readfile($fichier);

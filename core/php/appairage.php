<?php
/* This file is part of Jeedom - plugin M5Dial (licence AGPL).
 *
 * Appairage d'un nouveau bouton (etape 4c). Le bouton, qui connait seulement
 * le Wi-Fi et l'adresse de Jeedom (portail), appelle cette page toutes les
 * quelques secondes avec son adresse MAC et le code a 4 chiffres affiche sur
 * son ecran. Tant que l'utilisateur n'a pas accepte la demande dans la page du
 * plugin, la reponse est {"etat":"attente"}. Une fois acceptee, la page renvoie
 * une seule fois les identifiants (broker, MQTT, mot de passe de mise a jour).
 * Page publique (le bouton n'a encore aucun identifiant) : rien n'est donne
 * sans l'acceptation explicite de l'administrateur, pour ce MAC et ce code.
 */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

header('Content-Type: application/json');

$mac = strtoupper(preg_replace('/[^0-9A-Fa-f]/', '', init('mac')));
$code = preg_replace('/[^0-9]/', '', init('code'));
$nom = substr(preg_replace('/[^A-Za-z0-9_-]/', '', init('nom')), 0, 32);
$version = substr(preg_replace('/[^A-Za-z0-9._-]/', '', init('version')), 0, 20);

if (strlen($mac) != 12 || strlen($code) != 4) {
	http_response_code(400);
	echo json_encode(array('etat' => 'erreur', 'message' => 'parametres invalides'));
	die();
}

echo json_encode(m5dial::demandeAppairage($mac, $code, $nom, $version, isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''));

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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

// Apres l'installation : abonnement aux topics m5dial/# via MQTT Manager.
function m5dial_install() {
	m5dial::enregistrerTopic();
	m5dial::motDePasseOta();
}

// Apres une mise a jour : on reenregistre le topic et on complete les
// commandes des equipements existants (nouvelles commandes eventuelles).
function m5dial_update() {
	m5dial::enregistrerTopic();
	m5dial::motDePasseOta();
	foreach (eqLogic::byType('m5dial') as $eqLogic) {
		$eqLogic->save();
	}
}

// A la suppression : on rend le topic a MQTT Manager.
function m5dial_remove() {
	if (class_exists('mqtt2') && method_exists('mqtt2', 'removePluginTopicByPlugin')) {
		mqtt2::removePluginTopicByPlugin('m5dial');
	}
}

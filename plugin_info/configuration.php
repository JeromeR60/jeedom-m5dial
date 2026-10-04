<?php
/* This file is part of Jeedom - plugin M5Dial (licence AGPL). */
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
	include_file('desktop', '404', 'php');
	die();
}
?>
<form class="form-horizontal">
	<fieldset>
		<div class="form-group">
			<label class="col-md-4 control-label">{{Topic racine des boutons}}
				<sup><i class="fas fa-question-circle tooltips" title="{{Les boutons publient sur m5dial/<nom>/status et m5dial/<nom>/info, et reçoivent leur configuration sur m5dial/<nom>/config}}"></i></sup>
			</label>
			<div class="col-md-4">
				<span class="label label-info">m5dial</span>
			</div>
		</div>
		<legend><i class="fas fa-key"></i> {{Identifiants transmis aux boutons lors de l'appairage}}</legend>
		<div class="form-group">
			<label class="col-md-4 control-label">{{Utilisateur MQTT des boutons}}
				<sup><i class="fas fa-question-circle tooltips" title="{{Utilisateur défini dans la configuration de MQTT Manager (champ Authentification). Son mot de passe est transmis automatiquement.}}"></i></sup>
			</label>
			<div class="col-md-4">
				<select class="configKey form-control" data-l1key="mqtt_utilisateur">
					<?php
					$utilisateurs = m5dial::utilisateursMqtt();
					if (count($utilisateurs) == 0) {
						echo '<option value="">{{Aucun utilisateur dans MQTT Manager}}</option>';
					}
					foreach ($utilisateurs as $utilisateur => $mdp) {
						echo '<option value="' . htmlspecialchars($utilisateur) . '"' . ($utilisateur == 'm5dial' ? ' selected' : '') . '>' . htmlspecialchars($utilisateur) . '</option>';
					}
					?>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-4 control-label">{{Mot de passe des mises à jour (OTA)}}
				<sup><i class="fas fa-question-circle tooltips" title="{{Généré par le plugin et transmis aux boutons. À recopier dans secrets_ota.ini seulement pour une mise à jour depuis PlatformIO.}}"></i></sup>
			</label>
			<div class="col-md-4">
				<input class="form-control" readonly value="<?php echo htmlspecialchars(m5dial::motDePasseOta()); ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-4 control-label">{{Prérequis}}</label>
			<div class="col-md-8">
				{{Le plugin MQTT Manager (mqtt2) doit être installé et démarré : il fournit la connexion au broker.}}
			</div>
		</div>
	</fieldset>
</form>

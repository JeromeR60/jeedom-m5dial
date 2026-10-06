<?php
/* This file is part of Jeedom - plugin M5Dial (licence AGPL). */
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect('admin')) {
	include_file('desktop', '404', 'php');
	die();
}
?>
<form class="form-horizontal">
	<fieldset>
		<legend><i class="fas fa-info-circle"></i> {{Général}}</legend>
		<?php
		$m5dialInfo = json_decode(file_get_contents(__DIR__ . '/info.json'), true);
		$m5dialVersion = (is_array($m5dialInfo) && isset($m5dialInfo['pluginVersion'])) ? $m5dialInfo['pluginVersion'] : '?';
		?>
		<div class="form-group">
			<label class="col-md-4 control-label">{{Version du plugin}}</label>
			<div class="col-md-4">
				<span class="label label-success">v<?php echo htmlspecialchars($m5dialVersion); ?></span>
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-4 control-label">{{Topic racine des boutons}}
				<sup><i class="fas fa-question-circle tooltips" title="{{Les boutons publient sur m5dial/<nom>/status et m5dial/<nom>/info, et reçoivent leur configuration sur m5dial/<nom>/config}}"></i></sup>
			</label>
			<div class="col-md-4">
				<span class="label label-info">m5dial</span>
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-4 control-label">{{Prérequis}}</label>
			<div class="col-md-8">
				{{Le plugin MQTT Manager (mqtt2) doit être installé et démarré : il fournit la connexion au broker.}}
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
				<sup><i class="fas fa-question-circle tooltips" title="{{Généré par le plugin et transmis aux boutons lors de l'appairage. À recopier dans secrets_ota.ini seulement pour une mise à jour depuis PlatformIO.}}"></i></sup>
			</label>
			<div class="col-md-4">
				<div class="input-group">
					<input class="form-control roundedLeft" type="password" readonly id="in_m5dialOtaMdp" value="<?php echo htmlspecialchars(m5dial::motDePasseOta()); ?>">
					<span class="input-group-btn">
						<a class="btn btn-default" id="bt_m5dialVoirOta" title="{{Afficher / masquer}}"><i class="fas fa-eye"></i></a>
						<a class="btn btn-warning roundedRight" id="bt_m5dialNouveauOta" title="{{Générer un nouveau mot de passe}}"><i class="fas fa-sync"></i></a>
					</span>
				</div>
				<span class="help-block">{{Un nouveau mot de passe ne s'applique qu'aux boutons appairés ensuite : les boutons existants gardent l'ancien jusqu'à un réappairage.}}</span>
			</div>
		</div>

		<legend><i class="fas fa-microchip"></i> {{Mises à jour du firmware}}</legend>
		<div class="form-group">
			<label class="col-md-4 control-label">{{Installer automatiquement les nouvelles versions}}
				<sup><i class="fas fa-question-circle tooltips" title="{{Chaque nuit, le plugin vérifie la dernière version publiée sur GitHub. Il prévient toujours (message Jeedom, badge sur la vignette, commande Mise à jour disponible). Si cette case est cochée, il télécharge aussi le firmware et met à jour les boutons en ligne.}}"></i></sup>
			</label>
			<div class="col-md-4">
				<input type="checkbox" class="configKey" data-l1key="maj_auto">
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-4 control-label">{{Mode avancé (développeur)}}
				<sup><i class="fas fa-question-circle tooltips" title="{{Affiche le bouton Déposer un firmware.bin sur la page du plugin, pour installer un firmware compilé soi-même (tests, version de développement).}}"></i></sup>
			</label>
			<div class="col-md-4">
				<input type="checkbox" class="configKey" data-l1key="mode_avance">
			</div>
		</div>
	</fieldset>
</form>
<script>
	document.getElementById('bt_m5dialVoirOta').addEventListener('click', function () {
		var champ = document.getElementById('in_m5dialOtaMdp')
		champ.type = (champ.type == 'password') ? 'text' : 'password'
	})
	document.getElementById('bt_m5dialNouveauOta').addEventListener('click', function () {
		if (!confirm('{{Générer un nouveau mot de passe OTA ? Les boutons déjà appairés garderont l\'ancien jusqu\'à leur réappairage.}}')) return
		$.ajax({
			type: 'POST',
			url: 'plugins/m5dial/core/ajax/m5dial.ajax.php',
			data: { action: 'nouveauMdpOta' },
			dataType: 'json',
			success: function (data) {
				if (data.state != 'ok') {
					$('#div_alert').showAlert({ message: data.result, level: 'danger' })
					return
				}
				document.getElementById('in_m5dialOtaMdp').value = data.result
			}
		})
	})
</script>

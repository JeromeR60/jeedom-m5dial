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
		<div class="form-group">
			<label class="col-md-4 control-label">{{Prérequis}}</label>
			<div class="col-md-8">
				{{Le plugin MQTT Manager (mqtt2) doit être installé et démarré : il fournit la connexion au broker.}}
			</div>
		</div>
	</fieldset>
</form>

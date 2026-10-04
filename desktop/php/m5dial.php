<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('m5dial');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
	<!-- Page d'accueil du plugin -->
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
		<!-- Boutons de gestion du plugin -->
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br>
				<span>{{Ajouter}}</span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br>
				<span>{{Configuration}}</span>
			</div>
		</div>
		<legend><i class="fas fa-microchip"></i> {{Firmware des boutons}}</legend>
		<div class="form-horizontal" style="margin-left:10px;">
			<div class="form-group">
				<label class="col-sm-3 control-label">{{Firmware disponible pour les mises à jour}}</label>
				<div class="col-sm-9">
					<span class="label label-info" id="span_m5dialFirmware" style="font-size:1em;">{{chargement...}}</span>
					<label class="btn btn-default btn-sm" style="margin-left:10px;margin-bottom:0;">
						<i class="fas fa-upload"></i> {{Déposer un firmware.bin}}
						<input type="file" id="in_m5dialFirmware" accept=".bin" style="display:none;">
					</label>
					<span class="help-block">{{Fichier généré par PlatformIO : .pio\build\m5dial\firmware.bin (après un Build). Ensuite, bouton « Mettre à jour le firmware » dans chaque équipement.}}</span>
				</div>
			</div>
		</div>
		<legend><i class="fas fa-table"></i> {{Mes boutons M5Dial}}</legend>
		<?php
		if (count($eqLogics) == 0) {
			echo '<br><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun bouton trouvé : ils apparaissent automatiquement dès qu\'ils se connectent au broker MQTT}}</div>';
		} else {
			// Champ de recherche
			echo '<div class="input-group" style="margin:5px;">';
			echo '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic">';
			echo '<div class="input-group-btn">';
			echo '<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
			echo '<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
			echo '</div>';
			echo '</div>';
			// Liste des équipements du plugin
			echo '<div class="eqLogicThumbnailContainer">';
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $eqLogic->getImage() . '"/>';
				echo '<br>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				// Etat du bouton (commandes info En ligne / Version firmware / Source de la configuration)
				$enLigne = null;
				$version = '';
				$source = '';
				$cmd = $eqLogic->getCmd('info', 'online');
				if (is_object($cmd)) {
					$enLigne = $cmd->execCmd();
				}
				$cmd = $eqLogic->getCmd('info', 'version');
				if (is_object($cmd)) {
					$version = $cmd->execCmd();
				}
				$cmd = $eqLogic->getCmd('info', 'config_source');
				if (is_object($cmd)) {
					$source = $cmd->execCmd();
				}
				echo '<span style="display:block;font-size:0.85em;margin-top:2px;">';
				if ($enLigne === null || $enLigne === '') {
					echo '<i class="fas fa-circle" style="color:#999;"></i> {{Jamais vu}}';
				} elseif ($enLigne == 1) {
					echo '<i class="fas fa-circle" style="color:#5cb85c;"></i> {{En ligne}}';
				} else {
					echo '<i class="fas fa-circle" style="color:#d9534f;"></i> {{Hors ligne}}';
				}
				// Version sur la meme ligne (la vignette a une hauteur fixe) ; la
				// source de la configuration est dans l'infobulle.
				if ($version != '') {
					$titre = ($source == 'mqtt') ? '{{Configuration envoyée par Jeedom}}' : (($source == 'locale') ? '{{Configuration locale du bouton}}' : '');
					echo ' <span style="opacity:0.8;" title="' . $titre . '">· v' . htmlspecialchars($version) . '</span>';
				}
				echo '</span>';
				echo '<span class="hiddenAsCard displayTableRight hidden">';
				echo ($eqLogic->getIsVisible() == 1) ? '<i class="fas fa-eye" title="{{Equipement visible}}"></i>' : '<i class="fas fa-eye-slash" title="{{Equipement non visible}}"></i>';
				echo '</span>';
				echo '</div>';
			}
			echo '</div>';
		}
		?>
	</div> <!-- /.eqLogicThumbnailDisplay -->

	<!-- Page de présentation de l'équipement -->
	<div class="col-xs-12 eqLogic" style="display: none;">
		<!-- barre de gestion de l'équipement -->
		<div class="input-group pull-right" style="display:inline-flex;">
			<span class="input-group-btn">
				<!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
				<a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
				</a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs"> {{Dupliquer}}</span>
				</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
				</a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
				</a>
			</span>
		</div>
		<!-- Onglets -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#m5dialEcranstab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-th-large"></i> {{Écrans du bouton}}</a></li>
			<li role="presentation"><a href="#m5dialJsontab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-code"></i> {{JSON}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content">
			<!-- Onglet de configuration de l'équipement -->
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<!-- Partie gauche de l'onglet "Equipements" -->
				<!-- Paramètres généraux et spécifiques de l'équipement -->
				<form class="form-horizontal">
					<fieldset>
						<div class="col-lg-6">
							<legend><i class="fas fa-wrench"></i> {{Paramètres généraux}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Nom de l'équipement}}</label>
								<div class="col-sm-6">
									<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display:none;">
									<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Objet parent}}</label>
								<div class="col-sm-6">
									<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
										<option value="">{{Aucun}}</option>
										<?php
										$options = '';
										foreach ((jeeObject::buildTree(null, false)) as $object) {
											$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
										}
										echo $options;
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Catégorie}}</label>
								<div class="col-sm-6">
									<?php
									foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
										echo '<label class="checkbox-inline">';
										echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" >' . $value['name'];
										echo '</label>';
									}
									?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Options}}</label>
								<div class="col-sm-6">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked>{{Activer}}</label>
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked>{{Visible}}</label>
								</div>
							</div>

							<legend><i class="fas fa-cogs"></i> {{Paramètres spécifiques}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Nom du bouton}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Nom défini dans device.name du config.json du bouton (ex. m5dial-test). Rempli automatiquement pour un bouton détecté.}}"></i></sup>
								</label>
								<div class="col-sm-6">
									<input type="text" class="eqLogicAttr form-control" data-l1key="logicalId" placeholder="m5dial-salon">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Identifiant MQTT}}</label>
								<div class="col-sm-6">
									<span class="eqLogicAttr label label-default" data-l1key="configuration" data-l2key="clientId"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Adresse MAC}}</label>
								<div class="col-sm-6">
									<span class="eqLogicAttr label label-default" data-l1key="configuration" data-l2key="mac"></span>
								</div>
							</div>
						</div>

						<!-- Partie droite de l'onglet "Équipement" -->
						<!-- Affiche un champ de commentaire par défaut mais vous pouvez y mettre ce que vous voulez -->
						<div class="col-lg-6">
							<legend><i class="fas fa-microchip"></i> {{Firmware}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Disponible dans Jeedom}}</label>
								<div class="col-sm-6">
									<span class="label label-info m5dialFirmwareDispo">-</span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label"></label>
								<div class="col-sm-6">
									<a class="btn btn-primary btn-sm" id="bt_m5dialMajFirmware"><i class="fas fa-download"></i> {{Mettre à jour le firmware}}</a>
									<span class="help-block">{{Le bouton télécharge le firmware depuis Jeedom puis redémarre (environ 30 s). Suivi dans la commande « État de la mise à jour ».}}</span>
								</div>
							</div>
							<legend><i class="fas fa-info"></i> {{Informations}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Description}}</label>
								<div class="col-sm-6">
									<textarea class="form-control eqLogicAttr autogrow" data-l1key="comment"></textarea>
								</div>
							</div>
						</div>
					</fieldset>
				</form>
			</div><!-- /.tabpanel #eqlogictab-->

			<!-- Onglet editeur des ecrans du bouton (desktop/js/m5dial_editeur.js) -->
			<div role="tabpanel" class="tab-pane" id="m5dialEcranstab">
				<br>
				<div class="alert alert-info">
					{{Cochez les écrans à afficher sur ce bouton et choisissez les commandes Jeedom avec}} <i class="fas fa-list-alt"></i>.
					{{Sauvegardez l'équipement, puis envoyez la configuration : le bouton redémarre dessus.}}
				</div>
				<div style="margin-bottom:10px;">
					<a class="btn btn-success" id="bt_m5dialEnvoyerConfig"><i class="fas fa-paper-plane"></i> {{Envoyer la configuration au bouton}}</a>
					<a class="btn btn-warning" id="bt_m5dialConfigLocale"><i class="fas fa-undo"></i> {{Revenir à la configuration locale}}</a>
				</div>
				<div class="alert alert-danger" id="div_m5dialErreurJson" style="display:none;"></div>
				<div id="div_m5dialEditeur"></div>
			</div>

			<!-- Onglet JSON : configuration brute (toujours synchronisee avec l'editeur) -->
			<div role="tabpanel" class="tab-pane" id="m5dialJsontab">
				<br>
				<textarea class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="configJson" rows="30" style="font-family:monospace;font-size:12px;"></textarea>
				<span class="help-block">{{Configuration envoyée au bouton, générée par l'onglet Écrans du bouton. Vous pouvez aussi la coller ou la modifier ici (mêmes sections que le config.json du bouton ; la section device est ignorée).}}</span>
			</div>

			<!-- Onglet des commandes de l'équipement -->
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<a class="btn btn-default btn-sm pull-right cmdAction" data-action="add" style="margin-top:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une commande}}</a>
				<br><br>
				<div class="table-responsive">
					<table id="table_cmd" class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th class="hidden-xs" style="min-width:50px;width:70px;">ID</th>
								<th style="min-width:200px;width:350px;">{{Nom}}</th>
								<th>{{Type}}</th>
								<th style="min-width:260px;">{{Options}}</th>
								<th>{{Etat}}</th>
								<th style="min-width:80px;width:200px;">{{Actions}}</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div><!-- /.tabpanel #commandtab-->

		</div><!-- /.tab-content -->
	</div><!-- /.eqLogic -->
</div><!-- /.row row-overflow -->

<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, id_du_plugin) -->
<?php include_file('desktop', 'm5dial_editeur', 'js', 'm5dial'); ?>
<?php include_file('desktop', 'm5dial', 'js', 'm5dial'); ?>
<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js'); ?>

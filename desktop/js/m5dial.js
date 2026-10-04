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

/* Permet la réorganisation des commandes dans l'équipement */
$("#table_cmd").sortable({
  axis: "y",
  cursor: "move",
  items: ".cmd",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true
})

/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = { configuration: {} }
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {}
  }
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
  tr += '<td class="hidden-xs">'
  tr += '<span class="cmdAttr" data-l1key="id"></span>'
  tr += '</td>'
  tr += '<td>'
  tr += '<div class="input-group">'
  tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
  tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
  tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
  tr += '</div>'
  tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">'
  tr += '<option value="">{{Aucune}}</option>'
  tr += '</select>'
  tr += '</td>'
  tr += '<td>'
  tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>'
  tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'
  tr += '</td>'
  tr += '<td>'
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label> '
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label> '
  tr += '<div style="margin-top:7px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '</div>'
  tr += '</td>'
  tr += '<td>';
  tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>';
  tr += '</td>';
  tr += '<td>'
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>'
  }
  tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'
  tr += '</tr>'
  $('#table_cmd tbody').append(tr)
  var tr = $('#table_cmd tbody tr').last()
  jeedom.eqLogic.buildSelectCmd({
    id: $('.eqLogicAttr[data-l1key=id]').value(),
    filter: { type: 'info' },
    error: function (error) {
      $('#div_alert').showAlert({ message: error.message, level: 'danger' })
    },
    success: function (result) {
      tr.find('.cmdAttr[data-l1key=value]').append(result)
      tr.setValues(_cmd, '.cmdAttr')
      jeedom.cmd.changeType(tr, init(_cmd.subType))
    }
  })
}

/* Boutons "Envoyer la configuration" / "Revenir a la configuration locale".
   La configuration envoyee est celle du champ (elle est aussi enregistree). */
function m5dialAction(_action, _messageOk, _config) {
  var id = $('.eqLogicAttr[data-l1key=id]').value()
  if (id == '') {
    $('#div_alert').showAlert({ message: '{{Sauvegardez d\'abord l\'équipement}}', level: 'warning' })
    return
  }
  $.ajax({
    type: 'POST',
    url: 'plugins/m5dial/core/ajax/m5dial.ajax.php',
    data: { action: _action, id: id, config: _config },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error)
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({ message: data.result, level: 'danger' })
        return
      }
      $('#div_alert').showAlert({ message: _messageOk, level: 'success' })
    }
  })
}

$('#bt_m5dialEnvoyerConfig').off('click').on('click', function () {
  m5dialAction('envoyerConfig', '{{Configuration envoyée : le bouton va redémarrer dessus}}',
    $('.eqLogicAttr[data-l1key=configuration][data-l2key=configJson]').value())
})

$('#bt_m5dialConfigLocale').off('click').on('click', function () {
  m5dialAction('configLocale', '{{Retour à la configuration locale demandé : le bouton va redémarrer}}')
})

/* Appelee par le core apres le chargement d'un equipement : construit
   l'editeur des ecrans a partir du JSON enregistre. */
function printEqLogic(_eqLogic) {
  m5dialAfficher()
  m5dialInfoFirmware()
}

/* ------------------------------------------------------------------ */
/* Firmware : depot du fichier et mise a jour des boutons              */
/* ------------------------------------------------------------------ */
function m5dialTexteFirmware(_info) {
  if (!_info) return '{{aucun firmware déposé}}'
  return 'v' + _info.version + ' (' + Math.round(_info.taille / 1024) + ' Ko, ' + _info.date + ')'
}

function m5dialInfoFirmware() {
  $.ajax({
    type: 'POST',
    url: 'plugins/m5dial/core/ajax/m5dial.ajax.php',
    data: { action: 'infoFirmware' },
    dataType: 'json',
    error: function (request, status, error) { handleAjaxError(request, status, error) },
    success: function (data) {
      if (data.state != 'ok') return
      $('#span_m5dialFirmware, .m5dialFirmwareDispo').text(m5dialTexteFirmware(data.result))
    }
  })
}

$('#in_m5dialFirmware').off('change').on('change', function () {
  var fichier = this.files[0]
  if (!fichier) return
  var fd = new FormData()
  fd.append('action', 'envoyerFirmware')
  fd.append('fichier', fichier)
  $('#span_m5dialFirmware').text('{{envoi en cours...}}')
  $.ajax({
    type: 'POST',
    url: 'plugins/m5dial/core/ajax/m5dial.ajax.php',
    data: fd,
    processData: false,
    contentType: false,
    dataType: 'json',
    error: function (request, status, error) { handleAjaxError(request, status, error) },
    success: function (data) {
      $('#in_m5dialFirmware').val('')
      if (data.state != 'ok') {
        $('#div_alert').showAlert({ message: data.result, level: 'danger' })
        m5dialInfoFirmware()
        return
      }
      $('#div_alert').showAlert({ message: '{{Firmware déposé}} : v' + data.result.version, level: 'success' })
      $('#span_m5dialFirmware, .m5dialFirmwareDispo').text(m5dialTexteFirmware(data.result))
    }
  })
})

$('#bt_m5dialMajFirmware').off('click').on('click', function () {
  m5dialAction('majFirmware', '{{Mise à jour demandée : le bouton télécharge le firmware puis redémarre}}')
})

// Page d'accueil du plugin : affiche le firmware disponible.
m5dialInfoFirmware()

/* ------------------------------------------------------------------ */
/* Appairage des nouveaux boutons (page d'accueil du plugin)           */
/* ------------------------------------------------------------------ */
function m5dialAppairages() {
  if ($('#tb_m5dialAppairages').length == 0) return
  $.ajax({
    type: 'POST',
    url: 'plugins/m5dial/core/ajax/m5dial.ajax.php',
    data: { action: 'appairages' },
    dataType: 'json',
    global: false,
    success: function (data) {
      if (data.state != 'ok') return
      var html = ''
      data.result.forEach(function (d) {
        var e = function (t) { return $('<span>').text(t || '').html() }
        html += '<tr><td>' + e(d.nom) + '</td><td>' + e(d.mac) + '</td><td>' + e(d.ip) + '</td><td>' + e(d.version) + '</td>'
        html += '<td><span class="label label-warning" style="font-size:1.3em;letter-spacing:3px;">' + e(d.code) + '</span></td><td>'
        if (d.etat == 'attente') {
          html += '<a class="btn btn-success btn-sm m5dialReponse" data-mac="' + e(d.mac) + '" data-accepte="1"><i class="fas fa-check"></i> {{Accepter}}</a> '
          html += '<a class="btn btn-danger btn-sm m5dialReponse" data-mac="' + e(d.mac) + '" data-accepte="0"><i class="fas fa-times"></i> {{Refuser}}</a>'
        } else {
          html += (d.etat == 'accepte') ? '{{Accepté, transmission en cours...}}' : '{{Refusé}}'
        }
        html += '</td></tr>'
      })
      $('#tb_m5dialAppairages').html(html)
      $('#div_m5dialAppairages').toggle(data.result.length > 0)
    }
  })
}

$('#tb_m5dialAppairages').off('click', '.m5dialReponse').on('click', '.m5dialReponse', function () {
  $.ajax({
    type: 'POST',
    url: 'plugins/m5dial/core/ajax/m5dial.ajax.php',
    data: { action: 'reponseAppairage', mac: $(this).attr('data-mac'), accepte: $(this).attr('data-accepte') },
    dataType: 'json',
    error: function (request, status, error) { handleAjaxError(request, status, error) },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({ message: data.result, level: 'danger' })
      }
      m5dialAppairages()
    }
  })
})

// Rafraichissement toutes les 4 s tant que la page du plugin est ouverte.
m5dialAppairages()
if (window.m5dialMinuteurAppairage) clearInterval(window.m5dialMinuteurAppairage)
window.m5dialMinuteurAppairage = setInterval(function () {
  if ($('#tb_m5dialAppairages').length == 0) {
    clearInterval(window.m5dialMinuteurAppairage)
    return
  }
  m5dialAppairages()
}, 4000)

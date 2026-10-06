/* This file is part of Jeedom - plugin M5Dial (licence AGPL).
 *
 * Editeur de la configuration du bouton : chaque ecran du M5Dial (Presence,
 * Lumieres, Volets...) a sa section, et chaque commande se choisit avec le
 * selecteur de commandes de Jeedom. L'editeur lit et ecrit le JSON du champ
 * "configJson" (onglet JSON) : c'est toujours ce JSON qui est sauvegarde et
 * envoye au bouton. Les cles inconnues de l'editeur sont conservees.
 */

/* Description des sections. Champ :
 *   c : chemin dans l'objet ("actions.present", "tempRangeMireds.0")
 *   l : libelle
 *   t : 'info' / 'action' (commande Jeedom), 'texte' ou 'nombre'
 *   d : aide (facultatif)
 */
var M5DIAL_SECTIONS = [
  {
    cle: 'presence', titre: '{{Présence}}', icone: 'fas fa-home', liste: false,
    champs: [
      { c: 'modeInfo', l: '{{Mode actuel}}', t: 'info', d: '{{Info texte : Présent / Absent / Vacances}}' },
      { c: 'actions.present', l: '{{Passer en Présent}}', t: 'action' },
      { c: 'actions.absent', l: '{{Passer en Absent}}', t: 'action' },
      { c: 'vacances.nbJoursInfo', l: '{{Nombre de jours de vacances (info)}}', t: 'info' },
      { c: 'vacances.nbJoursAction', l: '{{Nombre de jours de vacances (curseur)}}', t: 'action', d: '{{Le scénario Jeedom active le mode Vacances quand cette valeur change}}' }
    ]
  },
  {
    cle: 'lumieres', titre: '{{Lumières}}', icone: 'fas fa-lightbulb', liste: true, ajout: '{{Ajouter une lumière}}',
    champs: [
      { c: 'nom', l: '{{Nom affiché}}', t: 'texte', d: '{{Sans accent (police du bouton)}}' },
      { c: 'etage', l: '{{Étage}}', t: 'texte', d: '{{RDC, 1er...}}' },
      { c: 'on', l: '{{On}}', t: 'action' },
      { c: 'off', l: '{{Off}}', t: 'action' },
      { c: 'etat', l: '{{État}}', t: 'info' },
      { c: 'slider', l: '{{Luminosité (curseur)}}', t: 'action', d: '{{Facultatif}}' },
      { c: 'sliderInfo', l: '{{Luminosité (info)}}', t: 'info' },
      { c: 'sliderRange.1', l: '{{Luminosité maximale}}', t: 'nombre', d: '{{254 en général}}' },
      { c: 'temp', l: '{{Température de blanc (curseur)}}', t: 'action', d: '{{Facultatif, en mireds}}' },
      { c: 'tempInfo', l: '{{Température de blanc (info)}}', t: 'info' },
      { c: 'tempRangeMireds.0', l: '{{Blanc mini (mireds)}}', t: 'nombre' },
      { c: 'tempRangeMireds.1', l: '{{Blanc maxi (mireds)}}', t: 'nombre' },
      { c: 'couleur', l: '{{Couleur (action)}}', t: 'action', d: '{{Facultatif}}' },
      { c: 'couleurInfo', l: '{{Couleur (info)}}', t: 'info' }
    ]
  },
  {
    cle: 'volets', titre: '{{Volets}}', icone: 'fas fa-bars', liste: true, ajout: '{{Ajouter un volet}}',
    champs: [
      { c: 'nom', l: '{{Nom affiché}}', t: 'texte', d: '{{Sans accent (police du bouton)}}' },
      { c: 'etage', l: '{{Étage}}', t: 'texte', d: '{{RDC, 1er... (pour les compteurs par étage)}}' },
      { c: 'etat', l: '{{Position (info, 0-100)}}', t: 'info' },
      { c: 'slider', l: '{{Position (curseur)}}', t: 'action' },
      { c: 'monter', l: '{{Monter}}', t: 'action' },
      { c: 'descendre', l: '{{Descendre}}', t: 'action' },
      { c: 'stop', l: '{{Stop}}', t: 'action' }
    ]
  },
  {
    cle: 'groupes', titre: '{{Actions groupées}}', icone: 'fas fa-layer-group', liste: false,
    champs: [
      { c: 'toutesLesLumieres.allumer', l: '{{Tout allumer}}', t: 'action' },
      { c: 'toutesLesLumieres.eteindre', l: '{{Tout éteindre}}', t: 'action' },
      { c: 'tousLesVolets.ouvrir', l: '{{Tous les volets : ouvrir}}', t: 'action' },
      { c: 'tousLesVolets.fermer', l: '{{Tous les volets : fermer}}', t: 'action' },
      { c: 'voletsRDC.ouvrir', l: '{{Volets RDC : ouvrir}}', t: 'action' },
      { c: 'voletsRDC.fermer', l: '{{Volets RDC : fermer}}', t: 'action' },
      { c: 'volets1er.ouvrir', l: '{{Volets 1er : ouvrir}}', t: 'action' },
      { c: 'volets1er.fermer', l: '{{Volets 1er : fermer}}', t: 'action' },
      { c: 'heureFermetureAuto.infoTexte', l: '{{Heure de fermeture automatique (info)}}', t: 'info', d: '{{Affichée sur l\'écran Volets}}' }
    ]
  },
  {
    cle: 'chauffage', titre: '{{Chauffage}}', icone: 'fas fa-thermometer-half', liste: false,
    champs: [
      { c: 'nomAffiche', l: '{{Nom affiché}}', t: 'texte' },
      { c: 'temperatureAmbianteInfo', l: '{{Température ambiante}}', t: 'info' },
      { c: 'consigneInfo', l: '{{Consigne (info)}}', t: 'info' },
      { c: 'consigneAction', l: '{{Consigne (curseur)}}', t: 'action' },
      { c: 'consigneMin', l: '{{Consigne mini}}', t: 'nombre' },
      { c: 'consigneMax', l: '{{Consigne maxi}}', t: 'nombre' },
      { c: 'consignePas', l: '{{Pas de réglage}}', t: 'nombre', d: '{{0.5 en général}}' },
      { c: 'modeInfo', l: '{{Mode actuel (info)}}', t: 'info' },
      { c: 'modes.confort', l: '{{Mode Confort}}', t: 'action' },
      { c: 'modes.nuit', l: '{{Mode Nuit}}', t: 'action' },
      { c: 'modes.vacances', l: '{{Mode Vacances}}', t: 'action' },
      { c: 'modes.off', l: '{{Mode Off}}', t: 'action' },
      { c: 'statutInfo', l: '{{Statut (chauffe ou non)}}', t: 'info' },
      { c: 'puissanceInfo', l: '{{Puissance (%)}}', t: 'info' },
      { c: 'temperatureExterieureInfo', l: '{{Température extérieure}}', t: 'info' }
    ]
  },
  {
    cle: 'capteurs', titre: '{{Capteurs}}', icone: 'fas fa-tint', liste: true, ajout: '{{Ajouter une pièce}}',
    champs: [
      { c: 'nom', l: '{{Nom de la pièce}}', t: 'texte' },
      { c: 'temperatureInfo', l: '{{Température}}', t: 'info' },
      { c: 'humiditeInfo', l: '{{Humidité}}', t: 'info', d: '{{Facultatif}}' }
    ]
  },
  {
    cle: 'meteo', titre: '{{Météo}}', icone: 'fas fa-cloud-sun', liste: false,
    champs: [
      { c: 'temperatureInfo', l: '{{Température}}', t: 'info', d: '{{Plugin Météo ou sonde extérieure}}' },
      { c: 'humiditeInfo', l: '{{Humidité}}', t: 'info', d: '{{Facultatif}}' },
      { c: 'conditionInfo', l: '{{Numéro condition}}', t: 'info', d: '{{Facultatif : code de condition du plugin Météo (ex. 1000 = ensoleillé), pour l\'icône}}' },
      { c: 'minInfo', l: '{{Température min}}', t: 'info', d: '{{Facultatif}}' },
      { c: 'maxInfo', l: '{{Température max}}', t: 'info', d: '{{Facultatif}}' },
      { c: 'leverInfo', l: '{{Lever du soleil}}', t: 'info', d: '{{Facultatif, format HHMM (ex. 758) : icône de nuit (lune) entre le coucher et le lever}}' },
      { c: 'coucherInfo', l: '{{Coucher du soleil}}', t: 'info', d: '{{Facultatif, format HHMM (ex. 1919)}}' },
      { c: 'previsions.0.conditionInfo', l: '{{Demain : numéro condition}}', t: 'info', d: '{{Facultatif : prévisions affichées en tournant la molette sur l\'écran Météo (plugin Météo : Numéro condition +1)}}' },
      { c: 'previsions.0.minInfo', l: '{{Demain : température min}}', t: 'info', d: '{{Facultatif (plugin Météo : Température Min +1)}}' },
      { c: 'previsions.0.maxInfo', l: '{{Demain : température max}}', t: 'info', d: '{{Facultatif (plugin Météo : Température Max +1)}}' },
      { c: 'previsions.1.conditionInfo', l: '{{Après-demain : numéro condition}}', t: 'info', d: '{{Facultatif (plugin Météo : Numéro condition +2)}}' },
      { c: 'previsions.1.minInfo', l: '{{Après-demain : température min}}', t: 'info', d: '{{Facultatif (plugin Météo : Température Min +2)}}' },
      { c: 'previsions.1.maxInfo', l: '{{Après-demain : température max}}', t: 'info', d: '{{Facultatif (plugin Météo : Température Max +2)}}' }
    ]
  },
  {
    cle: 'badges', titre: '{{Badges RFID}}', icone: 'fas fa-id-card', liste: false,
    champs: [
      { c: 'setUid', l: '{{Badge passé (message)}}', t: 'action', d: '{{Action message du virtuel Badges (UID lu)}}' },
      { c: 'enregistrerUid', l: '{{Enregistrer un badge (message)}}', t: 'action' },
      { c: 'dernierChangementInfo', l: '{{Dernier changement (info)}}', t: 'info' }
    ]
  }
]

var m5dialConfig = {}

/* Entrees du menu d'accueil du bouton (Reglages est toujours en dernier). */
var M5DIAL_MENU = [
  { cle: 'presence', l: '{{Présence}}' },
  { cle: 'lumieres', l: '{{Lumières}}' },
  { cle: 'volets', l: '{{Volets}}' },
  { cle: 'chauffage', l: '{{Chauffage}}' },
  { cle: 'capteurs', l: '{{Capteurs}}' },
  { cle: 'meteo', l: '{{Météo}}' },
  { cle: 'badges', l: '{{Badges RFID}}' }
]

// Ordre actuel des ecrans actifs : d'abord la liste "menu", puis les autres
// ecrans actifs dans l'ordre par defaut.
function m5dialOrdreMenu() {
  var actifs = M5DIAL_MENU.filter(function (m) { return m5dialConfig[m.cle] !== undefined }).map(function (m) { return m.cle })
  var ordre = []
  ;(Array.isArray(m5dialConfig.menu) ? m5dialConfig.menu : []).forEach(function (c) {
    if (actifs.indexOf(c) >= 0 && ordre.indexOf(c) < 0) ordre.push(c)
  })
  actifs.forEach(function (c) { if (ordre.indexOf(c) < 0) ordre.push(c) })
  return ordre
}

function m5dialHtmlMenu() {
  var ordre = m5dialOrdreMenu()
  var html = '<div class="panel panel-primary"><div class="panel-heading"><i class="fas fa-bars"></i> {{Menu du bouton}}'
  html += ' <sup><i class="fas fa-question-circle tooltips" title="{{Ordre des écrans sur le bouton (le premier est affiché au démarrage). Réglages est toujours en dernier.}}"></i></sup></div>'
  html += '<div class="panel-body">'
  if (ordre.length === 0) {
    html += '<div class="text-muted">{{Cochez au moins un écran ci-dessous}}</div>'
  }
  ordre.forEach(function (c, i) {
    var m = M5DIAL_MENU.find(function (x) { return x.cle === c })
    html += '<div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">'
    html += '<span class="label label-default" style="min-width:22px;">' + (i + 1) + '</span>'
    html += '<span style="min-width:130px;">' + m.l + '</span>'
    html += '<a class="btn btn-xs btn-default m5dialMenuDeplacer" data-index="' + i + '" data-sens="-1"' + (i === 0 ? ' disabled' : '') + ' title="{{Monter}}"><i class="fas fa-arrow-up"></i></a>'
    html += '<a class="btn btn-xs btn-default m5dialMenuDeplacer" data-index="' + i + '" data-sens="1"' + (i === ordre.length - 1 ? ' disabled' : '') + ' title="{{Descendre}}"><i class="fas fa-arrow-down"></i></a>'
    html += '</div>'
  })
  html += '<div style="display:flex;align-items:center;gap:6px;opacity:0.6;"><span class="label label-default" style="min-width:22px;">' + (ordre.length + 1) + '</span><span>{{Réglages}}</span></div>'
  html += '</div></div>'
  return html
}

/* ------------------------------------------------------------------ */
/* Acces aux valeurs par chemin                                        */
/* ------------------------------------------------------------------ */
function m5dialLire(_obj, _chemin) {
  var o = _obj
  var parts = _chemin.split('.')
  for (var i = 0; i < parts.length; i++) {
    if (o === null || typeof o !== 'object' || o[parts[i]] === undefined) return undefined
    o = o[parts[i]]
  }
  return o
}

function m5dialEcrire(_obj, _chemin, _valeur) {
  var parts = _chemin.split('.')
  var o = _obj
  for (var i = 0; i < parts.length - 1; i++) {
    if (o[parts[i]] === undefined || o[parts[i]] === null || typeof o[parts[i]] !== 'object') {
      o[parts[i]] = /^\d+$/.test(parts[i + 1]) ? [] : {}
    }
    o = o[parts[i]]
  }
  var der = parts[parts.length - 1]
  if (_valeur === undefined || _valeur === null || _valeur === '') {
    if (Array.isArray(o)) {
      o[der] = 0
    } else {
      delete o[der]
    }
  } else {
    o[der] = _valeur
  }
  m5dialNettoyer(_obj, parts.slice(0, -1))
}

// Supprime les objets intermediaires devenus vides (ex. "modes": {}).
function m5dialNettoyer(_obj, _parts) {
  for (var n = _parts.length; n > 0; n--) {
    var parent = _obj
    for (var i = 0; i < n - 1; i++) parent = parent[_parts[i]]
    var cible = parent[_parts[n - 1]]
    if (cible && typeof cible === 'object' && !Array.isArray(cible) && Object.keys(cible).length === 0) {
      delete parent[_parts[n - 1]]
    } else {
      return
    }
  }
}

/* ------------------------------------------------------------------ */
/* JSON <-> editeur                                                    */
/* ------------------------------------------------------------------ */
function m5dialChampJson() {
  return $('.eqLogicAttr[data-l1key=configuration][data-l2key=configJson]')
}

function m5dialLireJson() {
  var texte = $.trim(m5dialChampJson().value())
  if (texte === '') return {}
  try {
    var obj = JSON.parse(texte)
    return (obj && typeof obj === 'object' && !Array.isArray(obj)) ? obj : {}
  } catch (e) {
    $('#div_m5dialErreurJson').text('{{Le JSON de l\'onglet JSON est invalide, corrigez-le avant d\'utiliser l\'éditeur}} : ' + e.message).show()
    return null
  }
}

// Recalcule les champs derives puis reecrit le JSON (onglet JSON).
function m5dialEcrireJson() {
  ;(m5dialConfig.lumieres || []).forEach(function (l, i) {
    l.ordre = i + 1
    l.type = l.couleur ? 'couleur' : ((l.slider || l.temp) ? 'variateur' : 'onoff')
  })
  ;(m5dialConfig.volets || []).forEach(function (v, i) {
    v.ordre = i + 1
  })
  if (m5dialConfig.volets && m5dialConfig.voletPositionFermee === undefined) {
    m5dialConfig.voletPositionFermee = 0
  }
  m5dialChampJson().value(JSON.stringify(m5dialConfig, null, 2))
}

/* ------------------------------------------------------------------ */
/* Affichage                                                           */
/* ------------------------------------------------------------------ */
function m5dialHtmlChamp(_champ, _cle, _index, _valeur) {
  var attrs = ' data-section="' + _cle + '" data-index="' + _index + '" data-chemin="' + _champ.c + '"'
  var aide = _champ.d ? ' <sup><i class="fas fa-question-circle tooltips" title="' + _champ.d + '"></i></sup>' : ''
  var html = '<div class="form-group">'
  html += '<label class="col-sm-4 control-label">' + _champ.l + aide + '</label>'
  html += '<div class="col-sm-7">'
  if (_champ.t === 'info' || _champ.t === 'action') {
    var id = (_valeur !== undefined && _valeur !== 0) ? _valeur : ''
    html += '<div class="input-group input-group-sm">'
    html += '<input class="form-control roundedLeft m5dialNomCmd" readonly' + attrs + ' data-id="' + id + '" placeholder="{{Aucune}}" value="' + (id !== '' ? '#' + id + '#' : '') + '">'
    html += '<span class="input-group-btn">'
    html += '<a class="btn btn-default m5dialChoisir" data-type="' + _champ.t + '"' + attrs + ' title="{{Choisir une commande}} (' + _champ.t + ')"><i class="fas fa-list-alt"></i></a>'
    html += '<a class="btn btn-default roundedRight m5dialEffacer"' + attrs + ' title="{{Effacer}}"><i class="fas fa-times"></i></a>'
    html += '</span></div>'
  } else {
    var type = (_champ.t === 'nombre') ? 'number" step="any' : 'text'
    html += '<input class="form-control input-sm m5dialSaisie" type="' + type + '"' + attrs + ' data-type="' + _champ.t + '" value="' + (_valeur !== undefined ? String(_valeur).replace(/"/g, '&quot;') : '') + '">'
  }
  html += '</div></div>'
  return html
}

function m5dialAfficher() {
  var conf = m5dialLireJson()
  $('#div_m5dialErreurJson').hide()
  if (conf === null) {
    $('#div_m5dialErreurJson').show()
    $('#div_m5dialEditeur').hide()
    return
  }
  m5dialConfig = conf
  $('#div_m5dialEditeur').show()
  var html = m5dialHtmlMenu()
  M5DIAL_SECTIONS.forEach(function (s) {
    var active = (m5dialConfig[s.cle] !== undefined)
    html += '<div class="panel panel-default">'
    html += '<div class="panel-heading"><label class="checkbox-inline" style="font-weight:bold;">'
    html += '<input type="checkbox" class="m5dialSectionActive" data-section="' + s.cle + '"' + (active ? ' checked' : '') + '> '
    html += '<i class="' + s.icone + '"></i> ' + s.titre + '</label>'
    if (s.liste && active) {
      html += ' <a class="btn btn-xs btn-success pull-right m5dialAjouter" data-section="' + s.cle + '"><i class="fas fa-plus-circle"></i> ' + s.ajout + '</a>'
    }
    html += '</div>'
    if (active) {
      html += '<div class="panel-body"><form class="form-horizontal">'
      if (s.liste) {
        var elements = Array.isArray(m5dialConfig[s.cle]) ? m5dialConfig[s.cle] : []
        if (elements.length === 0) {
          html += '<div class="text-muted">{{Aucun élément : cliquez sur Ajouter}}</div>'
        }
        elements.forEach(function (el, i) {
          html += '<fieldset style="border:1px solid var(--al-border-color, #ccc);border-radius:5px;padding:5px 10px;margin-bottom:10px;">'
          html += '<legend style="font-size:1em;margin-bottom:5px;">' + (el.nom ? $('<span>').text(el.nom).html() : '{{Sans nom}}')
          html += ' <a class="btn btn-xs btn-danger pull-right m5dialSupprimer" data-section="' + s.cle + '" data-index="' + i + '" title="{{Supprimer}}"><i class="fas fa-trash"></i></a>'
          if (i > 0) html += ' <a class="btn btn-xs btn-default pull-right m5dialMonter" data-section="' + s.cle + '" data-index="' + i + '" title="{{Monter}}" style="margin-right:4px;"><i class="fas fa-arrow-up"></i></a>'
          html += '</legend>'
          s.champs.forEach(function (c) {
            html += m5dialHtmlChamp(c, s.cle, i, m5dialLire(el, c.c))
          })
          html += '</fieldset>'
        })
      } else {
        s.champs.forEach(function (c) {
          html += m5dialHtmlChamp(c, s.cle, -1, m5dialLire(m5dialConfig[s.cle], c.c))
        })
      }
      html += '</form></div>'
    }
    html += '</div>'
  })
  $('#div_m5dialEditeur').html(html)
  $('#div_m5dialEditeur .tooltips').tooltip && $('#div_m5dialEditeur .tooltips').tooltip()
  m5dialNomsCommandes()
}

// Remplace les "#id#" par le nom complet des commandes ([Objet][Equipement][Commande]).
function m5dialNomsCommandes() {
  var ids = []
  $('#div_m5dialEditeur .m5dialNomCmd').each(function () {
    var id = $(this).attr('data-id')
    if (id !== '' && ids.indexOf(id) < 0) ids.push(id)
  })
  if (ids.length === 0) return
  $.ajax({
    type: 'POST',
    url: 'plugins/m5dial/core/ajax/m5dial.ajax.php',
    data: { action: 'nomsCommandes', ids: JSON.stringify(ids) },
    dataType: 'json',
    error: function (request, status, error) { handleAjaxError(request, status, error) },
    success: function (data) {
      if (data.state != 'ok') return
      $('#div_m5dialEditeur .m5dialNomCmd').each(function () {
        var id = $(this).attr('data-id')
        if (id !== '' && data.result[id] !== undefined) {
          $(this).val(data.result[id])
          if (data.result[id].indexOf('{{introuvable}}') >= 0 || data.result[id].indexOf('introuvable') >= 0) {
            $(this).css('color', 'var(--al-danger-color, #d9534f)')
          }
        }
      })
    }
  })
}

/* ------------------------------------------------------------------ */
/* Modifications                                                       */
/* ------------------------------------------------------------------ */
function m5dialCible(_el) {
  var cle = _el.attr('data-section')
  var index = parseInt(_el.attr('data-index'))
  if (index >= 0) {
    return m5dialConfig[cle][index]
  }
  if (m5dialConfig[cle] === undefined || typeof m5dialConfig[cle] !== 'object') m5dialConfig[cle] = {}
  return m5dialConfig[cle]
}

function m5dialModifier(_el, _valeur) {
  m5dialEcrire(m5dialCible(_el), _el.attr('data-chemin'), _valeur)
  m5dialEcrireJson()
}

$('#div_m5dialEditeur').off('change', '.m5dialSaisie').on('change', '.m5dialSaisie', function () {
  var v = $(this).val()
  if ($(this).attr('data-type') === 'nombre') {
    v = (v === '') ? '' : Number(v)
  }
  m5dialModifier($(this), v)
  if ($(this).attr('data-chemin') === 'nom') {
    $(this).closest('fieldset').find('legend').contents().first().replaceWith(document.createTextNode($(this).val() || '{{Sans nom}}'))
  }
})

$('#div_m5dialEditeur').off('click', '.m5dialChoisir').on('click', '.m5dialChoisir', function () {
  var bouton = $(this)
  jeedom.cmd.getSelectModal({ cmd: { type: bouton.attr('data-type') } }, function (result) {
    if (!result || !result.cmd || !result.cmd.id) return
    var champ = bouton.closest('.input-group').find('.m5dialNomCmd')
    champ.attr('data-id', result.cmd.id).val(result.human).css('color', '')
    m5dialModifier(bouton, parseInt(result.cmd.id))
  })
})

$('#div_m5dialEditeur').off('click', '.m5dialEffacer').on('click', '.m5dialEffacer', function () {
  $(this).closest('.input-group').find('.m5dialNomCmd').attr('data-id', '').val('')
  m5dialModifier($(this), '')
})

$('#div_m5dialEditeur').off('change', '.m5dialSectionActive').on('change', '.m5dialSectionActive', function () {
  var s = M5DIAL_SECTIONS.find(function (x) { return x.cle === $(this).attr('data-section') }, this)
  if ($(this).is(':checked')) {
    m5dialConfig[s.cle] = s.liste ? [] : {}
  } else {
    delete m5dialConfig[s.cle]
    if (s.cle === 'volets') delete m5dialConfig.voletPositionFermee
  }
  m5dialEcrireJson()
  m5dialAfficher()
})

$('#div_m5dialEditeur').off('click', '.m5dialAjouter').on('click', '.m5dialAjouter', function () {
  var cle = $(this).attr('data-section')
  if (!Array.isArray(m5dialConfig[cle])) m5dialConfig[cle] = []
  m5dialConfig[cle].push({ nom: '' })
  m5dialEcrireJson()
  m5dialAfficher()
})

$('#div_m5dialEditeur').off('click', '.m5dialSupprimer').on('click', '.m5dialSupprimer', function () {
  var cle = $(this).attr('data-section')
  var index = parseInt($(this).attr('data-index'))
  m5dialConfig[cle].splice(index, 1)
  m5dialEcrireJson()
  m5dialAfficher()
})

$('#div_m5dialEditeur').off('click', '.m5dialMonter').on('click', '.m5dialMonter', function () {
  var cle = $(this).attr('data-section')
  var i = parseInt($(this).attr('data-index'))
  var liste = m5dialConfig[cle]
  var tmp = liste[i - 1]
  liste[i - 1] = liste[i]
  liste[i] = tmp
  m5dialEcrireJson()
  m5dialAfficher()
})

$('#div_m5dialEditeur').off('click', '.m5dialMenuDeplacer').on('click', '.m5dialMenuDeplacer', function () {
  if ($(this).attr('disabled')) return
  var ordre = m5dialOrdreMenu()
  var i = parseInt($(this).attr('data-index'))
  var j = i + parseInt($(this).attr('data-sens'))
  if (j < 0 || j >= ordre.length) return
  var tmp = ordre[j]
  ordre[j] = ordre[i]
  ordre[i] = tmp
  m5dialConfig.menu = ordre
  m5dialEcrireJson()
  m5dialAfficher()
})

// Retour sur l'onglet Ecrans apres une modification manuelle du JSON.
$('a[href="#m5dialEcranstab"]').off('shown.bs.tab click.m5dial').on('click.m5dial', function () {
  setTimeout(m5dialAfficher, 50)
})

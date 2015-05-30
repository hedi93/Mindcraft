<?php
$string['mindcraft:addinstance'] = 'Ajouter une instance de mindcraft';
$string['mindcraft'] = 'Mindcraft';
$string['modulename'] = 'Mindcraft';
$string['modulenameplural'] = 'Mindcrafts';
$string['instances'] = 'Instances de mindcraft';
$string['pluginadministration'] = 'Administration de mindcraft';
$string['pluginname'] = 'Mindcraft';
$string['nomindcraftfound'] = 'Aucune carte mental trouvée';
$string['mapsnotvisible'] = 'Ce mindcraft contient des cartes mentales que vous ne pouvez pas consulter vu qu\'elles ne sont pas encore valides.';

$string['mindcraft_help'] = 'Un outils pour la création des cartes mentales';

$string['lastupdated'] = 'Dernière modification';
$string['uniquelink'] = 'Lien de cette carte mentale';

$string['invalidid'] = 'ID de mindcraft ID est incorrect';
$string['errorincorrectcmid'] = 'ID de Module de cours was incorrect.';
$string['coursemisconf'] = 'Cours mal configuré';
$string['errorinvalidmindcraft'] = 'Instance mindcraft incorrecte.';
$string['removeinstances'] = 'Retirer toutes les instances de mindcraft';

// event names
$string['eventmindmapupdated'] = 'Une carte mentale a été mise à jour.';
$string['eventmindmapunlocked'] = 'Une carte mentale a été déverrouillée.';

// add mindcraft form
$string['mindmapinteractive'] = 'Carte mentale interactive';
$string['mindmapinteractive_help'] = 'Autoriser les étudiants à discuter à propos de la carte.';
$string['interactive'] = 'Carte interactive';
$string['settingsfieldset'] = 'Paramètres de création des cartes';
$string['mindcraftfieldset'] = 'Exemple personnalisé des champs';
$string['mindcraftintro'] = 'Introduction au mindcraft';
$string['mindcraftname'] = 'Nom du mindcraft';
$string['cardnumber'] = 'Nombre des cartes';

// Listing mindmaps
$string['maps'] = 'Cartes';
$string['owner'] = 'Propriétaire';
$string['state'] = 'Etat';
$string['timecreated'] = 'Date de création';
$string['timemodified'] = 'Date de dernière modification';

// Menu
$string['control'] = 'Contrôle';
$string['history'] = 'Historique';
$string['edit'] = 'Editer';
$string['mapname'] = 'Nom de la carte';

// tooltip
$string['save'] = 'Enregistrer';
$string['getprevious'] = 'Récupérer la version précédente';
$string['undo'] = 'Annuler';
$string['redo'] = 'Refaire';
$string['add'] = 'Ajouter';
$string['delete'] = 'Supprimer';
$string['group'] = 'Grouper les éléments';
$string['ungroup'] = 'Annuler le groupement';
$string['emoticon'] = 'Emoticon';

// submenu
$string['smiley'] = 'Smiley';
$string['taskpriority'] = 'Priorité de tâche';
$string['taskprogress'] = 'Progression de tâche';
$string['symboles'] = 'Symboles';
$string['mounths'] = 'Mois';
$string['weekday'] = 'Jour de la semaine';

// buttons
$string['change'] = 'Changer';
$string['deletemap'] = 'Supprimer carte';
$string['validate'] = 'Valider';
$string['invalidate'] = 'Invalider';
$string['export'] = 'Exporter';

// Properties menu
$string['properties'] = 'Propriétés';
$string['description'] = 'Description';
$string['shape'] = 'Forme';
$string['border'] = 'Bordure';
$string['link'] = 'Lien';
$string['goto'] = 'Visiter le lien';
$string['image'] = 'Image';
$string['dropimage'] = 'Déposer une image ici';
$string['deleteimage'] = 'Supprimer l\'image';
$string['file'] = 'Fichier';
$string['dropfile'] = 'Déposer un fichier ici';
$string['deletefile'] = 'Supprimer le fichier';
$string['downloadfile'] = 'Télécharger le fichier';

// shapes
$string['rectangle'] = 'Rectangle';
$string['cloud'] = 'Nuage';
$string['ellipse'] = 'Ellipse';
$string['diamond'] = 'Losange';
$string['star'] = 'Etoile';

// border
$string['none'] = 'Aucun';
$string['thin'] = 'Fin';
$string['bold'] = 'Gras';

// Comment block
$string['comments'] = 'Commentaires';
$string['notopics'] = 'Aucun commentaire à propos de ce noeud';
$string['respond'] = 'Répondre';
$string['submit'] = 'Envoyer';

// other
$string['available'] = 'Disponible';
$string['inuse'] = 'En utilisation';
$string['inusebyyou'] = 'En utilisation par vous';
$string['addmap'] = 'Ajouer une nouvelle carte';
$string['mainsubject'] = 'Sujet central';
$string['reply'] = 'Répondre';
$string['update'] = 'Modifier';
$string['posted'] = 'Posté le';
$string['deletecomment'] = 'Suppression d\'un commentaire';
$string['surefordeletingcomment'] = 'Êtes-vous sûr de vouloir supprimer ce commentaire ?';
$string['commentsposted'] = 'commentaires ont été postés comme étant une réponse pour ce commentaire.';
$string['deletemindcraft'] = 'Suppression de la carte';
$string['surefordeletingmindcraft'] = 'Vous allez supprimer la carte <strong>{$a->mapname}</strong> qui est une instance de <strong>{$a->instanceof}</strong>. Cette opération est irréversible et Moodle lui-même ne crée pas une sauvegarde de récupération. <br>Voulez-vous <strong>vraiment</strong> continuer ?';
$string['instancesofmindcraft'] = 'Ce mindcraft contient <strong>{$a}</strong> instance(s) y compris celle à supprimer.';
$string['validatemindcraft'] = 'Validation de la carte';
$string['mindcraftvalidated'] = 'La carte <strong>{$a->mapname}</strong> qui est une instance de <strong>{$a->instanceof}</strong> à été validée avec succès. Maintenant, les étudiants inscrits au cours <strong>{$a->course}</strong> peuvent la consulter.';
$string['canupdatemindcraft'] = 'Si vous voulez apporter des modifications à cette carte, vous pouvez toujours l\'invalider, travailler dessus et la revalider quand vous voulez.';
$string['invalidatemindcraft'] = 'Invalidation de la carte';
$string['mindcraftinvalidated'] = 'La carte <strong>{$a->mapname}</strong> qui est une instance de <strong>{$a->instanceof}</strong> à été invalidée avec succès. Maintenat, les étudiants inscrits au cours <strong>{$a->course}</strong> ne peuvent pas la consulter.';
$string['canrevalidatemindcraft'] = 'Apportez vos modifications à cette carte et ensuite vous pouvez toujours la valider pour qu\'elle soit visible aux étudiants';
$string['returntomap'] = 'Revenir à la carte';
$string['continue'] = 'Continuer';
$string['cancel'] = 'Annuler';
$string['by'] = 'Par';

// capabilities
$string['mindcraft:addmaps'] = 'Ajouter une carte';
$string['mindcraft:editmaps'] = 'Modifier la carte';
$string['mindcraft:viewmaps'] = 'Consulter la carte';
$string['mindcraft:viewother'] = 'Consulter les autres cartes';

$string['responsecomment'] = 'Répondre à un commentaire';
$string['response'] = 'Votre réponse';
$string['map'] = 'carte';
$string['creator'] = 'Créateur';
$string['lastupdate'] = 'Dernière modification';
$string['unsupportedformat'] = 'Format non supporté';
$string['unsupportedformat'] = 'Taille maximale (5MO) dépassée';


$string['alreadyinuse'] = 'Cette carte mentale est en cours d\'utilisation';
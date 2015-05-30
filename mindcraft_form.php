<p style="text-align: right;"><button class="btn" id="btn-export" onclick="exportDiagram()"><img src="ressources/img/export.png" alt="" style="margin-right: 3px; position: relative; bottom: 1px;"/><?= get_string('export', 'mindcraft') ?></button></p>
<form action="save_mindmap.php" method="POST" id="carte-form">
    <input type="hidden" name="mindcraft_id" id="mindcraft_id" value="<?= $mindcraft_map->id ?>"/>
    <?php
        if(has_capability('mod/mindcraft:editmaps', $context)) :
            $sameinuse = $DB->get_record("mindcraft_used", array('mindcraftmapid' => $mindcraft_map->id));
            if(!$sameinuse){
                $mindcraftInUse = new stdClass();
                $mindcraftInUse->mindcraftmapid = $mindcraft_map->id;
                $mindcraftInUse->userid = $USER->id;
                $mindcraftInUse->ip = $_SERVER['REMOTE_ADDR'];
                $mindcraftInUse->date = time();
                if (!$DB->insert_record("mindcraft_used", $mindcraftInUse)) {
                    die("insertion failed");
                }
            } else {
                if($_SERVER['REMOTE_ADDR'] == $sameinuse->ip && $USER->id == $sameinuse->userid){
                    $sameinuse->date = time();
                    if (!$DB->update_record("mindcraft_used", $sameinuse)) {
                        die("update failed");
                    }
                }
                else{
                    if($mindcraft_map->state == 0 || has_capability('mod/mindcraft:editmaps', $context)){
                        echo '<p>'.get_string('alreadyinuse', 'mindcraft').'</p>';
                        redirect($CFG->wwwroot . "/mod/mindcraft/view.php?id=" . $cm->id);
                    }
                }
            }
    ?>
    <div class="mindcraft-menu clearfix">
        <div class="mindcraft-menu-buttons separator">
            <small class="mindcraft-menu-legend"><?= get_string('control', 'mindcraft') ?></small>
            <button type="submit" class="mindcraft-menu-btn" onclick="save()" title="<?= get_string('save', 'mindcraft') ?>"><img src="ressources/img/save.png" alt=""></button>
            <div class="mindcraft-menu-btn" onclick="getPrevious()" title="<?= get_string('getprevious', 'mindcraft') ?>"><img src="ressources/img/load.png" alt=""></div>
        </div>
        <div class="mindcraft-menu-buttons separator">
            <small class="mindcraft-menu-legend"><?= get_string('history', 'mindcraft') ?></small>
            <div class="mindcraft-menu-btn" onclick="undo()" title="<?= get_string('undo', 'mindcraft') ?>"><img src="ressources/img/undo.png" alt=""></div>
            <div class="mindcraft-menu-btn" onclick="redo()" title="<?= get_string('redo', 'mindcraft') ?>"><img src="ressources/img/redo.png" alt=""></div>
        </div>
        <div class="mindcraft-menu-buttons">
            <div class="mindcraft-menu-legend"><?= get_string('edit', 'mindcraft') ?></div>
            <div class="mindcraft-menu-btn hided" id="add-shape-menu-btn" title="<?= get_string('add', 'mindcraft') ?>">
                <img src="ressources/img/plus.png" alt="">
                <div class="add-shapes-menu submenu clearfix">
                    <p onclick="addFigure('RoundedRectangle')"><img src="ressources/img/rectangle.png" alt="" title="<?= get_string('rectangle', 'mindcraft') ?>"></p>
                    <p onclick="addFigure('Cloud')"><img src="ressources/img/cloud.png" alt="" title="<?= get_string('cloud', 'mindcraft') ?>"></p>
                    <p onclick="addFigure('Ellipse')"><img src="ressources/img/ellipse.png" alt="" title="<?= get_string('ellipse', 'mindcraft') ?>"></p>
                    <p onclick="addFigure('Diamond')"><img src="ressources/img/diamond.png" alt="" title="<?= get_string('diamond', 'mindcraft') ?>"></p>
                    <p onclick="addFigure('EightPointedStar')"><img src="ressources/img/star.png" alt="" title="<?= get_string('star', 'mindcraft') ?>"></p>
                    <p onclick="addFigure('File')"><img src="ressources/img/file.png" alt="" title="<?= get_string('file', 'mindcraft') ?>"></p>
                </div>
            </div>
            <div class="mindcraft-menu-btn" onclick="deleteSelection()" title="<?= get_string('delete', 'mindcraft') ?>"><img src="ressources/img/delete.png" alt=""></div>
            <div class="mindcraft-menu-btn" onclick="groupElements()" title="<?= get_string('group', 'mindcraft') ?>"><img src="ressources/img/group.png" alt=""></div>
            <div class="mindcraft-menu-btn" onclick="ungroupElements()" title="<?= get_string('ungroup', 'mindcraft') ?>"><img src="ressources/img/ungroup.png" alt=""></div>
            <div class="mindcraft-menu-btn hided" id="add-emoticon-menu-btn" title="<?= get_string('emoticon', 'mindcraft') ?>">
                <img src="ressources/img/emoticon.png" alt="">
                <div class="emoticon-menu submenu">
                    <div class="emoticon-menu-item">
                        <small><?= get_string('smiley', 'mindcraft') ?></small>
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/emoticon/happy.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/emoticon/laugh.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/emoticon/angry.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/emoticon/cry.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/emoticon/sad.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/emoticon/sleep.png" alt="">
                    </div>
                    <div class="emoticon-menu-item">
                        <small><?= get_string('taskpriority', 'mindcraft') ?></small>
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/priorities/1.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/priorities/2.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/priorities/3.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/priorities/4.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/priorities/5.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/priorities/6.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/priorities/7.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/priorities/8.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/priorities/9.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/priorities/10.png" alt="">
                    </div>
                    <div class="emoticon-menu-item">
                        <small><?= get_string('taskprogress', 'mindcraft') ?></small>
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/task/25.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/task/50.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/task/75.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/task/100.png" alt="">
                    </div>
                    <div class="emoticon-menu-item">
                        <small><?= get_string('symboles', 'mindcraft') ?></small>
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/symboles/tick.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/symboles/tick-rounded.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/symboles/plus.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/symboles/plus-rounded.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/symboles/stop.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/symboles/prev.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/symboles/next.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/symboles/redo.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/symboles/info.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/symboles/ok.png" alt="">
                    </div>
                    <div class="emoticon-menu-item">
                        <small><?= get_string('mounths', 'mindcraft') ?></small>
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/mounth/jan.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/mounth/feb.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/mounth/mar.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/mounth/apr.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/mounth/may.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/mounth/jun.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/mounth/jul.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/mounth/aug.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/mounth/sep.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/mounth/oct.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/mounth/nov.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/mounth/dec.png" alt="">
                    </div>
                    <div class="emoticon-menu-item">
                        <small><?= get_string('weekday', 'mindcraft') ?></small>
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/day/sun.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/day/mon.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/day/tue.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/day/wed.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/day/thu.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/day/fri.png" alt="">
                        <img onclick="addDefaultImg(this.getAttribute('src'))" src="ressources/images/default/day/sat.png" alt="">
                    </div>
                </div>
            </div>
        </div>
        <?php if($USER->id == $mindcraft_map->userid) : ?>
        <div class="mindcraft-menu-buttons" style="float:right">
            <small class="mindcraft-menu-legend"><?= get_string('state', 'mindcraft') ?></small>
            <a class="btn btn-danger" href="delete_mindcraft.php?mindcraft_id=<?= $mindcraft_map->id ?>"><?= get_string('deletemap', 'mindcraft') ?></a>
            <?php if($mindcraft_map->state == 0) : ?>
                <a class="btn btn-success" id="mindcraft_validate" href="validate_mindcraft.php?mindcraft_id=<?= $mindcraft_map->id ?>"><?= get_string('validate', 'mindcraft') ?></a>
            <?php else : ?>
                <a class="btn btn-warning" id="mindcraft_invalidate" href="validate_mindcraft.php?mindcraft_id=<?= $mindcraft_map->id ?>"><?= get_string('invalidate', 'mindcraft') ?></a>
            <?php endif ?>
        </div>
        <div class="mindcraft-menu-buttons separator" style="float:right">
            <small class="mindcraft-menu-legend"><?= get_string('mapname', 'mindcraft') ?></small>
            <input type="text" style="width: 160px;vertical-align: top;'" name="mindcraft_name" id="mindcraft_name" value="<?= $mindcraft_map->name ?>"/>
            <a class="btn btn-primary" id="mindcraft_name_submit" href="change_mindcraft_name.php"><?= get_string('change', 'mindcraft') ?></a>
        </div>
        <?php endif ?>
    </div>
    <?php endif; ?>
    <div id="myImages"></div>
    <div id="sample">
        <div id="myDiagram"></div>
        <div id="myOverview"></div>
        <div id="nodeinfo"></div>
        <textarea  id="mySavedModel" name="carte_json"><?=$mindcraft_map->jsondata; ?></textarea>
    </div>
</form>
<div class="clearfix" style="clear: both;">
    <?php if(has_capability('mod/mindcraft:editmaps', $context)) : ?>
        <div class="block-40">
            <ul class="map-menu-group clearfix" id="properties">
                <li class="carte-menu-item clearfix" style="border-bottom:0"><h4><?= get_string('properties', 'mindcraft') ?></h4></li>
                <li class="carte-menu-item clearfix">
                    <input type="text" onchange="updateData(this.value, 'color')" id="part-color" class="color-picker" style="display:none" value="#4c4c4c">
                    <label for="part-text"><?= get_string('description', 'mindcraft') ?></label>
                    <input type="text" onchange="updateData(this.value, 'text')" name="text" id="part-text" class="info-input" value="" readonly/>
                </li>
                <li class="carte-menu-item clearfix">
                    <label for="part-figure"><?= get_string('shape', 'mindcraft') ?></label>
                    <input type="text" onchange="updateData(this.value, 'background')" id="part-background" class="color-picker" style="display:none" value="#fff">
                    <select onchange="updateData(this.value, 'figure')" name="figure" id="part-figure" class="info-input" disabled>
                        <option value="null">--</option>
                        <option value="RoundedRectangle" style="background: url('ressources/img/rectangle.png') no-repeat 5px center;background-size: 16px 16px;"><?= get_string('rectangle', 'mindcraft') ?></option>
                        <option value="Cloud" style="background: url('ressources/img/cloud.png') no-repeat 5px center;background-size: 16px 16px;"><?= get_string('cloud', 'mindcraft') ?></option>
                        <option value="Ellipse" style="background: url('ressources/img/ellipse.png') no-repeat 5px center;background-size: 16px 16px;"><?= get_string('ellipse', 'mindcraft') ?></option>
                        <option value="Diamond" style="background: url('ressources/img/diamond.png') no-repeat 5px center;background-size: 16px 16px;"><?= get_string('diamond', 'mindcraft') ?></option>
                        <option value="EightPointedStar" style="background: url('ressources/img/star.png') no-repeat 5px center;background-size: 16px 16px;"><?= get_string('star', 'mindcraft') ?></option>
                        <option value="File" style="background: url('ressources/img/file.png') no-repeat 5px center;background-size: 16px 16px;"><?= get_string('file', 'mindcraft') ?></option>
                    </select>
                </li>
                <li class="carte-menu-item clearfix">
                    <label for="part-border"><?= get_string('border', 'mindcraft') ?></label>
                    <input type="text" onchange="updateData(this.value, 'border')" id="part-border" class="color-picker" style="display:none" value="#60ac60">
                    <select onchange="updateData(parseInt(this.value), 'borderWidth')" name="borderWidth" id="part-borderWidth" class="info-input" disabled>
                        <option value="null">--</option>
                        <option value="0"><?= get_string('none', 'mindcraft') ?></option>
                        <option value="2" style="background: url('ressources/img/border-2.png') no-repeat 5px center;background-size: 16px 16px;"><?= get_string('thin', 'mindcraft') ?></option>
                        <option value="4" style="background: url('ressources/img/border-4.png') no-repeat 5px center;background-size: 16px 16px;"><?= get_string('bold', 'mindcraft') ?></option>
                    </select>
                </li>
                <li class="carte-menu-item clearfix">
                    <a href="#" style="display: block;float: right; width: 52px; height: 32px;" id="part-link-button" target="_self" title="<?= get_string('goto', 'mindcraft') ?>"><img src="ressources/img/link-img.png" alt=""/></a>
                    <label for="part-text"><?= get_string('link', 'mindcraft') ?></label>
                    <input type="text" onchange="updateData(this.value, 'link')" name="link" id="part-link" class="info-input" readonly/>
                </li>
                <li class="carte-menu-item clearfix" id="carte-menu-img">
                    <label><?= get_string('image', 'mindcraft') ?></label>
                    <a class="link-delete-file" href="#" id="delete-img-btn" title="<?= get_string('deleteimage', 'mindcraft') ?>"><img src="ressources/img/delete-upload.png" alt=""/></a>
                    <div class="drop-file" id="drop-img"></div>
                </li>
                <li class="carte-menu-item clearfix" id="carte-menu-file">
                    <label><?= get_string('file', 'mindcraft') ?></label>
                    <p class="file-buttons">
                        <a class="btn-file" href="#" id="delete-file-btn" title="<?= get_string('deletefile', 'mindcraft') ?>"><img src="ressources/img/delete-upload.png" alt=""/></a>
                        <a class="btn-file" href="#" id="file-download-button" title="<?= get_string('downloadfile', 'mindcraft') ?>"><img src="ressources/img/downloads.png" alt=""/></a>
                    </p>
                    <div class="drop-file" id="drop-file"></div>
                </li>
            </ul>
        </div>
    <?php endif; ?>
        <?php if( has_capability('mod/mindcraft:addcomments', $context) || (has_capability('mod/mindcraft:addcommentswheninteractive', $context) && $mindcraft_map->interactive) ) : ?>
        <div <?php if( has_capability('mod/mindcraft:addcomments', $context)) echo 'class="block-60"' ?>>
            <form class="comment-form" id="comment-form" action="save_comment.php" method="POST">
                <h4 style="margin-bottom: 22px;"><?= get_string('comments', 'mindcraft') ?></h4>
                <div class="comments"></div>
                <input type="hidden" name="mindcraft_id" id="mindcraftid" value="<?= $mindcraft_map->id ?>"/>
                <input type="hidden" name="node_id" id="node_id"/>
                <h5><?= get_string('respond', 'mindcraft') ?></h5>
                <textarea name="comment_field" id="comment_field" style="width: 100%; box-sizing:border-box"></textarea>
                <input type="submit" value="<?= get_string('submit', 'mindcraft') ?>" id="btn-submit-comments"/>
                <small id="comment-alert" style="display:none; color: #CE4844; position: relative; bottom: 3px">Vous devez sauvegarder pour commenter</small>
            </form>
        </div>
        <?php endif; ?>
</div>
<script src="ressources/js/spectrum.js"></script>
<script src="ressources/js/go.js"></script>
<script src="ressources/js/jquery.form.min.js"></script>
<?php if(has_capability('mod/mindcraft:editmaps', $context)) : ?>
    <script src="ressources/js/app.js"></script>
    <script>
        var timerSetInUse = setInterval(setInUse, 5000);
    </script>
<?php else : ?>
    <script src="ressources/js/viewapp.js"></script>
<?php endif; ?>
<script src="ressources/js/save.js"></script>

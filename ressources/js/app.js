function init() {
  var $go = go.GraphObject.make;  // for conciseness in defining templates

  myDiagram =
    $go(go.Diagram, "myDiagram",  // must name or refer to the DIV HTML element
      {
        // start everything in the middle of the viewport
        initialContentAlignment: go.Spot.Center,
        initialAutoScale: go.Diagram.UniformToFill,

        // have mouse wheel events zoom in and out instead of scroll up and down
        "toolManager.mouseWheelBehavior": go.ToolManager.WheelZoom,
        // support double-click in background creating a new node
        "clickCreatingTool.archetypeNodeData": { 
          "text": (lang == "fr") ? "Nouveau noeud" : "New node",
          "color": "#4c4c4c",
          "figure": "RoundedRectangle", 
          "background": "#fff", 
          "border":  "#60ac60", 
          "borderWidth": 2, 
          "img": "images/null.png",
          "userid": userid,
          "timecreated": time(),
          "userupdate": "0",
          "timemodified": "0",
          "link": "#",
          "linkIcon": "images/null.png",
          "file": "null",
          "fileIcon": "images/null.png",
          "fileName": "null"
        },
        "ChangedSelection": onSelectionChanged,
        "TextEdited": onTextEdited,
        // enable undo & redo
        "undoManager.isEnabled": true
      });

  // when the document is modified, add a "*" to the title and enable the "submit" button to comment
  myDiagram.addDiagramListener("Modified", function(e) {
    var button = document.getElementById("btn-submit-comments");
    button.disabled = myDiagram.isModified;
    var idx = document.title.indexOf("*");
    if (myDiagram.isModified) {
      if (idx < 0) document.title = "*" + document.title;
      $("#comment-alert").show();
    } else {
      if (idx >= 0) document.title = document.title.substr(0, idx); 
    }
  });

  // show datas of the selected the node
  myDiagram.addDiagramListener("ObjectSingleClicked", function(e) {
    node = e.subject.part;
    if (!(node instanceof go.Link)){
      $('#part-text').val(node.part.data.text);
      $('#part-color').val(node.part.data.color);
      $('#part-figure').val(node.part.data.figure);
      $('#part-background').val(node.part.data.background);
      $('#part-borderWidth').val(node.part.data.borderWidth);
      $('#part-border').val(node.part.data.border);
      $('#part-link-button').attr('href', node.part.data.link);
      if(node.part.data.link != '#'){
        $('#part-link-button').attr('target', '_blank');
        $('#part-link').val(node.part.data.link);
      }
      else{
        $('#part-link').val('http://');
      }

      $('#node_id').val(node.part.data.id);

      $('input.info-input').attr('readonly', false);
      $('select.info-input').prop('disabled', false);
      $(".color-picker").spectrum('enable');

      $('.drop-file').remove();

      if(node.part.data.img != "images/null.png"){
        $('#carte-menu-img').append('<div class="drop-file" id="drop-img" data-value="' + node.part.data.img + '"><img src="ressources/' + node.part.data.img + '" alt=""/></div>');
      }
      else{
        var text = (lang == 'fr') ? "Déposez une image ici" : "Drop an image here";
        text += "<br><small>(png, gif, jpeg, jpg)</small>";
        $('#carte-menu-img').append('<div class="drop-file" id="drop-img"><span class="instruction-drop-file">' + text + '</span><span class="progress-drop-file"></span></div>');
      }
      if(node.part.data.file != "null"){
        $('#carte-menu-file').append('<div class="drop-file" id="drop-file" data-value="' + node.part.data.file + '" title="' + node.part.data.fileName + '"><img src="ressources/' + node.part.data.fileIcon + '" alt="' + node.part.data.fileName + '"/></div>');
        $('#file-download-button').attr('href', 'ressources/files/' + node.part.data.file);
      }
      else{
        var text = (lang == 'fr') ? "Déposez un fichier ici" : "Drop a file here";
        text += "<br><small>(docx, pptx, xlsx, zip, pdf)</small>";
        $('#carte-menu-file').append('<div class="drop-file" id="drop-file"><span class="instruction-drop-file">' + text + '</span><span class="progress-drop-file"></span></div>');
        $('#file-download-button').attr('href', '#');
      }
      $('.drop-file').dropfile();

      getComments();
    }
  });

  // define the Node template
  myDiagram.nodeTemplate =
    $go(go.Node, "Auto",
      new go.Binding("location", "loc", go.Point.parse).makeTwoWay(go.Point.stringify),
      // setting the main subject as a non deletable node
      new go.Binding("deletable", "id", isMainSubject),
      // define the node's outer shape, which will surround the TextBlock
      $go(go.Shape, 
        new go.Binding("figure", "figure"),
        new go.Binding("fill", "background"),
        new go.Binding("stroke", "border"),
        new go.Binding("strokeWidth", "borderWidth"),
        new go.Binding("link", "link"),
        new go.Binding("file", "file"),
        {
          portId: "",
          fromLinkable: true,
          fromLinkableSelfNode: true,
          fromLinkableDuplicates: true,
          toLinkable: true,
          toLinkableSelfNode: true,
          toLinkableDuplicates: true,
          cursor: "pointer"
        }
      ),
      $go(go.Panel, "Horizontal",
        $go(go.Picture,
          {
            maxSize: new go.Size(40, 40),
            margin: new go.Margin(6, -10, 6, 15),
            click : goToLink,
            cursor: "alias"
          },
          new go.Binding("source", "linkIcon", getfullpath)),
          $go(go.Picture,
              {
                maxSize: new go.Size(40, 40),
                margin: new go.Margin(6, -10, 6, 15),
                click : getFile,
                cursor: "pointer",
                toolTip:
                    $go(go.Adornment, "Auto",
                        $go(go.Shape, { fill: "whitesmoke", stroke: "black", strokeWidth: 1 }),
                        $go(go.TextBlock, { margin: 4 },
                            new go.Binding("text", "fileName"))
                    )
              },
              new go.Binding("source", "fileIcon", getfullpath)
          ),
          $go(go.Picture,
              {
                maxSize: new go.Size(40, 40),
                margin: new go.Margin(6, -10, 6, 15)
              },
              new go.Binding("source", "img", getfullpath)
          ),
        $go(go.Panel, "Table",
          {
            maxSize: new go.Size(150, 999),
            margin: new go.Margin(6, 20, 0, 3),
            defaultAlignment: go.Spot.Left
          },
          $go(go.RowColumnDefinition, { column: 4, width: 4 }),
          $go(go.TextBlock, 
            {
              row: 0, column: 0, columnSpan: 5,
              editable: true, isMultiline: false,
              minSize: new go.Size(10, 16),
              font: "normal 15px Segoe UI, bold arial, sans-serif",
              margin: 12
            },
            new go.Binding("stroke", "color"),
            new go.Binding("text", "text").makeTwoWay()
          )
        )
      )
    );

    // return the full path of the picture
    function getfullpath(key){
      return 'ressources/' + key;
    };

    // open the link added to the node in a new tab
    function goToLink(e, obj){
      var link = obj.part.data.link;
      if(link != '#'){
        window.open(link, '_blank');
      }
    }

  // allow the user to download the uploaded file
  function getFile(e, obj){
    var fileLink = 'ressources/files/';
    fileLink += obj.part.data.file;
    window.open(fileLink, '_blank');
  }

  // checking if the current node is the main subject or not
  function isMainSubject(key){
    if (key == 0) return false
    else return true;
  }

  // context menu on selected item
  myDiagram.nodeTemplate.contextMenu =
    $go(go.Adornment, "Vertical",
      $go("ContextMenuButton",
          $go(go.TextBlock,{
                text: "Informations",
                margin: 5
              },
              { click: getInfo })),
      $go("ContextMenuButton",
        $go(go.TextBlock,{
          text: (lang == "fr") ? "Supprimer" : "Delete",
          margin: 5
        },
        { click: function() { myDiagram.commandHandler.deleteSelection(); } }))
    );

  // get node info
  function getInfo(e, obj){
    var node = obj.part;
    var data = { "node_id": node.data.id, "mindcraft_id": $('#mindcraft_id').val(),
      "userid": node.data.userid, "timecreated": node.data.timecreated,
      "userupdate": node.data.userupdate, "timemodified": node.data.timemodified
    };
    var $nodeinfo = $('#nodeinfo');
    $nodeinfo.show();
    $nodeinfo.append('<div class="loader"></div>');
    $.ajax({
      url: "node_info.php",
      type: "POST",
      data: data
    })
    .done(function(data, text, jqxhr){
      $nodeinfo.html(jqxhr.responseText);
    })
    .fail(function(){
      $nodeinfo.hide();
      return false;
    })
    .always(function(){
      $nodeinfo.show();
      $('.loader', $nodeinfo).remove();
    })
  }

  // unlike the normal selection Adornment, this one includes a Button
  myDiagram.nodeTemplate.selectionAdornmentTemplate =
    $go(go.Adornment, "Spot",
      $go(go.Panel, "Auto",
        $go(go.Shape, { fill: null, stroke: "#4369bd", strokeWidth: 2 }),
        $go(go.Placeholder)  // this represents the selected Node
      ),
      // the button to create a "next" node, at the top-right corner
      $go("Button",
        {
          alignment: go.Spot.TopRight,
          click: addNodeAndLink  // this function is defined below
        },
        $go(go.Shape, "PlusLine", { desiredSize: new go.Size(6, 6) })
      ) // end button
    ); // end Adornment

  // clicking the button inserts a new node to the right of the selected node,
  // and adds a link to that new node
  function addNodeAndLink(e, obj) {
    var adorn = obj.part;
    e.handled = true;
    var diagram = adorn.diagram;
    diagram.startTransaction("Add State");

    // get the node data for which the user clicked the button
    var fromNode = adorn.adornedPart;
    var fromData = fromNode.data;
    // create a new "State" data object, positioned off to the right of the adorned Node
    var toData = { "text": (lang == "fr") ? "Nouveau noeud" : "New node", "figure": "RoundedRectangle",
      "background": "#fff", "color":"rgb(76, 76, 76)", "border":  "#60ac60", "borderWidth": 2, "img": "images/null.png",
      "userid": userid, "timecreated": time(), "userupdate": "0", "timemodified": "0",
      "link": "#", "linkIcon": "images/null.png", "file": "null", "fileIcon" : "images/null.png", "fileName" : "null" }
    var p = fromNode.location.copy();
    p.x += 200;
    toData.loc = go.Point.stringify(p);  // the "loc" property is a string, not a Point object
    // add the new node data to the model
    var model = diagram.model;
    model.addNodeData(toData);
    
    // create a link data from the old node data to the new node data
    var linkdata = {
      from: model.getKeyForNodeData(fromData),  // or just: fromData.id
      to: model.getKeyForNodeData(toData)
      //text: "transition"
    };
    // and add the link data to the model
    model.addLinkData(linkdata);
    
    // select the new Node
    var newnode = diagram.findNodeForData(toData);
    diagram.select(newnode);
    
    diagram.commitTransaction("Add State");
    
    // if the new node is off-screen, scroll the diagram to show the new node
    diagram.scrollToRect(newnode.actualBounds);
  }

  // replace the default Link template in the linkTemplateMap
  myDiagram.linkTemplate =
    $go(go.Link,  // the whole link panel
      { curve: go.Link.Bezier, adjusting: go.Link.Stretch, reshapable: true },
      new go.Binding("points").makeTwoWay(),
      new go.Binding("curviness", "curviness"),
      $go(go.Shape,  // the link shape
        { isPanelMain: true, stroke: "#6060ac", strokeWidth: 2 }),
      $go(go.Shape,  // the arrowhead
        { toArrow: "standard", stroke: "#6060ac", strokeWidth: 1, fill: "#6060ac" }),
        $go(go.Panel, "Auto"
      )
    );

  // context menu
   myDiagram.contextMenu =
   $go(go.Adornment, "Vertical",
     $go("ContextMenuButton",
       $go(go.TextBlock,{
          text: "Undo",
          margin: 5
       }),
       { click: function(e, obj) { e.diagram.commandHandler.undo(); } },
       new go.Binding("visible", "", function(o) {
           return o.diagram.commandHandler.canUndo();
         }).ofObject()),
     $go("ContextMenuButton",
       $go(go.TextBlock,{
          text: "Redo",
          margin: 5
       }),
       { click: function(e, obj) { e.diagram.commandHandler.redo(); } },
       new go.Binding("visible", "", function(o) {
           return o.diagram.commandHandler.canRedo();
         }).ofObject())
   );

   // create group
   myDiagram.groupTemplate =
    $go(go.Group, "Vertical",
      { selectionObjectName: "PANEL",
        ungroupable: true },
      $go(go.TextBlock,
        { font: "bold 12pt sans-serif" },
        new go.Binding("text", "", go.Binding.toString),
        new go.Binding("stroke", "color")),
      $go(go.Panel, "Auto",
        { name: "PANEL" },
        $go(go.Shape, "Rectangle",
          { fill: "rgba(128,128,128,0.2)",
            stroke: "gray", strokeWidth: 3 }),
        $go(go.Placeholder,
          { padding: 5})
      )
    );

    myDiagram.commandHandler.archetypeGroupData =
    { key: "", isGroup: true, color: "rgb(128,128,128)" };

    // Overview
    myOverview =
      $go(go.Overview, "myOverview",  // the HTML DIV element for the Overview
        { observed: myDiagram });   // tell it which Diagram to show and pan

  // read in the JSON-format data from the "mySavedModel" element
  load();
}

// Show the diagram's model in JSON format
function save() {
  document.getElementById("mySavedModel").value = myDiagram.model.toJson();
  myDiagram.isModified = false;
  var msg;
  if(lang == "fr"){
    msg = "Carte enregistrée avec succès";
  }
  else{
    msg = "Map has been saved successfully"
  }
  $("#carte-form").ajaxForm(function() {
    alert(msg);
  });
  $("#btn-submit-comments").removeAttr('disabled');
  $("#comment-alert").hide();
}

//
function load() {
  myDiagram.model = go.Model.fromJson(document.getElementById("mySavedModel").value);
}

//Allows you to add nodes with different figures
function addFigure(figure){
  var toData = { "text": (lang == "fr") ? "Nouveau noeud" : "New node", "figure": figure,
    "background": "#fff", "color":"rgb(76, 76, 76)", "border":  "#60ac60", "borderWidth": 2, "img": "images/null.png",
    "userid": userid, "timecreated": time(), "userupdate": "0", "timemodified": "0",
    "link": "#", "linkIcon": "images/null.png", "file": "null", "fileIcon" : "images/null.png", "fileName" : "null" }
  var model = myDiagram.model;
  myDiagram.startTransaction("Add State");
  model.addNodeData(toData);
  myDiagram.commitTransaction("Add State");
}

//Allows you to group elements
function groupElements(){
  myDiagram.startTransaction("Create groupe");
  myDiagram.commandHandler.groupSelection();
  myDiagram.commitTransaction("Create groupe");
}

//Allows you to ungroup elements
function ungroupElements(){
  myDiagram.startTransaction("ungroupe");
  myDiagram.commandHandler.ungroupSelection();
  myDiagram.commitTransaction("ungroupe");
}

// undo a task
function undo(){
  myDiagram.commandHandler.undo();
}

// redo a task
function redo(){
  myDiagram.commandHandler.redo();
}

// delete a selection
function deleteSelection(){
  myDiagram.commandHandler.deleteSelection();
}

// update data when modifiying from the pop up
function updateData(text, field) {
  var node = myDiagram.selection.first();
  var data = node.data;
  if (node instanceof go.Node && data !== null) {
    var model = myDiagram.model;
    model.startTransaction("modified " + field);
    if (field === "id") {
      model.setDataProperty(data, "id", text);
    } else if (field === "text") {
      model.setDataProperty(data, "text", text);
    } else if (field === "figure") {
      model.setDataProperty(data, "figure", text);
    } else if (field === "borderWidth") {
      model.setDataProperty(data, "borderWidth", text);
    } else if (field === "background") {
      model.setDataProperty(data, "background", text);
    } else if (field === "color") {
      model.setDataProperty(data, "color", text);
    } else if (field === "border") {
      model.setDataProperty(data, "border", text);
    } else if (field === "link") {
      model.setDataProperty(data, "link", text);
      if(text == ""){
        model.setDataProperty(data, "link", "#");
        model.setDataProperty(data, "linkIcon", "images/null.png");
        document.getElementById("part-link-button").href = "#";
        document.getElementById("part-link-button").target = "_self";
      }
      else{
        model.setDataProperty(data, "link", text);
        model.setDataProperty(data, "linkIcon", "images/link-icon.png");
        document.getElementById("part-link-button").href = text;
      }
    } else if (field === "file") {
      model.setDataProperty(data, "file", text);
    }
    model.setDataProperty(data, "userupdate", userid);
    model.setDataProperty(data, "timemodified", time());
    model.commitTransaction("modified " + field);
  }
}

// Allow the user to edit text when a single node is selected
function onSelectionChanged(e) {
    var node = e.diagram.selection.first();
    if (node instanceof go.Node) {
      updateProperties(node.data);
    } else {
      updateProperties(null);
    }
  }

// Update the HTML elements for editing the properties of the currently selected node
function updateProperties(data) {
  if (data !== null) {
    document.getElementById("part-text").value = data.text || "";
    document.getElementById("part-color").value = data.color || "";
    document.getElementById("part-figure").value = data.figure || "";
    document.getElementById("part-background").value = data.background || "";
    document.getElementById("part-border").value = data.border || "";
    document.getElementById("part-borderWidth").value = data.borderWidth || "";
    document.getElementById("part-link").value = (data.link != '#') ? data.link : "http://";
    document.getElementById("part-link-button").href = data.link || "";
  } else {
    document.getElementById("part-text").value = "";
    document.getElementById("part-color").value = "#fff";
    document.getElementById("part-figure").value = "null";
    document.getElementById("part-background").value = "#fff";
    document.getElementById("part-border").value = "#fff";
    document.getElementById("part-borderWidth").value = "null";
    document.getElementById("node_id").value = "";
    document.getElementById("part-link").value = "";
    document.getElementById("part-link-button").href = "#";
    document.getElementById("part-link-button").target = "_self";
    document.getElementById("file-download-button").href = "#";

    $('.drop-file').remove();
    $('#carte-menu-img').append('<div class="drop-file" id="drop-img"></div>');
    $('#carte-menu-file').append('<div class="drop-file" id="drop-file"></div>');
    $('input.info-input').attr('readonly', true);
    $('select.info-input').prop('disabled', 'disabled');
    $(".color-picker").spectrum('disable');
    $('.comments').html('');
    $('#nodeinfo').html('').hide();
  }
}

// This is called when the user has finished inline text-editing
function onTextEdited(e) {
  var tb = e.subject;
  if (tb === null || !tb.name) return;
  var node = tb.part;
  if (node instanceof go.Node) {
    myDiagram.model.setDataProperty(node.part.data, "userupdate", userid);
    myDiagram.model.setDataProperty(node.part.data, "timemodified", time());
    updateProperties(node.data);
  }
}

// delete file
$("#delete-file-btn").click(function(e){
  e.preventDefault();
  var node = myDiagram.selection.first();
  myDiagram.model.startTransaction("delete file");
  myDiagram.model.setDataProperty(node.data, "file", "null");
  myDiagram.model.setDataProperty(node.data, "fileIcon", "images/null.png");
  myDiagram.model.setDataProperty(node.data, "userupdate", userid);
  myDiagram.model.setDataProperty(node.data, "timemodified", time());
  $('img', '#drop-file').remove();
  var text = (lang == 'fr') ? "Déposez un fichier ici" : "Drop a file here";
  text += "<br><small>(docx, pptx, xlsx, zip, pdf)</small>";
  $('#drop-file').append('<span class="instruction-drop-file">' + text + '</span><span class="progress-drop-file"></span>');
  $('.drop-file').dropfile();
  myDiagram.model.commitTransaction("delete file");
})

// delete picture
$("#delete-img-btn").click(function(e){
  e.preventDefault();
  var node = myDiagram.selection.first();
  myDiagram.model.startTransaction("delete img");
  myDiagram.model.setDataProperty(node.data, "img", "images/null.png");
  $('img', '#drop-img').remove();
  var text = (lang == 'fr') ? "Déposez une image ici" : "Drop an image here";
  text += "<br><small>(png, gif, jpeg, jpg)</small>";
  $('#drop-img').append('<span class="instruction-drop-file">' + text + '</span><span class="progress-drop-file"></span>');
  $('.drop-file').dropfile();
  myDiagram.model.setDataProperty(node.data, "userupdate", userid);
  myDiagram.model.setDataProperty(node.data, "timemodified", time());
  myDiagram.model.commitTransaction("delete img");
})

// Tool bar pop up
$('#add-shape-menu-btn, #add-emoticon-menu-btn').click(function(e){
  e.preventDefault();
  e.stopPropagation();
  var $this;
  var $that;
  if($(this).attr('id') == 'add-shape-menu-btn'){
    $this = $('#add-shape-menu-btn');
    $that = $('#add-emoticon-menu-btn');
  }
  else{
    $this = $('#add-emoticon-menu-btn');
    $that = $('#add-shape-menu-btn');
  }
  if($this.hasClass('hided')){
    $this.removeClass('hided');
    $that.addClass('hided');
    $('.submenu', $this).show();
    $('.submenu', $that).hide();
  }
  else{
    $this.addClass('hided');
    $('.submenu', $this).hide();
  }
});

$('html').click(function(e){
  e.stopPropagation();
  $('.submenu', '.mindcraft-menu-btn').hide();
  $('#add-shape-menu-btn').addClass('hided');
  $('#add-emoticon-menu-btn').addClass('hided');
})

$('.submenu', '.mindcraft-menu-btn').click(function(e){
  e.stopPropagation();
  $(this).parent().removeClass('hided');
  $(this).show();
})

// dropfile area
$.fn.dropfile = function(){
  var script = "upload.php";
  this.each(function(){
    $(this).bind({
      dragenter: function(e){
        e.preventDefault();
      },
      dragover: function(e){
        e.preventDefault();
        $(this).addClass('hover');
      },
      dragleave: function(e){
        e.preventDefault();
        $(this).removeClass('hover');
      }
    });
    this.addEventListener('drop', function(e){
      e.preventDefault();
      var files = e.dataTransfer.files;
      upload(files, $(this), 0)
    }, false)
  });

  function upload(files, field, index){
    var file = files[index];
    var upload_type;
    var node = myDiagram.selection.first();
    var xhr = new XMLHttpRequest();
    var progress = field.find('.progress-drop-file');

    if(field.attr('id') == 'drop-img'){
      upload_type = 'img';
    }
    else if(field.attr('id') == 'drop-file'){
      upload_type = 'file';
    }

    xhr.addEventListener('load', function(e){
      field.removeClass('hover');
      progress.css({height:0});
      if(!e.target.responseText){
        var text = (lang == 'fr') ? "Une erreur s'est produite, veuillez réessayer ou changer le type du fichier" : "An error occured, please try again or try to change the file format"
        alert(text);
        return false;
      }
      var json = JSON.parse(e.target.responseText);
      if(json.error){
        alert(json.error);
        return false;
      }
      field.remove('img');
      $('.instruction-drop-file', field).css({opacity:0});
      field.append(json.content);
      myDiagram.model.startTransaction("modified " + upload_type);
      if(upload_type == 'img'){
        myDiagram.model.setDataProperty(node.data, "img", "images/" + json.name);
      }
      else if(upload_type == 'file'){
        myDiagram.model.setDataProperty(node.data, "file", json.name);
        myDiagram.model.setDataProperty(node.data, "fileIcon", json.fileIcon);
        myDiagram.model.setDataProperty(node.data, "fileName", json.nameforusers);
        $('#file-download-button').attr('href', 'ressources/files/' + node.part.data.file);
        field.attr('data-value', node.part.data.file);
      }
      myDiagram.model.setDataProperty(node.data, "userupdate", userid);
      myDiagram.model.setDataProperty(node.data, "timemodified", time());
      myDiagram.model.commitTransaction("modified " + upload_type);
    }, false);

    xhr.upload.addEventListener('progress', function(e){
      if(e.lengthComputable){
        var perc = (Math.round(e.loaded / e.total) * 100) + '%';
        progress.css({height:perc}).html(perc);
      }
    }, false)

    xhr.open('post', script, true);
    xhr.setRequestHeader('content-type', 'multipart/form-data');
    xhr.setRequestHeader('x-upload-type', upload_type);
    xhr.setRequestHeader('x-file-type', file.type);
    xhr.setRequestHeader('x-file-name', file.name);
    xhr.setRequestHeader('x-file-size', file.size);
    xhr.setRequestHeader('x-mindcraft-id', $('#mindcraft_id').val());
    xhr.setRequestHeader('x-node-id', node.data.id);
    for(var i in field.data()){
      if(typeof field.data(i) !== 'object'){
        xhr.setRequestHeader('x-param-'+i, field.data(i))
      }
    }
    xhr.send(file);
  }
}

// color picker input
$(".color-picker").spectrum({
    showPaletteOnly: true,
    togglePaletteOnly: true,
    togglePaletteMoreText: 'more',
    togglePaletteLessText: 'less',
    chooseText: "Choose",
    hideAfterPaletteSelect: true,
    disabled: true,
    palette: [
        ["#000","#444","#4c4c4c","#999","#ccc","#eee","#f3f3f3","#fff"],
        ["#f00","#f90","#ff0","#0f0","#0ff","#00f","#90f","#f0f"],
        ["#f4cccc","#fce5cd","#fff2cc","#d9ead3","#d0e0e3","#cfe2f3","#d9d2e9","#ead1dc"],
        ["#ea9999","#f9cb9c","#ffe599","#b6d7a8","#a2c4c9","#9fc5e8","#b4a7d6","#d5a6bd"],
        ["#e06666","#f6b26b","#ffd966","#93c47d","#76a5af","#6fa8dc","#8e7cc3","#c27ba0"],
        ["#c00","#e69138","#f1c232","#60ac60","#45818e","#3d85c6","#674ea7","#a64d79"],
        ["#900","#b45f06","#bf9000","#38761d","#134f5c","#0b5394","#351c75","#741b47"],
        ["#600","#783f04","#7f6000","#274e13","#0c343d","#073763","#20124d","#4c1130"]
    ]
});

// default img add link
function addDefaultImg(src){
  src = src.replace('ressources/', '');
  myDiagram.model.startTransaction("modified img");
  var selection = myDiagram.selection.first();
  if(selection != null) {
    var img = selection.data.img;
    myDiagram.model.setDataProperty(selection.data, "img", src);
    myDiagram.model.setDataProperty(selection.data, "userupdate", userid);
    myDiagram.model.setDataProperty(selection.data, "timemodified", time());
    myDiagram.model.commitTransaction("modified img");
  }
  else{
    return false;
  }

}

// get all comments of the selected node
function getComments(){
    var formData = $('#comment-form').serialize();
    var $comments = $('.comments');
    $comments.append('<div class="loader"></div>');
    $.ajax({
      url: 'get_comment.php',
      type: 'POST',
      data: formData
    })
    .done(function(data, text, jqxhr){
      $comments.html(jqxhr.responseText);
    })
    .fail(function(jqxhr){
      alert('An error occured, please try again.')
    })
    .always(function(){
      $('.loader', $comments).remove();
    })
}

// get previous version of the map
function getPrevious(){
  var formData = $('#carte-form').serialize();
  $.ajax({
    url: 'get_previous.php',
    type: 'POST',
    data: formData
  })
  .done(function(data, text, jqxhr){
    $('#mySavedModel').val(jqxhr.responseText);
    var msg = (lang == "fr") ? "Cette version n'est pas encore sauvegardée, faite le si vous voulez" : "This version is not saved yet, save it if you want to";
    alert(msg);
    load();
  })
  .fail(function(jqxhr){
    alert('An error occured, please try again.')
  })
}

// set map in use to lock it
function setInUse(){
  var data = { mindcraftid : $('#mindcraft_id').val(), action : "setInUse" }
  $.ajax({
    url: "collaboration.php",
    type: "POST",
    data: data
  })
  .done(function(){
    console.log('checking...')
  })
}

// equivalent of php time() function
function time() {
  return Math.floor(new Date().getTime() / 1000);
}

// export diagram to png format
function exportDiagram(){
  var imgDiv = document.getElementById('myImages');
  imgDiv.innerHTML = ''; // clear out the old images, if any
  var db = myDiagram.documentBounds.copy();
  var boundswidth = db.width;
  var boundsheight = db.height;
  var imgWidth = boundswidth;
  var imgHeight = boundsheight;
  var p = db.position.copy();
  for (var i = 0; i < boundsheight; i += imgHeight) {
    for (var j = 0; j < boundswidth; j += imgWidth) {
      img = myDiagram.makeImage({
        scale: 1,
        position: new go.Point(p.x + j, p.y + i),
        size: new go.Size(imgWidth, imgHeight),
        background: "#fff"
      });
      // Append the new HTMLImageElement to the #myImages div
      var url = img.getAttribute('src');
      window.open(url,'Image');
      //imgDiv.appendChild(img);
    }
  }
}

// lock the map
setInUse();

// init the app
init();


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

                "ChangedSelection": onSelectionChanged
            });

    myDiagram.isReadOnly = true;

    // when the document is modified, add a "*" to the title and enable the "submit" button to comment
    myDiagram.addDiagramListener("Modified", function(e) {
        var button = document.getElementById("btn-submit-comments");
        if(button) button.disabled = false;
    });

    myDiagram.addDiagramListener("ObjectSingleClicked", function(e) {
        node = e.subject.part;
        $('#node_id').val(node.part.data.id);
        getComments();
    });

    // define the Node template
    myDiagram.nodeTemplate =
        $go(go.Node, "Auto",
            new go.Binding("location", "loc", go.Point.parse).makeTwoWay(go.Point.stringify),
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
                        cursor: "pointer"
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
                            margin: 12,
                            editable: false
                        },
                        new go.Binding("stroke", "color"),
                        new go.Binding("text", "text").makeTwoWay()
                    )
                )
            )
        );
    // return the src of the picture
    function getfullpath(key){
        return 'ressources/' + key;
    };

    function goToLink(e, obj){
        var link = obj.part.data.link;
        if(link != '#'){
            window.open(link, '_blank');
        }
    }

    function getFile(e, obj){
        var fileLink = 'ressources/files/';
        fileLink += obj.part.data.file;
        window.open(fileLink, '_blank');
    }

    // unlike the normal selection Adornment, this one includes a Button
    myDiagram.nodeTemplate.selectionAdornmentTemplate =
        $go(go.Adornment, "Spot",
            $go(go.Panel, "Auto",
                $go(go.Shape, { fill: null, stroke: "#4369bd", strokeWidth: 2 }),
                $go(go.Placeholder)  // this represents the selected Node
            )
        ); // end Adornment

    // replace the default Link template in the linkTemplateMap
    myDiagram.linkTemplate =
        $go(go.Link,  // the whole link panel
            { curve: go.Link.Bezier, adjusting: go.Link.Stretch, reshapable: false },
            new go.Binding("points").makeTwoWay(),
            new go.Binding("curviness", "curviness"),
            $go(go.Shape,  // the link shape
                { isPanelMain: true, stroke: "#6060ac", strokeWidth: 2 }),
            $go(go.Shape,  // the arrowhead
                { toArrow: "standard", stroke: "#6060ac", strokeWidth: 1, fill: "#6060ac" }),
            $go(go.Panel, "Auto"
            )
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

    // Overview
    myOverview =
        $go(go.Overview, "myOverview",  // the HTML DIV element for the Overview
            { observed: myDiagram });   // tell it which Diagram to show and pan

    // read in the JSON-format data from the "mySavedModel" element
    load();
}

// Show the diagram's model in JSON format
function load() {
    myDiagram.model = go.Model.fromJson(document.getElementById("mySavedModel").value);
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

function onSelectionChanged(e) {
    var node = e.diagram.selection.first();
    if (node instanceof go.Node) {
        updateProperties(node.data);
    } else {
        updateProperties(null);
    }
}

function updateProperties(data) {
    if (data == null) $('.comments').html('');
}

// init the app
init();


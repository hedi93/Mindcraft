$(function(){

	$("#comment-form").ajaxForm(function() {
		getComments();
		$('#comment_field').clearFields();
	});

	$('#mindcraft_name_submit').click(function(e){
		e.preventDefault();
		var newname = $('#mindcraft_name').val();
		newname = $.trim(newname);
		$('#mindcraft_name').val(newname);
		if(newname == ""){
			if(lang === 'fr'){
				var msg = "Veuillez saisir un nom pour la carte";
			}
			else{
				var msg = "Please enter a name for the mindmap";
			}
			alert(msg);
			return false;
		}
		var $this = $(this);
		var text = $this.text();
		if(lang === 'fr'){
			$this.text('Patientez ...');
		}
		else{
			$this.text('Wait ...');
		}
		url = $(this).attr('href');
		data = { mindcraft_id : $('#mindcraft_id').val(), mindcraft_name : newname }
		$.ajax({
			type : 'POST',
			url	 : url,
			data : data
		})
		.done(function(data, text, jqxhr){
			var msg = (lang == "fr") ? "Le nom de la carte a été changé": "Mind map name has been changed";
			alert(msg);
			$('#mindcraft_title').text($('#mindcraft_name').val())
		})
		.fail(function(jqxhr){
			alert('fail');
		})
		.always(function(){
			$this.text(text);
		});
	})
});
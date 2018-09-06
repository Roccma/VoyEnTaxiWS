function validar(){
	alert("Error");
	$.ajax({
	  method: "POST",
	  url: "some.php",
	  data: { name: "John", location: "Boston" }
	})
	  .done(function( msg ) {
	    alert( "Data Saved: " + msg );
	  });
	return false;
}

function cargarFavoritos(){
	jQuery.noConflict();
	$("#favoritosModal").modal('show');

    var exampleValues = {},
      parsedTemplate = "",
      templateText = $('#favoritosTemplate').html(),
      demoTemplate = _.template(templateText);
    $.ajax(
    	{url: "/tip/ejemplos/framework/usuario/favoritos/",
     	async: false, 
     	dataType: "json", 
     	success: function(json) {
    		exampleValues = json;
    	}
    });

    for (usr in exampleValues) {
      parsedTemplate += demoTemplate(exampleValues[usr]);
    }
    $("#favoritosTable > tbody").html(parsedTemplate);
    
}

$( document ).ready(function() {
//$(function() {
    console.log( "ready!" );
    $("#contenedor").find('li').each(function(j, li){
        console.log($(this).attr("id")+" - "+$(this).text());
    })
});
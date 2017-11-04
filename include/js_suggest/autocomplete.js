var MIN_LENGTH = 3;

/* include/js_suggest/suggest.php */
function suggest($a)
{
	$('#keyword').val('');
    $('#keyword').val($($a).text());
	$('#keyword').focus();
}


$( document ).ready(function() {
	$("#keyword").keyup(function() {
		var keyword = $("#keyword").val();
		if (keyword.length >= MIN_LENGTH) {
			
			
		 $.ajax({

				type: "GET",
				contentType: "application/json; charset=utf-8",
				url: "include/js_suggest/suggest.php",
				data: {'keyword' : keyword},
				dataType: "json",
				success: function (data) {
					//alert(data);
					$('#sresults').append('<div class="item" onclick="suggest(this)">' + data + '</div>');
				},
				error: function (result) {
					alert("Error");
				}
			});
			
			
		} else {
			$('#results').html('');
		}
	});

    $("#keyword").blur(function(){
    		$("#sresults").fadeOut(500);
    	})
        .focus(function() {
    	    $("#sresults").show();
    	});

});

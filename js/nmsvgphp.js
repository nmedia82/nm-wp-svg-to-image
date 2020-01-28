(function($){
    
    $("#nmsvgphp_btn").on('click', function(){
       
       const endpoint = nmsvgphp_vars.site_url+'/wp-json/nmsvgphp/v1/start';
       const data = {"class_value":"dillard-class", 
                        "text_value" : $("#svg_text").val(),
                        "template_name": 'dillard.svg'
                        };
       
       $('#nmsvgphp_img').html("Generating ...");
       
       $.ajax({
          type: "GET",
          url:endpoint,
          contentType: "image/svg",
          data: data,
          success: function(resp){
              console.log(resp);
          $('#nmsvgphp_img').html('<img src="' + resp + '" />'); },
        } );
 
    });
}(jQuery));
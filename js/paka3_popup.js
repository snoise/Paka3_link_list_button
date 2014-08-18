jQuery(function($) {
				var link_array = []
				$(document).ready(function() {


				//チェックボックスをクリックしたら
				$(document).on('click','.pgp_chk', function(){
					target = $(this).val();

				if( $(this).attr('id') != "opt_li" ){
					if( $(this).attr('checked')){
						link_array.push( target );
					}else{
						link_array.some(function(v, i){
							if ( v == target ) link_array.splice(i,1);    
						});
					}
				}

					//alert(link_array);
				  $("#preview").empty();

				 //pgp_chk_
					var str = "";
					$.each(link_array, function() {
						obj = $( '#pgp_title_' + this ).children('a');
						obj_title = obj.text();
						obj_link = obj.attr("href");
						//console.log( obj_title );
					
						if($('[name="opt_li"]:checked').length){
							str += "<li>"
							str += "<a href ='" + obj_link + "' title='" + obj_title + "'>" + obj_title + "</a>";
							str += "</li>";
						}else{
							str += "<a href ='" + obj_link + "' title='" + obj_title + "'>" + obj_title + "</a>";
							str += "<br />";
						}
					});

			
					if($('[name="opt_li"]:checked').length){
						str = "<ul class='pgp_ul'>" + str + "</ul>";
					}
					 $("#preview").append(str);
				});



					//OKクリックされたら
					$('#paka3_ei_btn_yes').on('click', function() {
						var str = $("#preview").html();
						//inlineのときはwindow
						top.send_to_editor( str );
						top.tb_remove(); 
					});

					$('#paka3_ei_btn_no').on('click', function() {
						top.tb_remove(); 
					});
					
					//Enterキーが入力されたとき
					$('#paka3_editer_insert_content').on('keypress',function () {
						if(event.which == 13) {
							$('#paka3_ei_btn_yes').trigger("click");
						}
						//Form内のエンター：サブミット回避
						return event.which !== 13;
					});

				});

})
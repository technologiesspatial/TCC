Dropzone.autoDiscover = false;
  
function multiUploader() {

    if($("#documentUploader").length==1)
    {	
    docMydropzone = $("#documentUploader").dropzone({
        init: function() {
            var thisDropzone = this;
        },
       // maxFiles:100,
        url: baseUrl + "/upload-prodpics",
        init: function() {
            var that = this;
        },
        accept: function(file, done) {
			$.blockUI({
				css: {
					border: 'none',
					padding: '5px',
					width: '24%',
					left: '39%',
					backgroundColor: '#000000',
					border: 'solid 1px #424445',
					opacity: .9,
					color: '#ffffff',
				},
				message: "<div class='Loader'><div class='Text' style='letter-spacing: 2px;'>Uploading...</div></div>",
			});
            var str1 = file.type;
            var str2 = "image";       
            if (str1.indexOf(str2) != -1) {	
                if($('.dz-preview').length>6){	
                    $('#documentUploader').hide();

                }
                if($('.dz-preview').length>=1){	
                    $('#onepicreq').hide();
                }else{
                    $('#onepicreq').show();
                }
                if($('.dz-preview').length>6){	
                    $('#documentUploader').hide();
                    this.removeFile(file);
                    $.unblockUI();
                    done("check");
					showAppAlert("The Collective Coven", "You can upload maximum 6 files only", "warning");
                }else{	
                    // $('#documentUploader').show();

                var IsTrue = checkMimeFileType(file, "image", done);    
                }            
							
            } else {
                $('#sessionForm .applyBtn').attr("disabled", true);
                done();
            }
            
        },
        uploadprogress: function(file, progress, bytesSent) { 
            // $(file.previewElement).wrap("<li></li>");

            }, 
        success: function(file, response) {
			$.unblockUI();
            $('[type=submit]').removeAttr("disabled");
            var result = jQuery.parseJSON(response);
            // console.log(result);
            var filesize = file.width;
            $('#documentPreview').removeClass('d-none');
			$('.category_picpicks').each(function(index, element) {
				var tid = $(this).attr("data-id");
				$(".dz-preview[id='img"+tid+"']").parents("li").remove();
				$(this).removeAttr("checked");
			})
            if (result == 'false' || !result) {
                var _this = this;
                file.previewElement.parentNode.removeChild(file.previewElement);
				showAppAlert("The Collective Coven", "Please Select Valid Image", "warning");
                //done("Please Select Valid Image");      
            } else {
                var image_name = result['media_path'];
                $('#documentPreview .dz-image-preview:last-child').attr("id", image_name);
                $(file.previewTemplate).append('<input class="server_file" type="hidden" value="' + image_name + '" /><input class="server_file_type" type="hidden" value="0" /><input type="hidden" name="doc_org_name[' + image_name + ']" value="' + file.name + '" />');
                $(file.previewTemplate).wrap("<li></li>");
				$("#product_chk").val(btoa("coven"));
				$(".help-block[for='product_chk']").remove();
            }
        },
        timeout: 30000000000,
        error: function(file, message) {
            var _this = this;
            file.previewElement.parentNode.removeChild(file.previewElement);
            _this.removeFile(file);
            //showAppAlert("",'Please select the Valid File',"error");
            //alert(message);
            $('[type=submit]').removeAttr("disabled");
        },
        canceled: function(file) {
            var _this = this;
            file.previewElement.parentNode.removeChild(file.previewElement);
            _this.removeFile(file);
        },

        acceptedFiles: "image/*",
        acceptedFiles: '.jpg, .png, .jpeg',
        autoDiscover: false,
        addRemoveLinks: true,
        maxFilesize: 10,
        previewsContainer: "#documentPreview",
        removedfile: function(file) {
            var _this = file;
            var currentFile = $(file.previewTemplate).children('.server_file').val();  
            // console.log(currentFile);         
            var fileType = $(file.previewTemplate).children('.server_file_type').val();
            if (typeof currentFile != "undefined") {
                $.ajax({
                    url: baseUrl + "/remove-files",
                    type: 'POST',
                    data: { file: currentFile, type: fileType },
                    success: function(result) {
                        // console.log("--------------");
                        // console.log(result);
                        // console.log("--------------");
                        $('[type=submit]').removeAttr("disabled");
                        if (result) {
                          $('#documentUploader').show();
                            //	var Val = parseInt($('#doc_count').val());
                            //$('#doc_count').val(Val-1);
                            console.log("--------------");
                            console.log(_this.previewElement.parentNode);
                            console.log("--------------");
                            _this.previewElement.parentNode.remove();

                            _this.previewElement.parentNode.removeChild(file.previewElement);
                        } else {
                            toastInit('Something went wrong', "error");
                        }
                    }
                });
            }
        }
    });
}
}